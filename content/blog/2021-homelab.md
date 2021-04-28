---
title: "2021 Homelab Update (Lab 3.0)"
date: 2021-04-25T12:36:34-07:00
draft: false
toc: true
tags:
  - homelab
---

I've been working with my homelab since 2016, and it's taken many different forms over the years. Having my own set of servers and networking equipment enables me to explore different enterprise software configurations and deployments that would be difficult to get hands on experience with otherwise. Numerous times, my homelab experience has directly contributed to success in my job, which is always satisifying.

In this blog post, I'll be showing the current state of my lab, the ins and outs of how things are running, and hopefully provide inspiration for your own lab/projects.

## Physical Equipment

I primarily run Dell and Cisco equipment in my lab, as it's what I'm most comfortable with.

{{< img src="/img/2021-homelab/full-rack-front.png" caption="The front of my rack" >}}

### Virtualization Servers

I have three servers for virtualization:

* 2x Dell R510
  * `ironpatriot`
  * `warmachine`
* Dell R710s
  * `ultron`

I run a VMware lab, so each virtualization server is running VMware ESXi 6.7 (I haven't had a chance to do a VMware 7 upgrade, as there are extra steps required due to the CPUs I have).

Each server has the following:

* 2x Intel X5660 CPUs (2.80 GHz, 6c/12t)
* 64 GB RAM
* 4x 1Gbps NICs
* 1x 10Gbps NIC
* 16GB USB flash drive for boot

### Storage Server

I only have one storage server:

* Dell NX3100
  * `ironman`
  * Intel E5506 CPU (2.13 GHz, 4c/4t)
  * 16 GB RAM
  * 2x 1Gbps NICs
  * 1x 10Gbps NIC

If you are unfamiliar with the NX3100, it's a slightly modified R510, with 12x 3.5" drive bays, and originally shipped with Windows Storage Server 2008. I'm running CentOS 7, and providing storage to my VMware hosts using NFS over 10Gbps.

I have three tiers of storage available for different workloads:

* SSD Tier
  * 5x 800 GB SAS SSDs
  * RAID 5 array, 2.2 TB usable capacity
    * One SSD is configured as hot spare, array is running as 3+1
  * Used for high performance/key workloads
* HDD Tier
  * 5x 4 TB SAS HDDs
  * RAID 5 array, 15 TB usable capacity
  * Used for mass storage and low performance workloads
* Scratch Tier
  * 2x 2 TB SATA HDDs
  * RAID 1 array, 1.8 TB usable capacity
  * Used for random testing, no expectation of data protection (very old drives)

Each of the RAID arrays is configured using `mdadm`. The individual drives are exposed to the host using a LSI SAS2308 HBA. I opted to use an HBA instead of configuring the arrays via PERC for added flexibility on storage options (e.g. I could use ZFS or a different block-level storage management system that wouldn't have interacted nicely with virtual drives exposed by a PERC).

### Networking

My core network switch is a Cisco C2960S-48LPS-L (`milano`). It is a very solid 48 port 1Gbps switch, and also provides Power-over-Ethernet (PoE) capabilities, which I use for my access point.

For 10Gbps switching, I have a Quanta LB6M (`eclector`). A very reliable switch that had aftermarket Noctua fans installed by the previous owner, so it's surprisingly quiet.

At the border, I have a Netgear CM600 modem, and a Dell R210 II server running pfSense (`deadpool`). I'm not a massive fan of pfSense but it provides solid out of the box functionality.

Dell R210 II specs:

* Intel E3-1240 CPU (3.30 GHz, 4c/8t)
* 8 GB RAM
* 2x 1Gbps NICs
* 1x 10Gbps NIC
  * The server came with this card, it's not being used currently
* 32 GB USB flash drive for boot/storage

For wireless capability, I'm running a Unifi AP AC Lite, made by Ubiquiti Networks. It's a very capable, yet affordable access point, with feature rich management capabilities. It's being powered over PoE by the core switch `milano`.

I also have a lot of miscellaenous Cisco gear that I use for testing various network topologies. It's all very ephemeral and not used for anything real, so it stays completely separate from the main network.

{{< img src="/img/2021-homelab/back-rack-net.png" caption="Network and power components on the back of the rack" >}}

### Power

Everything in my lab is being powered off an APC SMT1500RM2U, a 1500VA uninterruptible power supply (UPS). This UPS is at >90% load 24/7, so it doesn't work great when power is out for more than a few minutes, but it's been incredibly helpful for power blips that we get occasionally.

Connecting all of the equipment to the UPS is a pair of Tripp-Lite PDU1215, a 15A rackmount power delivery unit (PDU) with 13 outlets each. For servers with dual power supplies, they each have one PSU going to each PDU, for some semblance of power redundancy (although the UPS is much more likely to fail).

### Rack

Housing everything is a StarTech 25U open frame rack, which is shockingly solid and has wheels, making it easy to move around when needed. I'm also using Rackstuds instead of traditional cage nuts/screws, because cage nuts are painful and Rackstuds are awesome.

## Network Topology

I have a number of different subnets in my network, each operating on their own VLAN:

* VLAN 10: House Wired
  * Used by my roommates roommates and I for Ethernet-connected PCs, consoles, etc.
* VLAN 20: Privileged WiFi
  * Used by my roommates and I for wireless laptops, phones, etc.
* VLAN 21: Guest WiFi
  * Used by other people to get online
  * Not even sure if it works anymore, thanks COVID
* VLAN 22: IOT WiFi
  * Dedicated hidden SSID network for IOT ~~garbage~~ devices
* VLAN 30: Lab
  * The majority of my lab VMs live on this network
* VLAN 40: Management
  * VMware/etc. management interfaces live on this network
* VLAN 50: Malware
  * All of my malware analysis infrastructure lives on this network
* VLAN 60: DMZ
  * Anything internet facing sits in this network

I have strict firewall rules controlling my internet border, and traffic ingressing/egressing from the DMZ into the rest of my network, in order to minimize risk for any internet facing service. pfSense makes it easy to control the firewall rules between VLANs, but I strongly recommend testing the controls you put in place to make sure they are working properly, and monitoring what your border looks like using a tool like [Shodan](https://www.shodan.io/), or by `nmap`'ing your IP from somewhere off your network. There are also strict firewall rules closing off the malware network, in case I accidentally detonate something in an uncontrolled environment to mitigate risk to other systems.

The 10Gbps network, switched through `eclector`, isn't being routed at L3, and uses jumbo frames everywhere, for optimized performance.

I also have a 1 Gbps network tap, which is in-line on the connection from `milano` to `deadpool`. This packet feed is used for network monitoring, more details later.

{{< img src="/img/2021-homelab/tap.png" caption="Network tap, capturing most East/West traffic" >}}

{{< img src="/img/2021-homelab/diagram.png" >}}

## VMware

As I mentioned, I primarily run VMware in my lab, which provides a lot of flexibility for me to spin up a variety of workloads on demand. I'm using vCenter (deployed as an appliance, `hydra`) to manage the environment, which lets me easily migrate VMs between hosts and centrally manage networking. Here are some quick stats for my VMware deployment:

* 100 GHz total CPU capacity
* 192 GB total RAM capacity
* 18.38 TB total disk capacity
* 36 VMs

![vCSA home page](/img/vcsa-home.png)

My biggest bottleneck is RAM usage, I always sit at > 80% RAM usage. To expand my lab capabilities, I'll have to add on another VM host or purchase more RAM for the existing ones.

The 10Gbps network that all VM hosts are connected to is used for both storage and vMotion traffic. The 1Gbps network is used for all of the VM traffic, and also ESXi/vCenter management traffic. Each of the three VMware hosts has 4x 1Gbps ports connected to `milano` as VLAN trunks for any VLAN that is needed in VMware (30, 40, 50, and 60).

### Workloads

I have a large variety of things running in my lab. Here are some of the systems I'm running:

* `blackwidow`: Database server
  * This provides MySQL and PostgreSQL databases to any application that may need a database (e.g. Nextcloud).
* `hulk`: GitLab server
  * Some of the projects I work on contain sensitive code that I don't want to host on public GitHub/GitLab deployments (typically projects related to my malware research).
* `hulkbuster`: Nextcloud server
  * I use Nextcloud for local file syncing between systems. It's great for quickly moving files between VMs, and I can access it on my phone/iPad as well.
* Domain Name System (DNS)
  * `heimdall`, `vision`
  * I'm using BIND 9 to provide DNS for the entire network. `heimdall` is running as the master server, and syncs records to `vision`.
* Active Directory Domain Services (AD DS)
  * `hill`, `sitwell`
  * I'm using AD DS to provide central authentication and management for other Windows hosts in the network.
  * At present, I'm not using AD DS for SSO based authentication outside of the Windows systems, but it's something I'm planning on doing.
* `dormammu`: SSH work server
  * I use this VM as a always-running Linux box I can connect.
  * Its main use at present is persistent IRC sessions using `weechat`. 
* Elasticsearch Cluster
  * I run a large Elasticsearch cluster, which ingests data from a variety of sources.
  * It has 4 hot nodes and 2 cold nodes, using Index Lifecycle Management (ILM) to migrate data between tiers:
    * The hot tier has 400 GB of total capacity, running on SSD tier storage.
    * The cold tier has 4 TB of total capacity, running on HDD tier storage.
  * I'm also using Kibana and Logstash on `jarvis` for visualization and data analysis, and data ingestion/normalization.
* Capture the Flag (CTF)
  * As an avid CTF player, I have some support infrastructure setup which makes it easier to work on challenges and share info with my teammates:
    * `ctfsync` is running a Ghidra server and a revsync server.
    * `groot` is a Ubuntu VM with all of my CTF pwning tools pre-configured and easily accessible.
    * `babygroot` is a Kali VM, which I don't use too often, but is very handy with some of the tools that come pre-installed.
  * I also spin up ephemeral VMs for odd challenges that need weird setup, or for some challenges/events that have dedicated VMs associated with them.
    * Hack-a-Sat 2020 Finals was one of those events, due to all of the special software needed
* `fury`: Zeek
  * I am a longtime Zeek user, both for my own research and at work. The Zeek install on `fury` is used to monitor the packets coming from my network tap, which provides north-south visibility on most of my network. A future architecture of the network will hopefully provide better visibility, as there is some intra-network traffic that the tap doesn't see currently.
* `steve`: Minecraft server
  * My friends and I all enjoy playing Minecraft, so this VM runs a vanilla server that we all play on together
* `drax`: Wireguard server
  * This is my VPN server that I use to securely access the network from off-site, and also bring my cloud-hosted systems into the network, where appropriate.
* `wakanda`: NGINX server
  * This NGINX install serves as a reverse proxy, providing external access to systems beyond the DMZ in the network (e.g. GitLab, Nextcloud, etc).
  * It also handles SSL certificates via Let's Encrypt/`certbot`.

## Malware Research Environment

As previously mentioned, some of the infrastructure running in my lab is used to support my malware research activities. Having a separate, dedicated network where I can dynamically analyze malware in a more robust environment is incredibly helpful, and saves a lot of time for me as an analyst. I have safe ways to allow outbound Internet connections, using a dedicated pfSense install and VPN tunnels out to the internet.

For tooling on these systems, I primarily use [FLARE VMs](https://github.com/fireeye/flare-vm), along with custom tooling I've developed. I also run emulators on a separate host in this network for automated threat intelligence collection.

## FAQs

_**Why do you have all of this?**_

Having all of this equipment/infrastructure available serves a few purposes:

* It provides a semi-realistic corporate environment where I can learn about IT systems management and configuration. I can spin up new things, break things, and nobody cares, making it an ideal way for me to learn about all sorts of technologies that I may not be able to learn about otherwise
* It's a lot cheaper for me to spin up new workloads in my homelab environment than in the cloud

But most importantly, it's fun.

_**How much did all of this cost?**_

Thanks to a lot of time spent on eBay and r/homelabsales, I was able to score a lot of good deals on equipment, which was important given my tight student budget. I'd rather not talk in specifics publicly, but keep an eye on those two places for good deals, along with your local Craigslist/Facebook Marketplace type websites. You can also check local university surplus stores, or ask your IT department if they are recycling any equipment, you might be able to get greats core. You'll surely be able to score some good deals with enough patience.

_**How can I get started?**_

The best thing you can do is download something like [VirtualBox](https://www.virtualbox.org/) or (preferred) [VMware Workstation](https://www.vmware.com/products/workstation-pro.html) (if you are a university student, you may get VMware products for free, ask your IT department). If you are on Windows or macOS, download an Ubuntu ISO and learn how to make a VM, snapshots, etc. and how Linux works. If you are on Linux already, download Windows and do the same thing. Find something you're interested in, and learn how it works.

If you have some budget and are looking to buy some physical equipment, eBay and r/homelabsales, along with local online listings are a great place to start. Try not to get too adventurous to start, dip your toes in the water and make sure it's something you'll get value out of. I've made the mistake of getting things because they look interesting and not using them enough to justify them, so learn from my mistakes and be careful when you can.