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

Gets the address that the provided URL redirects to, or FALSE if there's no redirect.
```php
Http::get_redirect_url($url);
```

Follows and collects all redirects, in order, for the given URL.
```php
Http::get_all_redirects($url);
```

Gets the address that the URL ultimately leads to.
```php
Http::get_final_url($url);
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

## Unit Tests

```
composer exec phpunit
```

## Author

Hugo Maugey [Webmaster](https://hugo.maugey.fr/webmaster) | [Consultant SEO](https://hugo.maugey.fr/consultant-seo) | [Fullstack developer](https://hugo.maugey.fr/developpeur-web)