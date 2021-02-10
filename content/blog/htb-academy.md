---
title: "Hack The Box - Academy"
date: 2021-02-09T20:49:48-08:00
draft: false
tags:
    - security
    - htb
toc: true
---

[Academy](https://app.hackthebox.eu/machines/297) is an easy Linux box on [Hack The Box](https://www.hackthebox.eu), created by [egre55](https://app.hackthebox.eu/users/1190) and [mrb3n](https://app.hackthebox.eu/users/2984). A summary for the box is at the bottom, in order to avoid spoilers for anyone looking for a nudge on their current progress.

## Recon

As always, I started by scanning the box with `nmap`:

```
# Nmap 7.80 scan initiated Fri Feb  5 17:20:27 2021 as: nmap -A -T4 -p1-65535 -oN nmap.out 10.10.10.215
Nmap scan report for 10.10.10.215
Host is up (0.095s latency).
Not shown: 65532 closed ports
PORT      STATE SERVICE VERSION
22/tcp    open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.1 (Ubuntu Linux; protocol 2.0)
80/tcp    open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: Did not follow redirect to http://academy.htb/
33060/tcp open  mysqlx?
| fingerprint-strings: 
|   DNSStatusRequestTCP, LDAPSearchReq, NotesRPC, SSLSessionReq, TLSSessionReq, X11Probe, afp: 
|     Invalid message"
|_    HY000
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port33060-TCP:V=7.80%I=7%D=2/5%Time=601DEF44%P=x86_64-pc-linux-gnu%r(NU
SF:LL,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(GenericLines,9,"\x05\0\0\0\x0b\x
SF:08\x05\x1a\0")%r(GetRequest,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(HTTPOpt
SF:ions,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(RTSPRequest,9,"\x05\0\0\0\x0b\
SF:x08\x05\x1a\0")%r(RPCCheck,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(DNSVersi
SF:onBindReqTCP,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(DNSStatusRequestTCP,2B
SF:,"\x05\0\0\0\x0b\x08\x05\x1a\0\x1e\0\0\0\x01\x08\x01\x10\x88'\x1a\x0fIn
SF:valid\x20message\"\x05HY000")%r(Help,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%
SF:r(SSLSessionReq,2B,"\x05\0\0\0\x0b\x08\x05\x1a\0\x1e\0\0\0\x01\x08\x01\
SF:x10\x88'\x1a\x0fInvalid\x20message\"\x05HY000")%r(TerminalServerCookie,
SF:9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(TLSSessionReq,2B,"\x05\0\0\0\x0b\x0
SF:8\x05\x1a\0\x1e\0\0\0\x01\x08\x01\x10\x88'\x1a\x0fInvalid\x20message\"\
SF:x05HY000")%r(Kerberos,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(SMBProgNeg,9,
SF:"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(X11Probe,2B,"\x05\0\0\0\x0b\x08\x05\x
SF:1a\0\x1e\0\0\0\x01\x08\x01\x10\x88'\x1a\x0fInvalid\x20message\"\x05HY00
SF:0")%r(FourOhFourRequest,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(LPDString,9
SF:,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(LDAPSearchReq,2B,"\x05\0\0\0\x0b\x08
SF:\x05\x1a\0\x1e\0\0\0\x01\x08\x01\x10\x88'\x1a\x0fInvalid\x20message\"\x
SF:05HY000")%r(LDAPBindReq,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(SIPOptions,
SF:9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(LANDesk-RC,9,"\x05\0\0\0\x0b\x08\x0
SF:5\x1a\0")%r(TerminalServer,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(NCP,9,"\
SF:x05\0\0\0\x0b\x08\x05\x1a\0")%r(NotesRPC,2B,"\x05\0\0\0\x0b\x08\x05\x1a
SF:\0\x1e\0\0\0\x01\x08\x01\x10\x88'\x1a\x0fInvalid\x20message\"\x05HY000"
SF:)%r(JavaRMI,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(WMSRequest,9,"\x05\0\0\
SF:0\x0b\x08\x05\x1a\0")%r(oracle-tns,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(
SF:ms-sql-s,9,"\x05\0\0\0\x0b\x08\x05\x1a\0")%r(afp,2B,"\x05\0\0\0\x0b\x08
SF:\x05\x1a\0\x1e\0\0\0\x01\x08\x01\x10\x88'\x1a\x0fInvalid\x20message\"\x
SF:05HY000")%r(giop,9,"\x05\0\0\0\x0b\x08\x05\x1a\0");
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
# Nmap done at Fri Feb  5 17:22:36 2021 -- 1 IP address (1 host up) scanned in 128.56 seconds
```

I was a bit bewildered by the `tcp/33060` output, and try to poke at it using `nc` and `mysql`, but didn't get anything useful, so I decided to ignore it and move on to the Apache service

## Enumeration

The `nmap` scan easily gave us a domain name to use, so I added it to my `/etc/hosts` file and checked out the page:

![index page for website screenshot](/img/htb/academy/website-index.png)

Nothing too interesting, let's see what's on the Register page:

![register page for website screenshot](/img/htb/academy/website-register.png)

Looks pretty straightforward, I filled out the form and inspected the request in Burp:

![register request in burp screenshot](/img/htb/academy/burp-register.png)

It looks like there is an additional parameter from a hidden field in the HTML form, `roleid`. I set this value to `1`, assuming that it would give me some form of admin functionality.

I went back to the login page and signed in with the account I made:

![logged in home page for website screenshot](/img/htb/academy/website-loggedin.png)

All of the pages and buttons were placeholders, so I fired up Dirbuster to try and find any hidden pages:

![dirbuster screenshot](/img/htb/academy/dirbuster.png)

The `/admin.php` page sounds very intriguing. When I browsed to it, I got another login page, and it looks the same as the login page from previously:

![admin login page for website screenshot](/img/htb/academy/website-adminlogin.png)

I wonder if setting `roleid=1` will let us login to it? I entered my credentials again, and was able to login!

![admin page screenshot](/img/htb/academy/website-admin.png)

The `dev-staging-01.academy.htb` domain is very interesting, so I added to my `/ec/hosts` and checked it out:

![dev page screenshot](/img/htb/academy/website-stacktrace.png)

It looks like the website is using Laravel. I scrolled down a little further and found some very interesting variables being set:

![dev page variables screenshot](/img/htb/academy/website-vars.png)

The `APP_KEY` variable is almost certainly going to be important, along with those database creds. However, they look pretty simple (even for HTB), so I thought they were probably placeholder creds, but I'd try them out anyways when I had the chance.

At this point, I couldn't find anything else, so I started looking for any Laravel exploits I could possibly leverage, and found a very promising [Metasploit module](https://www.rapid7.com/db/modules/exploit/unix/http/laravel_token_unserialize_exec/).

## Establishing a Foothold

The Metasploit module `exploit/unix/http/laravel_token_unserialize_exec` would allow us to achieve Remote Code Execution on the box if the following conditions are met:

* The server is using Laravel versions 5.5.40 or 5.6.x <= 5.6.29
* We need to know the `APP_KEY`

Luckily, we knew the `APP_KEY` being used, but I wasn't sure about the version number. I tried to find the verison number in the stack trace or variables being shown on the debug log, but wasn't able to find it. I then decided to just try it out and see what happened.

I fired up `msfconsole` and configured the exploit module:

![msfconsole configuring options screenshot](/img/htb/academy/msf-options.png)

Then, I crossed my fingers and ran the exploit:

![msfconsole triggering the exploit screenshot](/img/htb/academy/msf-exploit.png)

Excellent! We have gotten a shell as `www-data`.

## Escalating to User #1

At this point, I started to get my lay of the land on the system, and identify what my target is (but not before upgrading my shell with `python3 -c 'import pty;pty.spawn("/bin/bash")'`)

![shell output from upgrading shell and ls screenshot](/img/htb/academy/shell1.png)

I was dropped into `/var/www/html/htb-academy-dev-01/public`, so I started poking around the webroot directory and looked at the other folders and file contents. In `/var/www/html/academy`, there was the website contents for the main site running on the `academy.htb` vhost, and I found a `.env` file in that directory:

![shell output from ls in /var/www/html/academy screenshot](/img/htb/academy/shell2.png)

A `.env` file should always catch your attention, because they are commonly used to store secrets or other credentials that are very interesting to us as hackers/red team operators. I `cat`'d the `.env` file and, low and behold, found some credentials!

![output from cat'ing the .env file screenshot](/img/htb/academy/shell-env.png)

At this point, I tried to login to MySQL using those credentials but wasn't able to. I'm not sure if it's an error on my part, a permissions issue, or maybe those are just invalid creds.

![output from mysql screenshot](/img/htb/academy/shell-mysql.png)

Since they didn't work for MySQL, I started enumerating users on the system, in the hopes that it would work for one of them. While looking through the various `/home` folders, I saw that the `user.txt` file was in `/home/cry0l1t3`, so I tried their account first:

![output from ssh as cry0l1t3 screenshot](/img/htb/academy/ssh-cry0l1t3.png)

Excellent! We know have a shell as `cry0l1t3`, along with the `user.txt` flag.

## Escalating to User #2

I noticed that `cry0l1t3` was in the `adm` group, and assumed that similarly to [Doctor](/blog/htb-doctor/), this privesc would be accomplished by finding credentials in `/var/log`. I started looking through the log files I had access to, but none of the files immediately stood out to me, so I started manually inspecting them.

![output from ls /var/log screenshot](/img/htb/academy/shell-varlog.png)

A couple files were of particiular interest because they handled authentication:

* `/var/log/auth.log`
  * On Red Hat based systems, this would be `/var/log/secure`, but this was a Ubuntu box
* `/var/log/audit/audit.log`

At this point, I got lazy and decided to use `linpeas.sh` to see if it picked up anything interesting (in case you aren't familiar, [LinPEAS](https://github.com/carlospolop/privilege-escalation-awesome-scripts-suite/tree/master/linPEAS) is a terrific script for Linux privilege escalation). Turns out, it analyzed audit logs to find uses of `sudo` or `su` with passwords in the audit logs!

![partial output from linpeas screenshot](/img/htb/academy/shell-linpeas.png)

It is definitely feasible to do this analysis manually, it just required a bit of *nix-fu (`grep` mostly) and some knowledge of how the audit logs work, and how to identify what's interesting and what isn't. Since our goal is to find credentials, filtering for actions that could cause credentials to accidentally be entered into the log is a great place to start. Anything that requires authentication or switching users (`login`, `sudo`, `su`, etc.) is a prime candidate of somewhere a user could accidentally put their password in the username field, causing it to be entered into the log.

Since `mrb3n` was in the password, and there was a user on the system named `mrb3n`, I guessed that that was the account with that password, and tried it out:

![output from su screenshot](/img/htb/academy/shell-su.png)

Perfect! We were now logged in as `mrb3n`.

## Escalating to Root

As always, I ran `sudo -l` to check if/what `sudo` permissions I had:

![output from sudo screenshot](/img/htb/academy/shell-sudo.png)

Looks like we can use `/usr/bin/composer` as root! I checked [GTFOBins](https://gtfobins.github.io/) to see if there was an already documented method for exploiting this access to gain a root shell, and [there was](https://gtfobins.github.io/gtfobins/composer/)! I used the technique documented on GTFOBins and was able to retreive the `root.txt` flag.

![output from composer screenshot](/img/htb/academy/shell-root.png)

## tl;dr

Exploit an inseucre login process to achieve admin web access and discover a development subdomain. The dev site is in debug mode, and shows the Laravel `APP_KEY`. Use a Metasploit module to exploit an RCE vulnerability in Laravel to achieve a low-privilege shell as `www-data`. Escalate to `cry0l1t3` by using credentials found in `/var/www/academy/.env`. Escalate to `mrb3n` by using credentials found in the audit log. Escalate to root by using a GTFOBin technique for `composer` via `sudo`.