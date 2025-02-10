<?php
$myfile = fopen("blacklist.rsc", "w") or die("Unable to open file!");
fwrite($myfile, "# Generated on " . date("d M Y") . " at " . date("H:i:s"));
fwrite($myfile, "\n");

// Define the private and special-use IP ranges
$private_ranges = array(
    '10.0.0.0/8',       // Private range
    '172.16.0.0/12',    // Private range
    '192.168.0.0/16',   // Private range
    '127.0.0.0/8',      // Loopback
    '169.254.0.0/16',   // Link-local
    '100.64.0.0/10',    // Shared address space
);

// Define the whitelist (excluded IPs)
$exclude_ips = array(
    "151.139.128.10",
    "76.76.21.21",
);

// Function to check if an IP is in a given CIDR range
function ip_in_range($ip, $range) {
    list($subnet, $bits) = explode('/', $range);
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);
    if ($ip_long === false || $subnet_long === false) {
        return false; // Invalid IP or subnet
    }
    $mask = -1 << (32 - $bits);
    $subnet_long &= $mask;
    return ($ip_long & $mask) === $subnet_long;
}

// Function to remove the port number from an IP address
function remove_port_from_ip($ip_with_port) {
    $ip_parts = explode(':', $ip_with_port);
    return filter_var($ip_parts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ip_parts[0] : false;
}

// Function to validate CIDR notation
function validate_cidr($cidr) {
    list($ip, $mask) = explode('/', $cidr);
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false; // Invalid base IP
    }
    if ($mask < 0 || $mask > 32) {
        return false; // Invalid subnet mask
    }
    return true;
}

// Function to check if an IP is in the whitelist
function is_whitelisted($ip, $whitelist) {
    foreach ($whitelist as $entry) {
        if (strpos($entry, '/') !== false) {
            // Check if the IP is in a whitelisted CIDR range
            if (ip_in_range($ip, $entry)) {
                return true;
            }
        } else {
            // Check if the IP matches a specific whitelisted IP
            if ($ip === $entry) {
                return true;
            }
        }
    }
    return false;
}

// List of URLs to fetch IP addresses
$list = array(
    "https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt",
    "https://check.torproject.org/torbulkexitlist?ip=1.1.1.1&port=80",
    "https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt",
    "https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt",
    "https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks4.txt",
    "https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks5.txt",
    "https://raw.githubusercontent.com/hookzof/socks5_list/master/proxy.txt",
    "https://raw.githubusercontent.com/jetkai/proxy-list/main/online-proxies/txt/proxies.txt",
    "https://lists.blocklist.de/lists/all.txt",
    "https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/dshield_top_1000.ipset",
    "http://rules.emergingthreats.net/fwrules/emerging-Block-IPs.txt",
    "https://www.spamhaus.org/drop/drop.txt",
    "https://www.spamhaus.org/drop/edrop.txt",
);

$listname = "blacklist";
$comments = array(
    "VPN",
    "TOR",
    "Proxy",
    "Proxy2",
    "Proxy3",
    "Proxy4",
    "Proxy5",
    "Proxy6",
    "blocklist.de",
    "dshield-top-1000",
    "emergingthreats",
    "Spamhaus-DROP",
    "Spamhaus-EDROP",
);

// Start writing to the file
fwrite($myfile, ":do {/ip firewall address-list\n");

