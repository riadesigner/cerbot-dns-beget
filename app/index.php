<?php
require_once 'vendor/autoload.php';

$parent = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($parent);
$dotenv->load();


$login = urlencode($_ENV['BEGET_LOGIN']);
$pass = urlencode($_ENV['BEGET_API_PASS']);
$domain = idn_to_ascii($_ENV['DOMAIN_NAME']); 

$query = implode("",[
    "login={$login}&passwd={$pass}&input_format=json",
    "&output_format=json&input_data=",
    urlencode('{"fqdn":"'.$domain.'"}'),
]);

$url =  "https://api.beget.com/api/dns/getData?".$query;
$res = get_from_url_2($url);

echo "<pre>";
print_r($res);
echo "</pre>";


/**
 * THE SIMPLEST 
 * WAY TO GET JSON FROM URL 
 */
function get_from_url($url): array{
    $response = file_get_contents($url);
    return json_decode($response, true);
}

/**
 * ANOTHER WAY
 * WAY TO GET JSON FROM URL 
 */
function get_from_url_2($url): array{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}


?>