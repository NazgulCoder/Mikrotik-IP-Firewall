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
    ('https://lists.blocklist.de/lists/all.txt', 'blocklist.de'),
    ('http://rules.emergingthreats.net/fwrules/emerging-Block-IPs.txt', 'emergingthreats'),
    ('https://raw.githubusercontent.com/ktsaou/blocklist-ipsets/master/firehol_level1.netset', 'Firehol-Level1'),
]

def is_private_ip(ip: str) -> bool:
    try:
        ip_obj = ipaddress.ip_address(ip)
        return any(ip_obj in ipaddress.ip_network(range) for range in PRIVATE_RANGES)
    except ValueError:
        return False

def parse_ip_entry(entry: str) -> tuple:
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
    output_file = 'blacklist.rsc'
    listname = 'blacklist'
    entries: Set[tuple] = set()
    
    print(f"Generating {output_file}...")
    
    for url, comment in SOURCES:
        print(f"Fetching from {url}...")
        ips = fetch_ips(url)
        
        for entry in ips:
            ip_entry, base_ip = parse_ip_entry(entry)
            
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
