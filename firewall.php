<?php
$myfile = fopen("blacklist.txt", "w") or die("Unable to open file!");
fwrite($myfile, "# Generated on " . date("d M Y") . " at " . date("H:i:s"));
fwrite($myfile, "\n");

$list = array(
    "https://BLACKLIST-SITE.com/list.txt",
    "https://TOR-BLACKLIST.org/nodes.txt",
// add your blacklist lists
);

$listname = "blacklist";
$comments = array(
    "List 1",
    "List 2",
);

fwrite($myfile, "/ip firewall address-list\n");

foreach ($list as $key => $value) {
    $iplist = file($value, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($iplist as $ip) {
        // Skip empty lines and lines containing "#"
        if (trim($ip) == "" || strpos($ip, "#") !== false) {
            continue;
        }
        $comment = $comments[$key];
        fwrite($myfile, "add list={$listname} address={$ip} comment={$comment}\n");
    }
}

fclose($myfile);





// Github Commit Push


$access_token = 'YOUR TOKEN FOR GITHUB AUTO PUSH WITH CRONJOB';
$repo_owner = 'YOUR USERNAME ON GITHUB';
$repo_name = 'REPOSITORY NAME';
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
