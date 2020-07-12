---
title: "Configuring and Securing an SSH-based Jump Host"
date: 2020-07-11T15:22:01-07:00
draft: false
toc: true
tags:
  - security
  - infra
---

Most infrastructure deployments contain systems that should be protected and not allow anyone on the Internet to log in to. Today, I will be going over a few different ways to secure a jump host (also known as a bastion host), which can be used as an entry point into a secure infrastructure environment.

When using a jump host, internal systems can have firewall rules configured to only allow SSH access from the jump host, or the jump host can have two NICs, one on the public Internet, and another on your internal network.

Command examples starting with `#` are run as root, and `$` are run as a normal user.

## Configuring the Host

I will be using an Ubuntu 18.04 Server VM as an example in this post. However, all of these steps should work fine on any *nix system, some commands may need to be adjusted though (i.e. `apt` vs `yum`, etc.)

First, make sure your system is up to date. Depending on how old your base image is, you will probably have GRUB, kernel, or other packages updated that require a reboot.

```
# apt update && apt upgrade -y
# reboot
```

I also recommend creating a new user that isn't able to use sudo, in order to minimize potential compromise. If you need to administer the system for whatever reason, you can log in using this low privileged user, and then escalate locally to `root` using `su`. In order for that to work, we also need to set a password on the root account. If your VM already has a standard user with `sudo` permissions, another user should be created without `sudo` permissions.

When adding the `lowpriv` user, feel free to leave all of the user info fields blank, or fill them out to your liking.

```
# passwd
# adduser lowpriv
```

## SSH Keys

When authenticating to SSH, the most common authentication scheme is providing a password. However, you can also utilize an SSH keypair to connect automatically without a password (except for unlocking the private key, optional but recommended).

If you don't already have an SSH keypair generated on your system, you can generate one by running this command on your **local system**:

```
$ ssh-keygen -t ed25519
```

Now, add your public key on the `lowpriv` user so you don't get locked out while re-configuring `sshd` in the next section (make sure to fill in the `ssh-pub-key-here` placeholder).

```
# su - lowpriv

(we are now in a new shell as the lowpriv user)
$ mkdir .ssh
$ echo "ssh-pub-key-here" >> .ssh/authorized_keys
$ chmod 600 .ssh/authorized_keys
$ exit
```

Now, make sure you can SSH using a keypair to your jump host VM as the `lowpriv` user before continuing.

### Krypton