foreach ($list as $key => $value) {
    // Fetch the IP list from the URL
    $iplist = @file($value, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Check if fetching was successful
    if (!$iplist || empty($iplist)) {
        echo "Failed to fetch data from: $value\n";
        continue; // Skip this source if it fails
    }

    foreach ($iplist as $ip_entry) {
        // Skip empty lines
        if (trim($ip_entry) == "") {
            continue;
        }

        // Remove text after semicolon or hash
        $semicolon_pos = strpos($ip_entry, ";");
        $hash_pos = strpos($ip_entry, "#");
        if ($semicolon_pos !== false) {
            $ip_entry = substr($ip_entry, 0, $semicolon_pos);
        }
        if ($hash_pos !== false) {
            $ip_entry = substr($ip_entry, 0, $hash_pos);
        }

        // Trim whitespace
        $ip_entry = trim($ip_entry);

        // Handle CIDR notation
        if (strpos($ip_entry, '/') !== false) {
            if (!validate_cidr($ip_entry)) {
                echo "Skipping invalid CIDR entry: $ip_entry\n";
                continue;
            }
            $ip = explode('/', $ip_entry)[0]; // Extract base IP for validation
        } else {
            // Handle IPs with ports
            $ip = remove_port_from_ip($ip_entry);
            if (!$ip) {
                echo "Skipping invalid IP with port: $ip_entry\n";
                continue;
            }
        }

        // Validate IP format
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            echo "Skipping invalid IP: $ip\n";
            continue; // Skip invalid IPs
        }

        // Check if the IP is whitelisted
        if (is_whitelisted($ip, $exclude_ips)) {
            echo "Skipping whitelisted IP: $ip\n";
            continue;
        }

        // Check if IP is in a private range
        $is_private = false;
        foreach ($private_ranges as $range) {
            if (ip_in_range($ip, $range)) {
                $is_private = true;
                break;
            }
        }

        // Debugging: Log private IPs
        if ($is_private) {
            echo "Skipping private IP: $ip\n";
            continue;
        }

        // Add valid IP or CIDR range to the blacklist
        $comment = $comments[$key];
        fwrite($myfile, ":do {add address={$ip_entry} list={$listname} comment={$comment} timeout=23h} on-error={}\n");
    }
}

// Final parenthesis for good syntax
fwrite($myfile, "}");
fclose($myfile);

// Read contents of file into array
$lines = file('blacklist.rsc', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$unique_lines = array();
$processed_lines = array();

foreach ($lines as $line) {
    // Remove excess spaces
    $line = preg_replace('/\s+/', ' ', $line);

    // Check if line contains the string "comment"
    if (strpos($line, 'comment') !== false) {
        // Split line at the "comment" keyword
        $split_line = explode('comment', $line, 2);

        // Check if first half of line is unique
        if (!in_array($split_line[0], $processed_lines)) {
            // Add first half of line to array of processed lines
            $processed_lines[] = $split_line[0];

            // Reconstruct line with IP and comment only
            $unique_lines[] = trim($split_line[0]) . ' comment' . trim($split_line[1]);
        }
    } else {
        // Check if line is unique
        if (!in_array($line, $processed_lines)) {
            // Add line to array of processed lines
            $processed_lines[] = $line;

            // Add line to array of unique lines
            $unique_lines[] = $line;
        }
    }
}

// Write unique lines back to file
file_put_contents('blacklist.rsc', implode("\n", $unique_lines));





// Github Commit Push


$access_token = 'YOUR GITHUB TOKEN';
$repo_owner = 'YOUR GITHUB NAME';
$repo_name = 'YOUR GITHUB REPOSITORY NAME';
$branch = 'main';
$file_path = 'blacklist.rsc';
$file_content = file_get_contents($file_path);

// Build the commit message
$commit_message = 'Updated ' . $file_path;

// Get the current file contents
$ch = curl_init("https://api.github.com/repos/{$repo_owner}/{$repo_name}/contents/{$file_path}?ref={$branch}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: token ' . $access_token,
    'User-Agent: PHP'
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $result = json_decode($response);
    $current_sha = $result->sha;
    $current_content = base64_decode($result->content);
} else {
    echo 'Error getting file contents: ' . $http_code;
    exit;
}

// Create the new file content
$new_content = $file_content;

// Create the new file content encoded in base64
$new_content_base64 = base64_encode($new_content);

// Build the request data
$request_data = [
    'message' => $commit_message,
    'content' => $new_content_base64,
    'sha' => $current_sha,
    'branch' => $branch
];

// Commit the changes
$ch = curl_init("https://api.github.com/repos/{$repo_owner}/{$repo_name}/contents/{$file_path}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: token ' . $access_token,
    'User-Agent: PHP',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo 'Changes committed successfully.';
} else {
    echo 'Error committing changes: ' . $http_code;
}

?>
