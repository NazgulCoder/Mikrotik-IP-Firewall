# Mikrotik IP Firewall Updater
Mikrotik IP Firewall Updater can be considered a fork of my previous [ipfirewall-updater](https://github.com/NazgulCoder/ipfirewall-updater)

## BLACKLISTS

Blacklists are updated daily, I will always try to improve them, optimize and add more sources, do not hesitate to open a pull request.
If any of the blacklists is not mantained anymore it eventually will be removed.

[vpn-proxy-tor.rsc](https://raw.githubusercontent.com/NazgulCoder/Mikrotik-IP-Firewall/main/vpn-proxy-tor.rsc) includes the following blacklists:

- [X4BNet VPN Blacklist](https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt)
- [TOR Blacklist](https://check.torproject.org/torbulkexitlist?ip=1.1.1.1&port=80)
- [clarketm ProxyList](https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt)
- [TheSpeedX ProxyList - HTTP](https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt)
- [TheSpeedX ProxyList - SOCKS4](https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks4.txt)
- [TheSpeedX ProxyList - SOCKS5](https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks5.txt)
- [hookzof ProxyList](https://raw.githubusercontent.com/hookzof/socks5_list/master/proxy.txt)
- [jetkai ProxyList](https://raw.githubusercontent.com/jetkai/proxy-list/main/online-proxies/txt/proxies.txt)

[blacklist.rsc](https://raw.githubusercontent.com/NazgulCoder/Mikrotik-IP-Firewall/main/blacklist.rsc) includes the following ones for an optimal Firewall security:

- [blocklist.de](https://lists.blocklist.de/lists/all.txt)
- [emergingthreats](http://rules.emergingthreats.net/fwrules/emerging-Block-IPs.txt)
- [Firehol Level1](https://raw.githubusercontent.com/ktsaou/blocklist-ipsets/master/firehol_level1.netset)

I decided to make also a list [DNS-Proxy.rsc](https://raw.githubusercontent.com/NazgulCoder/Mikrotik-IP-Firewall/main/DNS-Proxy.rsc) in case you want to force users in your network to use your DNS Server such as AdguardHome, Pi-Hole, Windows Server etc. Just block those IPs on the ports 53, 443, 853 and 8853. This includes the following lists:
- [dibdot List](https://raw.githubusercontent.com/dibdot/DoH-IP-blocklists/master/doh-ipv4.txt)
- [jpgpi250 List](https://raw.githubusercontent.com/jpgpi250/piholemanual/master/DOHipv4.txt)



## HOW TO INSTALL ON MIKROTIK

To use those lists on your Mikrotik device follow these steps:
1) Open New Terminal and paste this script

```bash
/ip firewall filter
add action=drop chain=input comment="Drop new connections from blacklisted IP's to this router" connection-state=new in-interface=ether1 src-address-list=blacklist
```
or
```bash
/ip firewall filter
add action=drop chain=forward comment="Drop new connections from blacklisted IP's to this router" dst-address-list=blacklist
```
2) Run in terminal the following commands and modify them accordingly with your preferred blacklist

```bash
# Download Script
/system script add name="BlacklistUpdater" source={/tool fetch url="https://raw.githubusercontent.com/NazgulCoder/Mikrotik-IP-Firewall/main/blacklist.txt" mode=https;
:delay 60
/import file-name=blacklist.txt;
}
 
# Script Scheduler
/system scheduler add comment="BlacklistUpdater" interval=1d \
name="BlacklistUpdater" on-event=DownloadBlacklist \
start-date=jan/01/1970 start-time=01:00:00
```

## SELFHOSTED

To deploy this project you just need Apache or NGINX and PHP (I'm using 7.3, however 8.X version should be working as well)

Make sure you change your php.ini like this

```bash
allow_url_fopen=1
```

Make sure you have cURL enabled

You can change the lists as you prefer by adding/removing items from the array

(optional)
You can configure the last part of the script to automatically push a Github commit to have your own lists always updated


## FEATURES

- Exports more than 20k entries in less than 5 sec (any free webhost works fine)
- Simple usage, just open the .php page and the txt file will be exported, and eventually pushed to Github
- All entries are sanitized for Mikrotik Syntax and duplicates are removed
- You can automate it easily with cronjob, I use free webhost with [cron-job.org](https://cron-job.org/)

Why I made this?

Personal need since there is no project like this on the internet, at least not free.
Don't get me wrong, I know there are people who make lists like I'm doing, but they do not release their tools to do so and to automatically commit updates to Github. With my project you can decide which lists add and remove, so you have 100% flexibility for your needs!


Why PHP?

- Because you don't need a VPS - Dedicated Server to run it
- Because a Free Webhost is enough
- Because PHP is fun, easy and runs everywhere
- Because PHP rocks


## LICENSE



[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

MIT License

Copyright (c) [2024] [NazgulCoder]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
