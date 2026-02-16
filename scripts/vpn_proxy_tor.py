#!/usr/bin/env python3
import requests
import ipaddress
from datetime import datetime
from typing import List, Set

PRIVATE_RANGES = [
    '10.0.0.0/8', '100.64.0.0/10', '127.0.0.0/8', '169.254.0.0/16',
    '172.16.0.0/12', '192.0.0.0/24', '192.0.2.0/24', '192.168.0.0/16',
    '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24', '224.0.0.0/4',
    '240.0.0.0/4', '255.255.255.255/32'
]

SOURCES = [
    ('https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt', 'VPN'),
    ('https://check.torproject.org/torbulkexitlist?ip=1.1.1.1&port=80', 'TOR'),
    ('https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt', 'Proxy'),
    ('https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt', 'Proxy2'),
    ('https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks4.txt', 'Proxy3'),
    ('https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks5.txt', 'Proxy4'),
    ('https://raw.githubusercontent.com/hookzof/socks5_list/master/proxy.txt', 'Proxy5'),
    ('https://raw.githubusercontent.com/jetkai/proxy-list/main/online-proxies/txt/proxies.txt', 'Proxy6'),
    ('https://raw.githubusercontent.com/alireza-rezaee/tor-nodes/main/latest.all.csv', 'TOR-CSV'),
    ('https://raw.githubusercontent.com/NazgulCoder/IPLists/refs/heads/main/resources/cloudflare-warp.txt', 'Cloudflare-WARP'),
]

def is_private_ip(ip: str) -> bool:
    try:
        ip_obj = ipaddress.ip_address(ip)
        return any(ip_obj in ipaddress.ip_network(range) for range in PRIVATE_RANGES)
    except ValueError:
        return False

def parse_ip_entry(entry: str, is_csv: bool = False) -> tuple:
    if is_csv:
        parts = entry.split(',')
        if len(parts) >= 2:
            ip_str = parts[1].strip()
            try:
                ip_obj = ipaddress.ip_address(ip_str)
                if ip_obj.version == 4:
                    return ip_str, ip_str
            except ValueError:
                pass
        return None, None
    
    for delimiter in [';', '#']:
        if delimiter in entry:
            entry = entry.split(delimiter)[0]
    
    entry = entry.strip()
    if not entry:
        return None, None
    
    if '/' in entry:
        try:
            network = ipaddress.ip_network(entry, strict=False)
            if network.version == 4:
                return str(network), str(network.network_address)
        except ValueError:
            return None, None
    else:
        if ':' in entry:
            entry = entry.split(':')[0]
        
        try:
            ip_obj = ipaddress.ip_address(entry)
            if ip_obj.version == 4:
                return entry, entry
        except ValueError:
            return None, None
    
    return None, None

def fetch_ips(url: str) -> List[str]:
    try:
        response = requests.get(url, timeout=30)
        response.raise_for_status()
        return response.text.strip().split('\n')
    except Exception as e:
        print(f"Error fetching {url}: {e}")
        return []

def generate_list():
    output_file = 'vpn-proxy-tor.rsc'
    listname = 'VPN'
    entries: Set[tuple] = set()
    
    print(f"Generating {output_file}...")
    
    for idx, (url, comment) in enumerate(SOURCES):
        print(f"Fetching from {url}...")
        ips = fetch_ips(url)
        is_csv = (idx == 8)  # CSV source
        
        for entry in ips:
            ip_entry, base_ip = parse_ip_entry(entry, is_csv)
            
            if not ip_entry or not base_ip:
                continue
            
            if is_private_ip(base_ip):
                continue
            
            entries.add((ip_entry, comment))
    
    with open(output_file, 'w') as f:
        f.write(f"# Generated on {datetime.now().strftime('%d %b %Y')} at {datetime.now().strftime('%H:%M:%S')}\n")
        f.write(":do {/ip firewall address-list\n")
        
        for ip_entry, comment in sorted(entries):
            f.write(f":do {{add address={ip_entry} list={listname} comment={comment} timeout=23h}} on-error={{}}\n")
        
        f.write("}")
    
    print(f"✓ Generated {output_file} with {len(entries)} unique entries")

if __name__ == '__main__':
    generate_list()
