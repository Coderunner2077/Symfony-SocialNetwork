<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
    
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
if(PHP_VERSION_ID < 70000)
    include_once __DIR__.'/../var/bootstrap.php.cache';
Debug::enable();

$kernel = new AppKernel('prod', false);

//$kernel->loadClassCache(); deprecated 
//$kernel = new AppCache($kernel);

Request::setTrustedProxies(['192.0.0.1', '10.0.0.0/8'], Request::HEADER_X_FORWARDED_ALL);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
