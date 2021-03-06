<?php
namespace Test;

// \HttpMessage
use Kambo\HttpMessage\Uri;
use Kambo\HttpMessage\Factories\String\UriFactory;

/**
 * Unit test for the UriFactory object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating url from string.
     * 
     * @return void
     */
    public function testCreate()
    {
        $uri = UriFactory::create(
            'http://user:password@test.com:1111/path/123?q=abc#test'
        );

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('user:password', $uri->getUserInfo());
        $this->assertEquals('test.com', $uri->getHost());
        $this->assertEquals(1111, $uri->getPort());
        $this->assertEquals('/path/123', $uri->getPath());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('test', $uri->getFragment());
    }
}