In the above scenario, the private key lives on your computer, and either has to be transferred to your other computers (more convenient) or additional keypairs have to be generated on each other computer (more security). However, a startup called [Krypton](https://krypt.co/) (recently purchased by Akamai) has developed a new technology enabling the private key material to live on your phone using the Secure Enclave on iOS or the Keystore on Android to safeguard your keys, and enable you to control access via push notifications everytime the key is requested. I just started using it and really enjoy the added security/visibility into my authentication, and will be pushing it out across most of my infrastructure.

Krypton is designed for securing MFA for common websites, but if you enable Developer Mode in the app on your phone you can setup SSH and PGP as well. I'm not going to go in depth on setting it up in this post, but it's pretty straightforward and I'd highly recommend it.

## SSH Config

_**IMPORTANT**_: Before the below modifications to the SSH config, ensure that you have tested logging as the `lowpriv` user, as in this section we will be disallowing any other account from using SSH.

I set the following options in my `/etc/ssh/sshd_config`:

* `Port 31337`: Move SSH to a non-standard port. This doesn't provide much security but it stops dumb SSH bruteforcers from hitting the SSH port.
* `LogLevel VERBOSE`: Increase the log verbosity
* `PermitRootLogin no`: Disable root login
* `PubkeyAuthentication yes`: Enable authentication with keypairs
* `AuthorizedKeysFile   .ssh/authorized_keys`: Hardcode directory used for authorized public keys (this is expanded to `$HOME/.ssh/authorized_keys`)
* `HostbasedAuthentication no`: Disable host authentication
* `IgnoreRhosts yes`: Explicitly disable `.rhosts` files
* `PasswordAuthentication no`: Disable password-based auth
* `PermitEmptyPasswords no`: Disable empty passwords (this doesn't really matter because we disabled password auth but extra verbosity won't hurt)
* `ChallengeResponseAuthentication no`: Disable challenge/response auth
* `UsePAM yes`: Enable PAM modules
* `AllowAgentForwarding yes`: Allow SSH clients to forward SSH agents to use this host as a proxy
* `GatewayPorts no`: Disable SSH remote forwarding
* `X11Forwarding no`: Disable X11 forwarding. Depending on your use case you may need to enable this.
* `PrintMotd no`: Disable printing the MOTD. This can be enabled/configured to your liking.
* `AcceptEnv LANG LC_*`: Accept locale variables from SSH client
* `AllowUsers lowpriv`: Whitelist the `lowpriv` user. This (in combination with `PermitRootLogin no`) effectively disables all other users from SSHing in.

Here is the final config:

{{< gist captainGeech42 819167bb8f5b0853f7f0176d0b8bf4f3 >}}

## Firewall

Next, I'm setting up `iptables` as our firewall software to prevent connections to other ports on the system. This isn't super necessary as it's the only publicly accessible daemon running on our system but it's always a good idea to configure a firewall, especially in a security-sensitive context such as this. The order in which these rules are applied is important, as it is _very easy_ to lock yourself out of a system with a bad `iptables` rule (speaking from experience).

(note: these rules are based on [this Unix SE answer](https://unix.stackexchange.com/a/119727))

```
# iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
# iptables -I INPUT -p tcp -m state --state NEW --dport 31337 -j ACCEPT

(if you don't want the host to respond to ping, this rule isn't necessary)
# iptables -A INPUT -p icmp --icmp-type 0 -m state --state ESTABLISHED,RELATED -j ACCEPT

# iptables -A INPUT -i lo -j ACCEPT
# iptables -A INPUT -j DROP
```

Now, test that you can still SSH into the system. If not, reboot the system, which will cause the rules to be removed, and troubleshoot applying the firewall rules.

Once you've gotten the rules working to your liking, save them so they persist on reboot:

```
# apt install iptables-persistent netfilter-persistent
# iptables-save > /etc/iptables/rules.v4
# systemctl restart netfilter-persistent
# systemctl enable netfilter-persistent
```

Reboot your VM to make sure the firewall rules were correctly persisted (you can check the current rule status with `iptables -L`)

## Prevent Brute-Forcing

`fail2ban` is a popular tool used to prevent brute force attacks by analyzing application logs and dynamically creating firewall rules to block misbehaving IPs.

First, you need to install the package and start the daemon:

```
# apt install -y fail2ban
# systemctl start fail2ban
# systemctl enable fail2ban
```

Now, write the SSH jail config to `/etc/fail2ban/jail.d/sshd.conf`:

{{< gist captainGeech42 866b1a3930015e6592b6c045945c7fbd >}}

This configures `fail2ban` to watch the SSH auth log at `/var/log/auth.log`, and if an IP address fails authentication 3 times within 60 seconds, they are blocked for 1800 seconds (30 minutes). Feel free to tune these values to your liking.

Finally, restart the `fail2ban` daemon to apply the new jail config:

```
systemctl restart fail2ban
```

## Usage

Right now, you can SSH into the jump host and then start another SSH connection to your destination:

```
zander@mypc:~$ ssh -p31337 lowpriv@jumphost
...
lowpriv@jumphost:~$ ssh root@secretserver
...
root@secretserver:~#
```

However, there are a few improvements we can make for using a jump host.

### Agent Forwarding


The above authentication workflow is slow and inefficient, requires additional credentials to reside on the jump host, and doesn't lend itself well to automation. SSH has a number of very cool features, one of which is agent forwarding. Using the following SSH config, we can automate proxing through the jump host to our final destination with one command:

{{< gist captainGeech42 3a11b6b7f6649cb1c8b0c850a4689089 >}}

```
zander@mypc:~$ ssh secret
...
root@secretserver:~#
```

However, this has a couple caveats

1. The final destination (in the above example, `secretserver`) needs to have your local public key in its `authorized_keys` file. The above config uses the authentication on the starting point, not a keypair on the jump host
2. The final destination needs to configured to accept forwarded agents. This is the default behavior and usually won't be an issue.

### Port Forwarding

Another valuable use case for the jump host is to enable access to internal resources from your local machine. This is also possible using SSH local port forwarding, which can be configured at connection time on the command line, or in your SSH config file.

Here's how to configure this at runtime (using the jumphost in the above SSH config):

```
ssh -L 8080:myhost.com:80 jump
```

Now, you can connect to `localhost:8080` and access `myhostcom:80` as if you were on the jump host.

_**WARNING**_: By default, `-L 8080:myhost.com:80` establishes a listener at `0.0.0.0:8080` on your local system, allowing anyone on your network to connect to your system over port 8080 and get forwarded to the remote system. It is highly advised to configure with `-L 127.0.0.1:8080:myhost.com:80` instead to limit the exposure of the forwarded service.

You can also configure this in your SSH config (this does the same as `-L 127.0.0.1:8080:myhost.com:80`):

{{< gist captainGeech42 8b7e2e22a77fb3cc013e118e26a899c8 >}}

This is especially useful for accessing internal websites and RDP servers.

### Destination Firewall

Once you've tested connecting to your remote system, firewall rules should be put in place to restrict access to the SSH daemon from only the jump host, otherwise there isn't nearly as much of an added security benefit.

You can do this using `iptables` (make sure to replace `jump-ip-here` with your actual jump host IP):

```
# iptables -A INPUT -p tcp --dport 22 -s jump-ip-here -j ACCEPT
# iptables -A INPUT -p tcp --dport 22 -j DROP
```

After testing to make sure these rules properly drop outside connections and allow connections from the jump host, ensure the rules will persist across reboot by doing the following (install command will vary on different distros):

```
# apt install iptables-persistent netfilter-persistent
# iptables-save > /etc/iptables/rules.v4
# systemctl restart netfilter-persistent
# systemctl enable netfilter-persistent
```
