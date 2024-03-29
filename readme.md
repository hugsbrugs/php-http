# php-http

This library provides PHP utilities functions to manage URLs. Read [PHP DOC](https://hugsbrugs.github.io/php-http)

[![Build Status](https://travis-ci.org/hugsbrugs/php-http.svg?branch=master)](https://travis-ci.org/hugsbrugs/php-http)
[![Coverage Status](https://coveralls.io/repos/github/hugsbrugs/php-http/badge.svg?branch=master)](https://coveralls.io/github/hugsbrugs/php-http?branch=master)

## Install

Install package with composer
```
composer require hugsbrugs/php-http
```

In your PHP code, load librairy
```php
require_once __DIR__ . '/../vendor/autoload.php';
use Hug\Http\Http as Http;
```

## Configuration

In order to use cache mechanism, define following constants
```php
define('PDP_PDO_DSN', 'mysql:host=localhost;dbname=database');
define('PDP_PDO_USER', 'username');
define('PDP_PDO_PASS', 'password');
define('PDP_PDO_OPTIONS', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
```

Alternatively define path to the local stored [public suffix list](https://publicsuffix.org/list/public_suffix_list.dat)
```php
define('PUBLIC_SUFFIX_LIST', realpath(__DIR__ . '/../../../cache/public_suffix_list.dat'));
```
This method should not be used in production since it's really slow.

Otherwise the default, not accurate, cache/public_suffix_list.dat file will be used.

## Usage

Execute shell nslookup command
```php
Http::nslookup($url);
```

Check if an url is accessible (means not a 404)
```php
Http::is_url_accessible($url);
```

Returns HTTP code for given URL
```php
Http::get_http_code($url);
```

Cleans an url from its query parameters
```php
Http::url_remove_query($url);
```

Cleans an url from its query parameters and path
```php
Http::url_remove_query_and_path($url);
```

Quick and dirty function to save an image from the internet
```php
Http::grab_image($url, $save_to);
```

Returns basic HTTP headers for a CURL request
```php
Http::get_default_headers($host);
```

Extracts suffix, tld, domain and subdomain from an URL
```php
Http::extract_all_from_url($url);
```

Extracts extention from an URL
```php
Http::extract_extension_from_url($url);
```

Extracts scheme (ftp, http) from an URL
```php
Http::extract_scheme_from_url($url);
```

Extracts a TLD (Top Level Domain) from an URL
```php
Http::extract_tld_from_url($url);
```

Extracts a sub domain from an URL
```php
Http::extract_subdomain_from_url($url);
```

Extracts a domain name from an URL
```php
Http::extract_domain_from_url($url);
```

Separates Headers from Body in CURL response
```php
Http::extract_request_headers_body($html_with_headers);
```

Sets a php script desired status code (usefull for API)
```php
Http::header_status($statusCode);
```

Gets the address and/or http code that the provided URL redirects to. $return can be : url/code/all
```php
Http::get_redirect_url($url, $timeout = 5, $return = 'url');
```

Follows and collects all redirects, in order, for the given URL.
```php
Http::get_all_redirects($url);
```

Gets the address and/or http code that the URL ultimately leads to. $return can be : url/code/all
```php
Http::get_final_url($url, $return = 'url');
```

Check a TXT record in domain zone file
```php
Http::check_txt_record($domain, $txt);
```

Waits and tests every minute if domain zone has correct IP adress and TXT record set
```php
Http::wait_for_zone_ok($domain, $ip, $txt_record, $wait_minutes = 15);
```

Tests if domain zone has correct IP adress and TXT record set
```php
Http::is_zone_ok($domain, $ip, $txt_record);
```

Get name servers of given domain
```php
Http::get_name_servers('maugey.fr');
```

Add escaped fragment to URL
```php
Http::add_escaped_fragment($url);
```

To enable CORS, put this line at top of your PHP script
```php
Http::cors();
```

Converts an URL to a filename
It does not encode URL parameters (only scheme - domain - folders - file)
```php
Http::url_2_filename($url);
```

## Dependecies
https://github.com/jeremykendall/php-domain-parser
https://github.com/jeremykendall/php-domain-parser/tree/5.7.0
https://publicsuffix.org/list/public_suffix_list.dat


## Unit Tests

```
composer exec phpunit
phpunit --configuration phpunit.xml
```

## Author

Hugo Maugey [Webmaster](https://hugo.maugey.fr/webmaster) | [Consultant SEO](https://hugo.maugey.fr/consultant-seo) | [Fullstack developer](https://hugo.maugey.fr/developpeur-web)