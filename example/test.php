<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Http\Http as Http;

// https://stackoverflow.com/questions/10288130/php-curl-following-redirects
$url = 'https://lady-sushi.fr';


// $redir = Http::get_final_url($url);
// echo $redir;

$redir = Http::get_all_redirects($url);
print_r($redir);

// $redir = Http::get_redirect_url($url);
// echo $redir;

exit;

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

$filename = Http::url_2_filename($url);
error_log('filename : ' . $filename);


exit;

$test = Http::grab_image('https://naturo-paca.fr/img/martin_carre.jpg', __DIR__ . '/../data/martin_carre.jpg');

exit;

# Curl Request with headers (test with proxy)

$filename = __DIR__ . '/free-headers.html';
$html = file_get_contents($filename);
$req = Http::extract_request_headers_body($html);
error_log(print_r($req['headers'], true));


exit;

$test = Http::grab_image('https://hugo.maugey.fr/img/hugo_maugey.jpg', __DIR__ . '/../data/hugo_maugey.jpg');
error_log($test);

