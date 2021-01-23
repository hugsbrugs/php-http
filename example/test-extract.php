<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Http\Http as Http;


$url = 'https://www.free.google.com/search?q=tony+parker&oq=tony+parker';
// $url = 'www.google.com';

$extension = Http::extract_extension_from_url($url);
error_log('Extension : ' . $extension);

$domain = Http::extract_domain_from_url($url);
error_log('Domain : ' . $domain);

$tld = Http::extract_tld_from_url($url);
error_log('Tld : ' . $tld);

$subdomain = Http::extract_subdomain_from_url($url);
error_log('Subdomain : ' . $subdomain);

