<?php
namespace Test;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\ServerRequest;
use Kambo\HttpMessage\Stream;
use Kambo\HttpMessage\UploadedFile;
use Kambo\HttpMessage\Uri;

// \HttpMessage\Factories
use Kambo\HttpMessage\Factories\Enviroment\ServerRequestFactory;
use Kambo\HttpMessage\Factories\String\UriFactory;

/**
 * Unit test for the ServerRequest object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ServerRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get request target
     * 
     * @return void
     */
    public function testGetRequestTarget()
    {
        $serverRequest = $this->getEnviromentForTest();

        $this->assertEquals('/path/123?q=abc', $serverRequest->getRequestTarget());
    }

    /**
     * Test get server params - server variable should be same as the one
     * which has been used for the enviroment initialization.
     * 
     * @return void
     */
    public function testGetServerParams()
    {
        $serverRequest = $this->getEnviromentForTest();
        $expected = [
            'HTTP_HOST' => 'test.com',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36'.
            ' (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'cs-CZ,cs;q=0.8,en;q=0.6',
            'SERVER_NAME' => 'test.com',
            'SERVER_ADDR' => '10.0.2.15',
            'SERVER_PORT' => '1111',
            'REMOTE_ADDR' => '10.0.2.2',
            'REQUEST_SCHEME' => 'http',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'q=abc',
            'REQUEST_URI' => '/path/123?q=abc',
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'password',
        ];

        $this->assertEquals($expected, $serverRequest->getServerParams());
    }

    /**
     * Test get cookie params
     * 
     * @return void
     */
    public function testGetCookieParams()
    {
        $cookie        = ['foo' => 'bar', 'name' => 'value'];
        $serverRequest = $this->getEnviromentForTest([], null, null, $cookie);
        $expected      = [
            "foo" => "bar",
            "name" => "value"
        ];

        $this->assertEquals($expected, $serverRequest->getCookieParams());
    }

    /**
     * Test changing cookie params.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithCookieParams()
    {
        $cookie        = ['foo' => 'bar', 'name' => 'value'];
        $serverRequest = $this->getEnviromentForTest([], null, null, $cookie);
        $expected      = [
            "foo" => "bar",
            "name" => "value"
        ];

        $cookiesNewRequest = [
            "foo" => "bar",
            "name" => "value"
        ];

        $newServerRequest = $serverRequest->withCookieParams($cookiesNewRequest);

        $this->assertEquals($cookiesNewRequest, $newServerRequest->getCookieParams());
        $this->assertEquals($expected, $serverRequest->getCookieParams());
    }

    /**
     * Test changing request target.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithRequestTarget()
    {
        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withRequestTarget('/foo/?bar=test');

        // check if was not changed
        $this->assertEquals('/path/123?q=abc', $serverRequest->getRequestTarget());
        $this->assertEquals('/foo/?bar=test', $newRequest->getRequestTarget());
    }

    /**
     * Test get request method.
     * 
     * @return void
     */
    public function testGetMethod()
    {
        $serverRequest = $this->getEnviromentForTest();

        $this->assertEquals('GET', $serverRequest->getMethod());
    }

    /**
     * Test changing request method.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithMethod()
    {
        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withMethod('POST');

        // check if was not changed in original instance of object
        $this->assertEquals('GET', $serverRequest->getMethod());
        $this->assertEquals('POST', $newRequest->getMethod());
    }

    /**
     * Test changing request method to invalid value an exception must be thrown.
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testWithMethodInvalid()
    {
        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withMethod('TEST');
    }

    /**
     * Test get uri from the request.
     * 
     * @return void
     */
    public function testGetUri()
    {
        $serverRequest = $this->getEnviromentForTest();

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
    }

    /**
     * Test changing uri of request.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithUri()
    {
        $url    = 'http://user:password@test.com:1111/path/123?q=abc';
        $newUrl = 'http://foo.com/bar?parameter=value';

        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withUri(UriFactory::create($newUrl));

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertEquals($url, (string)$serverRequest->getUri());

        $this->assertInstanceOf(Uri::class, $newRequest->getUri());
        $this->assertEquals($newUrl, (string)$newRequest->getUri());
    }

    /**
     * Test get query params from the request.
     * 
     * @return void
     */
    public function testGetQueryParams()
    {
        $serverRequest = $this->getEnviromentForTest();
        $expected      = [
            'q' => 'abc'
        ];

        $this->assertEquals($expected, $serverRequest->getQueryParams());
    }

    /**
     * Test changing query params of request.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithQueryParams()
    {
        $serverRequest = $this->getEnviromentForTest();
        $expected      = [
            'q' => 'abc'
        ];
        $newQuery      = [
            'foo' => 'bar'
        ];

        $newRequest = $serverRequest->withQueryParams($newQuery);

        $this->assertEquals($expected, $serverRequest->getQueryParams());
        $this->assertEquals($newQuery, $newRequest->getQueryParams());
    }

    /**
     * Test get upload files
     * 
     * @return void
     */
    public function testGetUploadedFiles()
    {
        $serverRequest = $this->getEnviromentForTest();
        $this->assertEquals([], $serverRequest->getUploadedFiles());
    }

    /**
     * Test changing upload files.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithUploadedFiles()
    {
        $newFiles = [
            'upload-field' => [
                new UploadedFile('tmp/test.txt', 'test.txt', 'text/plain', 1024, 0),
                new UploadedFile('tmp/test2.txt', 'test2.txt', 'text/plain', 2048, 0)
            ],
            'second-upload-field' => [
                new UploadedFile('tmp/test3.txt', 'test3.txt', 'text/plain', 4096, 0),
                new UploadedFile('tmp/test4.txt', 'test4.txt', 'text/plain', 8192, 0)
            ]            
        ];

        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->WithUploadedFiles($newFiles);

        $this->assertEquals([], $serverRequest->getUploadedFiles());
        $this->assertEquals($newFiles, $newRequest->getUploadedFiles());
    }

    /**
     * Test changing upload files with invalid values.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithUploadedFilesInvalid()
    {
        $newFiles = [
            'foo' => 'bar'
        ];

        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->WithUploadedFiles($newFiles);
    }

    /**
     * Test get empty (null) parsed body of the request.
     * 
     * @return void
     */
    public function testGetParsedBody()
    {
        $serverRequest = $this->getEnviromentForTest();
        $this->assertEquals(null, $serverRequest->getParsedBody());
    }

    /**
     * Test get urlencoded parsed body of the request.
     * 
     * @return void
     */
    public function testGetParsedBodyFormEncode()
    {
        $serverRequest = $this->getEnviromentForTest(
            [
                'CONTENT_TYPE'=>'application/x-www-form-urlencoded'
            ],
            'test=test&submit=Test'
        );
        $expected = [
            "test" => "test",
            "submit" => "Test"
        ];

        $this->assertEquals($expected, $serverRequest->getParsedBody());
    }

    /**
     * Test get json parsed body of the request.
     * 
     * @return void
     */
    public function testGetParsedBodyJson()
    {
        $expected = [
            "test" => "test",
            "submit" => "Test"
        ];

        $serverRequest = $this->getEnviromentForTest(
            [
                'CONTENT_TYPE'=>'application/json'
            ],
            json_encode($expected)
        );

        $this->assertEquals($expected, $serverRequest->getParsedBody());
    }

    /**
     * Test get xml parsed body of the request.
     * 
     * @return void
     */
    public function testGetParsedBodyXml()
    {
        $expected = [
            "test" => "test",
            "submit" => "Test"
        ];

        $bodyXml = "<?xml version='1.0'?> 
        <document>
         <test>data</test>
         <example>here</example>
        </document>";

        $serverRequest = $this->getEnviromentForTest(['CONTENT_TYPE'=>'text/xml'], $bodyXml);
        $this->assertEquals(simplexml_load_string($bodyXml), $serverRequest->getParsedBody());
    }

    /**
     * Test with new parsed body.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithParsedBody()
    {
        $serverRequest = $this->getEnviromentForTest(
            ['CONTENT_TYPE'=>'application/x-www-form-urlencoded'],
            'test=test&submit=Test'
        );
        $expected = [
            "test" => "test",
            "submit" => "Test"
        ];
        $newBody = [
            "foo" => "bar",
            "bar" => "foo"
        ];

        $newRequest = $serverRequest->withParsedBody($newBody);

        $this->assertEquals($newBody, $newRequest->getParsedBody());
        $this->assertEquals($expected, $serverRequest->getParsedBody());
    }

    /**
     * Test with invalid, non parsed body
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testWithParsedBodyInvalidArgument()
    {
        $serverRequest = $this->getEnviromentForTest(
            ['CONTENT_TYPE'=>'application/x-www-form-urlencoded'],
            'test=test&submit=Test'
        );
        $newRequest = $serverRequest->withParsedBody('value cannot be string');
    }

    /**
     * Test get attributes from request
     * 
     * @return void
     */
    public function testGetAttributes()
    {
        $serverRequest = $this->getEnviromentForTest();
        $this->assertEquals([], $serverRequest->getAttributes());
    }

    /**
     * Test adding new attributes
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithAttribute()
    {
        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
        $this->assertEquals(null, $serverRequest->getAttribute('foo'));
    }

    /**
     * Test get attribute from request
     * 
     * @return void
     */
    public function testGetAttribute()
    {
        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
    }

    /**
     * Test get attribute from request with defualt value
     * 
     * @return void
     */
    public function testGetAttributeDefualtValue()
    {
        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('foo', $newRequest->getAttribute('foo2', 'foo'));
    }

    /**
     * Test removing attribute from the enviroment
     * 
     * @return void
     */
    public function testWithoutAttribute()
    {
        $serverRequest = $this->getEnviromentForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
        $this->assertEquals(null, $serverRequest->getAttribute('foo'));

        $newRequestWithout = $newRequest->withoutAttribute('foo');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
        $this->assertEquals(null, $newRequestWithout->getAttribute('foo'));
    }

    /**
     * Test get protocol version
     * 
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $serverRequest = $this->getEnviromentForTest();
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
    }

    // ------------ PRIVATE METHODS

    /**
     * Get instance of ServerRequest for the test
     * 
     * @return ServerRequest ServerRequest for the test
     */
    private function getEnviromentForTest($change = [], $body = '', $attributes = null, $cookies = [])
    {
        $serverForTest = array_merge([
            'HTTP_HOST' => 'test.com',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36'.
            ' (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'cs-CZ,cs;q=0.8,en;q=0.6',
            'SERVER_NAME' => 'test.com',
            'SERVER_ADDR' => '10.0.2.15',
            'SERVER_PORT' => '1111',
            'REMOTE_ADDR' => '10.0.2.2',
            'REQUEST_SCHEME' => 'http',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'q=abc',
            'REQUEST_URI' => '/path/123?q=abc',
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'password',
        ], $change);

        $memoryStream = fopen('php://memory','r+');
        fwrite($memoryStream, $body);
        rewind($memoryStream);

        $enviroment = new Enviroment($serverForTest, $memoryStream, $cookies);
        return ServerRequestFactory::fromEnviroment($enviroment);
    }
}
