# Mikrotik-IP-Firewall-Updater
Mikrotik IP Firewall Updater can be considered a fork of my previous [ipfirewall-updater](https://github.com/NazgulCoder/ipfirewall-updater)

## BLACKLISTS

Blacklists are updated daily, I will always try to improve them, optimize and add more sources, do not hesitate to open a pull request.

[vpn-proxy-tor.txt](https://raw.githubusercontent.com/NazgulCoder/Mikrotik-IP-Firewall/main/vpn-proxy-tor.txt) includes the following blacklists:

- [X4BNet VPN Blacklist](https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt)
- [TOR Blacklist](https://check.torproject.org/torbulkexitlist?ip=1.1.1.1&port=80)

[blacklist.txt](https://raw.githubusercontent.com/NazgulCoder/Mikrotik-IP-Firewall/main/blacklist.txt) includes all vpn-proxy-tor.txt blacklists plus the following ones:

- [blocklist.de](https://lists.blocklist.de/lists/all.txt)
- [dshield Top 1000](https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/dshield_top_1000.ipset)
- [emergingthreats](http://rules.emergingthreats.net/fwrules/emerging-Block-IPs.txt)


## SELFHOSTED

To deploy this project you just need Apache or NGINX and PHP (I used 7.4)

Make sure you change your php.ini like this

```bash
allow_url_fopen=1
```

Make sure you have cURL enabled

You can change the lists as you prefer by adding/removing items from the array

(optional)
You can configure the last part of the script to automatically push a Github commit to have your own lists always updated


## FEATURES

- Exports more than 20k entries in less than 10 sec (locally hosted with XAMPP/UwAmp)
- Simple usage, just open the .php page and the txt file will be exported
- You can automate it easily with cronjob, I use free webhost with [cron-job.org](https://cron-job.org/)

Why I made this?

Personal need since there is no project like this on the internet, at least not free





## LICENSE



[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

MIT License

Copyright (c) [2023] [NazgulCoder]

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
