<?php

# For PHP7
// declare(strict_types=1);

// namespace Hug\Tests\Http;

use PHPUnit\Framework\TestCase;

use Hug\Http\Http as Http;

/**
 *
 */
final class HttpTest extends TestCase
{    
    public $url1 = null;
    public $url2 = null;
    public $url3 = null;
    public $url4 = null;
    public $url5 = null;

    function __construct()
    {
        $this->url1 = 'http://www.free.fr';
        $this->url2 = 'http://www.lequipe.fr';
        $this->url3 = 'https://wine-trip.com';
        $this->url4 = 'http://www.';
        $this->url5 = 'http://www.';
    }


    /* ************************************************* */
    /* ***************** Http::nslookup **************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanNslookup()
    {
        $test = Http::nslookup('www.free.fr');
        $this->assertInternalType('string', $test);
        $this->assertEquals('212.27.48.10', $test);
    }

    /**
     *
     */
    public function testCannotNslookup()
    {
        $test = Http::nslookup('http://www.free.fr');
        $this->assertInternalType('null', $test);
        $this->assertNull($test);
    }

    /* ************************************************* */
    /* ************* Http::is_url_accessible *********** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanAccessUrl()
    {
        $test = Http::is_url_accessible('www.free.fr');
        $this->assertInternalType('boolean', $test);
        $this->assertTrue($test);
    }

    /**
     *
     */
    public function testCannotAccessUrl()
    {
        $test = Http::is_url_accessible('http://www.tatayoyomacouille.fr');
        $this->assertInternalType('boolean', $test);
        $this->assertFalse($test);
    }


    /* ************************************************* */
    /* ************** Http::get_http_code ************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGetHttpCode()
    {
        $test = Http::get_http_code('http://www.free.fr');
        $this->assertInternalType('integer', $test);
        $this->assertEquals(302, $test);
    }

    /**
     *
     */
    public function testCannotGetHttpCode()
    {
        $test = Http::get_http_code('http://www.tatayoyomacouille.fr');
        $this->assertInternalType('integer', $test);
        $this->assertEquals(0, $test);
    }

    /* ************************************************* */
    /* ************* Http::url_remove_query ************ */
    /* ************************************************* */

    /**
     *
     */
    public function testCanUrlRemoveQuery()
    {
        $test = Http::url_remove_query('http://www.free.fr/page1/souspage2?param1=value1&param2=value2');
        $this->assertInternalType('string', $test);
        $this->assertEquals('http://www.free.fr/page1/souspage2', $test);
    }

    /**
     *
     */
    public function testCannotUrlRemoveQuery()
    {
        $test = Http::url_remove_query('freecsdcfr?param1=value1&param2=value2');
        $this->assertInternalType('string', $test);
        $this->assertEquals('http://freecsdcfr', $test);
    }

    /* ************************************************* */
    /* ******** Http::url_remove_query_and_path ******** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanUrlRemoveQueryAndPath()
    {
        $test = Http::url_remove_query_and_path('http://www.free.fr/page1/souspage2?param1=value1&param2=value2');
        $this->assertInternalType('string', $test);
        $this->assertEquals('http://www.free.fr', $test);
    }

    /**
     *
     */
    public function testCannotUrlRemoveQueryAndPath()
    {
        $test = Http::url_remove_query_and_path('freecsdcfr/page1/souspage2?param1=value1&param2=value2');
        $this->assertInternalType('string', $test);
        $this->assertEquals('', $test);
    }

    /* ************************************************* */
    /* **************** Http::grab_image *************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGrabImage()
    {
        $test = Http::grab_image('https://hugo.maugey.fr/img/hugo_maugey.jpg', __DIR__ . '/../../../data/hugo_maugey.jpg');
        $this->assertInternalType('boolean', $test);
        $this->assertTrue($test);
    }

    /**
     *
     */
    public function testCannotGrabImage()
    {
        $test = Http::grab_image('https://free.fr/img/martin_carre.jpg', __DIR__ . '/../../../data/martin_carre.jpg');
        $this->assertInternalType('boolean', $test);
        $this->assertFalse($test);
    }

    /* ************************************************* */
    /* *********** Http::get_default_headers *********** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGetDefaultHeaders()
    {
        $test = Http::get_default_headers('hugo.maugey.fr');
        // $this->assertInternalType('array', $test);
        // $this->assertArrayHasKey('Connection', $test);
        // $this->assertArrayHasKey('Accept', $test);
        // $this->assertArrayHasKey('Accept-Charset', $test);
        // $this->assertArrayHasKey('Keep-Alive', $test);
        // $this->assertArrayHasKey('Accept-Language', $test);
        // $this->assertArrayHasKey('Host', $test);
    }

    /**
     *
     */
    public function testCannotGetDefaultHeaders()
    {
        $test = Http::get_default_headers(null);
        $this->assertInternalType('array', $test);
        // $this->assertArrayHasKey('Connection', $test);
        // $this->assertArrayHasKey('Accept', $test);
        // $this->assertArrayHasKey('Accept-Charset', $test);
        // $this->assertArrayHasKey('Keep-Alive', $test);
        // $this->assertArrayHasKey('Accept-Language', $test);
        // $this->assertArrayHasKey('Host', $test);
    }

