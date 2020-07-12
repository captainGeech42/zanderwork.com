---
title: "Setting up an Onion Mirror"
date: 2020-07-12T08:03:47-07:00
draft: false
toc: true
tags:
  - infra
---

I've been working on a few infrastructure improvement projects in preperation for rolling out my new homelab environment, and one thing I've wanted to do is setup an Onion mirror for this site. I have pretty much no actual reason to do it, but it's always fun to learn something new and have something on `tHe DaRk WeB`.

This post will include some notes for using [Hugo](https://gohugo.io/), [NGINX](https://www.nginx.com/), and Ubuntu 18.04, but should be pretty general and easily transferrable to any other stack.

As usual, command examples starting with `#` are run as root, and `$` are run as a normal user.

## Hugo

Due to how Hugo generates your site, there are only a couple things to make sure you setup for this to work:

* In your `root/config.toml`, make sure to set `baseURL = "/"` instead of a full domain name. This way, links are built using relative paths instead of absolute paths, and you won't have to do any sort of nginx filter/rewrite to fix them en route.
* Ensure that any links you have on your site end in a trailing `/` (i.e. `url = "/about/"` instead of `url = "/about"`). Otherwise, a redirect will occur and NGINX will route to the wrong port, causing the request to fail. I assume there is an NGINX rule that could be written to solve this but it's easiest to just not let the problem occurs.

## NGINX

For my site hosted on clearnet, I use Let's Encrypt (via `certbot`) to provide HTTPS. However, Let's Encrypt policy forbids generating certificates for .onion domains, so you will need to configure an additional server to bypass the auto redirect to HTTPS used by clearnet browsers. Luckily, the Tor network provides end to end encryption for all requests so HTTPS is mostly redundant, see [this Tor Project blog post](https://blog.torproject.org/facebook-hidden-services-and-https-certs) for more details and the pros/cons for HTTPS+Tor.

In your NGINX config (probably `/etc/nginx/sites-enabled/default`), add this (make sure to adjust the webroot properly):

{{< gist captainGeech42 3019472646a100477c932e7c5a1a7c16 >}}

This config also sets up new access and error logs to monitor site traffic via Tor. Please note that IP addresses for the viewers won't be logged, as all requests come from the Tor proxy running locally, so requests will all be from `127.0.0.1`.

Now test that you can access your site locally with `curl localhost:8080`.

## Tor

First, install the Tor command line interface:

```
# apt install -y tor
```

Now, add the following lines to `/etc/tor/torrc` (feel free to change the directory name):

```
HiddenServiceDir /var/lib/tor/myonionsite/
HiddenServicePort 80 127.0.0.1:8080
HiddenServiceVersion 3
```

Please note that Tor will create the `HiddenServiceDir` on disk after restarting the daemon with the proper permissions, so don't create it yourself.

Then, restart the Tor daemon, and set it to launch at boot:

```
# systemctl restart tor
# systemctl enable tor
```

You can see the hostname generated for your service by running:

```
# cat /var/lib/tor/myonionsite/hostname
```

If you plan on operating this service for a while, you should back up the private key data in `/var/lib/tor/myonionsite` so you can redeploy the site in the future. Please note that it will take about 10 minutes for your service to be accessible on the Tor network.

### Vanity URL

Normally, an v3 onion address is made up of 56 Base32 characters, generated at random when you initialize your onion service. However, you can create a custom URL with a chosen prefix to create a more recognizable domain. A great tool to do this is [mkp224o](https://github.com/cathugger/mkp224o), and will output the hostname and private key data for any domain it generates that matches a specified filter.

Please note that generating domains with a prefix <= 6 chars is feasible, but anything larger will take a fair amount of time and compute power.

After generating the hostname and key data, replace the existing data on the server in `/var/lib/tor/myonionsite` and restart the Tor daemon.

## OPSEC

Depending on your threat model, you may have extra OPSEC concerns that you wish to use Tor to protect against, for yourself and/or your users. This post provides no extra OPSEC information, and that is up to you to implement. What I've described above is the bare minimum to get a service running to mirror a clearnet site, and the same amount of OPSEC as running a clearnet site.
