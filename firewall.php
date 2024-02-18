<?php
$myfile = fopen("blacklist.txt", "w") or die("Unable to open file!");
fwrite($myfile, "# Generated on " . date("d M Y") . " at " . date("H:i:s"));
fwrite($myfile, "\n");

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

fwrite($myfile, "/ip firewall address-list\n");

foreach ($list as $key => $value) {
    $iplist = file($value, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($iplist as $ip) {
        // Skip empty lines and lines containing "#"
        if (trim($ip) == "" || strpos($ip, "#") !== false || strpos($ip, ";") === 0) {
            continue;
        }
        // Remove text after semicolon
        $semicolon_pos = strpos($ip, ";");
        if ($semicolon_pos !== false) {
            $ip = substr($ip, 0, $semicolon_pos);
        }

        // Split IP address and port number
        $ip_parts = explode(':', $ip);
        $ip_address = $ip_parts[0];
        $comment = $comments[$key];
        fwrite($myfile, "add list={$listname} address={$ip_address} comment={$comment}\n");
        
    }
}

fclose($myfile);

// Read contents of file into array
$lines = file('blacklist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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
file_put_contents('blacklist.txt', implode("\n", $unique_lines));





// Github Commit Push


$access_token = 'YOUR GITHUB TOKEN';
$repo_owner = 'YOUR GITHUB NAME';
$repo_name = 'YOUR GITHUB REPOSITORY NAME';
$branch = 'main';
$file_path = 'blacklist.txt';
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
