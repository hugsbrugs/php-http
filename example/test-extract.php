<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Http\Http as Http;

define('PDP_PDO_DSN', 'mysql:host=localhost;dbname=XXX');
define('PDP_PDO_USER', 'XXX');
define('PDP_PDO_PASS', 'XXX');
define('PDP_PDO_OPTIONS', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);


$url = 'https://www.free.google.com/search?q=tony+parker&oq=tony+parker';
$url = 'https://www.boom.co.uk/page1/sspage2?query=value#coucou';
// $url = 'http://www.ulb.ac.be';
// $url = 'www.google.com';


$extension = Http::extract_extension_from_url($url);
error_log('Extension : ' . $extension);

$domain = Http::extract_domain_from_url($url);
error_log('Domain : ' . $domain);

$tld = Http::extract_tld_from_url($url);
error_log('Tld : ' . $tld);

$subdomain = Http::extract_subdomain_from_url($url);
error_log('Subdomain : ' . $subdomain);

$all = Http::extract_all_from_url($url);
error_log('All : ' . print_r($all, true));

$url_2_filename = Http::url_2_filename($url);
error_log('url_2_filename : ' . $url_2_filename);
