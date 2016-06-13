# kambo httpmessage
[![Build Status](https://img.shields.io/travis/kambo-1st/HttpMessage.svg?branch=master&style=flat-square)](https://travis-ci.org/kambo-1st/HttpMessage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kambo-1st/HttpMessage/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kambo-1st/HttpMessage/?branch=master)

Just another PHP implementation of PSR-7 - HTTP message interfaces

## Install

Prefered way to install library is with composer:
```sh
composer require kambo/httpmessage
```

## Usage

### Server request
Creation of ServerRequest instance that encapsulates all data as it has arrived to the
application from the CGI and/or PHP environment:

```php
$enviroment    = new Enviroment($_SERVER, $_COOKIE, $_FILES, file_get_contents('php://input'));
$serverRequest = ServerRequestFactory::fromEnviroment($enviroment);
```

## License
The MIT License (MIT), https://opensource.org/licenses/MIT