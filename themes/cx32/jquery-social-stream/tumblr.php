<?php
ini_set('display_errors', 0);

function dc_curl_get_contents($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
        
    curl_close($ch);
    
    return $data;
}


$id = isset($_GET['id']) ? $_GET['id'] : '';
$count = isset($_GET['count']) ? $_GET['count'] : 20;
$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
$url = 'http://'. $id . '.tumblr.com/api/read/json?callback='. $callback .'&num='. $count;

$response = file_get_contents($url);
if ($response === false) {
    $response = dc_curl_get_contents($url);    
}

echo $response;





