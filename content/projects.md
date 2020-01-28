---
title: "Projects"
date: 2019-07-14T14:00:29-04:00
---

### Homelab
Blog post soon to come

In progress [diagram](/img/lab.png)

### Virtualized PC Workstation
Blog post soon to come

Using a Threadripper 3960x, RX 5700 XT, and Radeon VII along with libvirt to run multiple virtualized workstations. Primarily a Windows VM with VGA passthrough (Radeon VII) for near bare-metal graphics performance, and a Linux VM for non-gaming/Windows activities. [PCPartPicker parts list](https://pcpartpicker.com/user/zwork/saved/XdYn7P)


### LEGO Star Destroyer CTF Challenge (in progress)
This was originally just a cool Raspberry Pi project with some LEGOs but I somehow figured out a way to make it even more awesome! Turns out, the only way to possibly top LEGOs is adding a CTF challenge to it! Or in this case, 2!

This uses a Raspberry Pi 3B+ and WS2801 LED strips around the side, and a TC1602A 8x2 character LCD screen on the front to display messages.

There are two CTF challenges running off the Pi:
* A easy/medium pwn challenge (partially difficult due to Raspberry Pi being ARM rather than x86)
* A easy web challenge

These challenges are going to be used in an upcoming CTF event hosted by OSUSEC, and after that is over more details about the challenges and source code for the electronics will be made public.

![Star Destroyer](/img/stardestroyer.jpg)
### OSU Security Club Discord Bot
This is a Discord bot to support the [OSU Security Club](https://www.osusec.org) Discord Server.

Features:
* Email verification upon joining the server
  * When a new user joins the server, the bot will message them and ask for their Oregon State email. Upon receipt, the bot will email them a token and wait to receive it, upon which the user will receive the `Member` role, allowing them to access more channels.
  * This is used to restrict access to sensitive/internal channels since the Discord invite link is publicly available on our website
* Role management
  * Users can add/remove roles relevant to what they are doing in the club
* Channel management
  * Admins can create/archive channels for the CTF competitions we do
* And more...

[GitLab repo](https://gitlab.com/osusec/discord-bot)

### OSU Security Club WiFi Demo
This was a project put together for the 2019 College of Engineering Expo to show the dangers of using untrusted WiFi networks.

There are two parts to the demo:
* Passive collection: The user enters some data on the webpage, and the attacker machine passively captures all of the form fields
* Active MitM: The user enters some data on another webpage, and the attacker machine intercepts the request and modifies some fields to show they were able to actively interrupt the connection

![Wifi Demo](/img/wifi-demo.jpg)

[GitHub repo](https://github.com/osusec/wifi-demo/)

_more projects will be added soon_