    /* ************************************************* */
    /* *********** Http::extract_tld_from_url ********** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanExtractTldFromUrl()
    {
        $test = Http::extract_tld_from_url('https://www.boom.co.uk/page1/sspage2?query=value#coucou');
        $this->assertInternalType('string', $test);
        $this->assertEquals('boom.co.uk', $test);
    }

    /**
     *
     */
    public function testCannotExtractTldFromUrl()
    {
        $test = Http::extract_tld_from_url('https://www.boom.couk/page1/sspage2?query=value#coucou');
        $this->assertInternalType('string', $test);
        // $this->assertEquals('boom.co.uk', $test);
        // assertException()
    }

    /* ************************************************* */
    /* ******** Http::extract_subdomain_from_url ******* */
    /* ************************************************* */

    /**
     *
     */
    public function testCanExtractSubdomainFromUrl()
    {
        $test = Http::extract_subdomain_from_url('https://www.boom.co.uk/page1/sspage2?query=value#coucou');
        $this->assertInternalType('string', $test);
        $this->assertEquals('www', $test);
    }

    /**
     *
     */
    public function testCannotExtractSubdomainFromUrl()
    {
        $test = Http::extract_subdomain_from_url('https://www.boom.couk/page1/sspage2?query=value#coucou');
        $this->assertInternalType('string', $test);
        $this->assertEquals('www', $test);
        // assertException
    }

    /* ************************************************* */
    /* ********* Http::extract_domain_from_url ********* */
    /* ************************************************* */

    /**
     *
     */
    public function testCanExtractDomainFromUrl()
    {
        $test = Http::extract_domain_from_url('https://www.boom.co.uk/page1/sspage2?query=value#coucou');
        $this->assertInternalType('string', $test);
        $this->assertEquals('www.boom.co.uk', $test);
    }

    /**
     *
     */
    public function testCannotExtractDomainFromUrl()
    {
        $test = Http::extract_domain_from_url('https://www.boom.couk/page1/sspage2?query=value#coucou');
        $this->assertInternalType('string', $test);
        $this->assertEquals('www.boom.couk', $test);
        // assertException
    }

    /* ************************************************* */
    /* ******* Http::extract_request_headers_body ****** */
    /* ************************************************* */

    /**
     *
     */
    // public function testCanExtractRequestHeadersBody()
    // {
    //     $test = Http::extract_request_headers_body($html_with_headers);
    //     $this->assertInternalType('string', $test);
    // }


    /* ************************************************* */
    /* ************** Http::header_status ************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanHeaderStatus()
    {
        $test = Http::header_status(200);
        $this->assertInternalType('boolean', $test);
        $this->assertFalse($test);
    }

    /**
     *
     */
    public function testCannotHeaderStatus()
    {
        $test = Http::header_status(702);
        $this->assertInternalType('boolean', $test);
        $this->assertFalse($test);
    }

    /* ************************************************* */
    /* ************* Http::get_redirect_url ************ */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGetRedirectUrl()
    {
        $test = Http::get_redirect_url('http://free.fr');
        $this->assertInternalType('string', $test);
        $this->assertEquals('http://portail.free.fr/', $test);
    }

    /**
     *
     */
    public function testCannotGetRedirectUrl()
    {
        $test = Http::get_redirect_url('http://maugey.fr');
        $this->assertInternalType('string', $test);
        $this->assertEquals('https://maugey.fr/', $test);
    }

    /* ************************************************* */
    /* ************ Http::get_all_redirects ************ */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGetAllRedirects()
    {
        $test = Http::get_all_redirects('http://free.fr');
        $this->assertInternalType('array', $test);
        // $this->assertArrayHasKey('jack', $this->array1);
    }

    /**
     *
     */
    public function testCannotGetAllRedirects()
    {
        $test = Http::get_all_redirects('http://fraa.fr');
        $this->assertInternalType('array', $test);
        // $this->assertArrayHasKey('jack', $this->array1);
    }

    /* ************************************************* */
    /* ************** Http::get_final_url ************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGetFinalUrl()
    {
        $test = Http::get_final_url('http://free.fr');
        $this->assertInternalType('string', $test);
        $this->assertEquals('http://portail.free.fr/', $test);
    }

    /**
     *
     */
    public function testCannotGetFinalUrl()
    {
        $test = Http::get_final_url('http://www.fraa.fr');
        $this->assertInternalType('string', $test);
        $this->assertEquals('http://www.fraa.fr', $test);
    }

}

