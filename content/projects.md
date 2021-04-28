---
title: "Projects"
date: 2019-07-14T14:00:29-04:00
---

I always have a side project that I'm working on (usually more than one). I've highlighted some of the major public ones here, but you can probably find newer info on my [GitHub](https://github.com/captainGeech42)/[GitLab](https://gitlab.com/captainGeech) profiles.

## RansomWatch

RansomWatch is a tool that helps cyber threat intelligence (CTI) analysts monitor [ransomware leak sites](https://www.bleepingcomputer.com/news/security/list-of-ransomware-that-leaks-victims-stolen-files-if-not-paid/). It scrapes various ransomware leak sites to identify new and removed victims, and sends notifications via Slack when something changes.

Technologies used: Python, sqlite, Docker

[Git repo](https://github.com/captainGeech42/ransomwatch)

![RansomWatch example](https://raw.githubusercontent.com/captainGeech42/ransomwatch/main/img/slack_example_new_victim.png)

## Homelab

I have a homelab (running VMware) where I learn about different IT technologies, and run the infrastructure for my malware analysis and other personal projects. Check out my [latest blog post](/blog/2021-homelab/) for details on Lab 3.0.

{{< img src="/lab.png" caption="Latest lab diagram (Lab 3.0)" >}}

## TrainWatch

TrainWatch is an iOS and watchOS app that monitors the status the subway trains in Washington, D.C. and provides a clean interface on your wrist for checking when the next trains will arrive nearest you.

Technologies used: Xcode, Swift, SwiftUI

[Git repo](https://github.com/captainGeech42/TrainWatch)

## Zeek Packages

As an active [Zeek](https://zeek.org/) user, I often find myself identifying gaps in coverage by the existing functionality. In these situations, I always try and build a package to implement this functionality, and share it with the rest of the Zeek community.

I've written/published two packages so far, and hope to do more in the future:

* [zeek-intel-path](https://github.com/captainGeech42/zeek-intel-path)
  * This package adds a new Intel type for URL paths.
* [zeek-bogon](https://github.com/captainGeech42/zeek-bogon)
  * This package adds detection for [bogon networks](https://team-cymru.com/community-services/bogon-reference/), and adds a new field to the `conn.log` that can be used to filter for them.
  * It was also the winner of the [third Zeek Package Contest](https://zeek.org/2020/07/15/zeek-package-contest-zpc-3/).

You can see an up-to-date list of all my published package by checking the [package listings](https://packages.zeek.org/packages?q=captainGeech42).

## massmoji

massmoji is a tool that downloads all of the 10,000+ emojis from [Slackmojis](https://slackmojis.com/) and automatically adds them all to your Slack workspace. Functionality of this will break whenever Slack changes their API on the web interface, as this uses a private API route that was identified from monitoring the HTTP traffic of manually uploading an emoji. However, it works pretty fantastically for the time being.

Technologies used: Python

[Git repo](https://github.com/captainGeech42/massmoji)

## Decompliation as a Service (DaaS)

DaaS is a REST API that enables someone to upload a binary and utilize the IDA Pro decompiler to decomplie the binary, and retrieve the output pseudo-C over HTTP. It was developed to support the [OSUSEC Discord Bot](https://gitlab.com/osusec/discord-bot/-/blob/master/commands/ctf.py#L89), which now has a `!decompile` command.

Technologies used: Python, Flask, sqlite, Docker, IDA Pro

[Git repo](https://github.com/captainGeech42/daas)

![DaaS example](/img/daas-example.png)

## findmal

findmal is a CLI tool that makes it easier to download malware samples. Provide one or more hashes, and it checks different malware repositories to find the sample and download it. I use it regularly when I stumble across a tweet or article that mentions a hash, so I can pull down the sample and save it for my own analysis.

Technologies used: Go

[Git repo](https://github.com/captainGeech42/findmal)

![findmal example](/img/findmal-example.png)

## DamCTF

DamCTF is a [Capture the Flag](https://ctftime.org/ctf-wtf/) competition hosted by the [OSU Security Club](https://www.osusec.org/). I was the lead organizer and infra team lead for 2020, and developed a full CI/CD pipeline that handled all of our challenges and infrastructure deployments, along with monitoring the health of our infrastructure. The infrastructure was backed by Ansible and Terraform for Infrastructure-as-Code (IaC), and operated primarily using Google Kubernetes Engine on the Google Cloud Platform (GCP; they were one of the event sponsors).

I also developed a 5-part malware analysis challenge, which involved participants analyzing PCAP traffic, reverse engineering a Linux malware sample, building an emulator for the C2 traffic, and exploiting a web panel. For more details on this challenge, see [this blog post](/blog/damctf-2020-malware/).

For the infra components, I worked with these technologies:

* Ansible
* Terraform
* GitLab CI/CD
* Docker
* bash
* Elastic Stack
* Google Cloud
* Kubernetes

For the challenges I authored:

* C
* Docker
* Python
* PHP
* MySQL

[Public challenge git repo](https://gitlab.com/osusec/damctf-2020) (the infra repo isn't public, happy to share info about this on request)

## Discord Webhook Proxies

For DamCTF, I wanted to integrate our monitoring and CI/CD pipeline into our existing communications platform (Discord), I built two proxies that ran in our k8s cluster that handled formatting rich embed messages for Discord based on incoming data from various sources:

* [Elastic/Kibana alerting](https://github.com/captainGeech42/elastic-discord-webhook-proxy)
* [Terraform Cloud status notifications](https://github.com/captainGeech42/tf-discord-webhook-proxy)

Technologies used: Go, Docker

![Elastic webhook example](https://camo.githubusercontent.com/2fea01815ff13cb26bf4f806d48b03599bcdb972bab9d2d9c9ab91a2c231d52b/68747470733a2f2f692e696d6775722e636f6d2f6d306f564a42622e706e67)

## Docker Zeek + ELK

This project provides a simple way to quickly deploy Zeek and the Elastic Stack using Docker for one-off PCAP analysis. Using `docker-compose`, you can quickly spin up all of the necessary infrastructure, and drop PCAPs in a folder being watched by Zeek. After they are analyzed, you can query the output Zeek data in Kibana.

Technologies used: Docker, Zeek, Elastic Stack, bash

[Git repo](https://github.com/captainGeech42/docker-zeek-elk)

## OSU Security Club WiFi Demo
This was a project put together for the 2019 College of Engineering Expo to show the dangers of using untrusted WiFi networks.

There are two parts to the demo:
* Passive collection: The user enters some data on the webpage, and the attacker machine passively captures all of the form fields
* Active MitM: The user enters some data on another webpage, and the attacker machine intercepts the request and modifies some fields to show they were able to actively interrupt the connection

[Git repo](https://github.com/osusec/wifi-demo/)

![Wifi Demo](/img/wifi-demo.jpg)

## Flagnado

Flagnado is a CTF attack/defense dashboard and exploit throwing tool. It allows team members to upload their exploits and automatically run them against the target hosts throughout the competition, and tracks their success against each team.

Technologies used: Python, Django, sqlite

[Git repo](https://gitlab.com/osusec/flagnado)
