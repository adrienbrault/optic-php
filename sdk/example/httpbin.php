<?php

require __DIR__.'/../vendor/autoload.php';

use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Optic\Sdk\Optic;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

// Setup
$client = HttpAsyncClientDiscovery::find();

$psrHttpFactory = new PsrHttpFactory(
    Psr17FactoryDiscovery::findServerRequestFactory(),
    Psr17FactoryDiscovery::findStreamFactory(),
    Psr17FactoryDiscovery::findUploadedFileFactory(),
    Psr17FactoryDiscovery::findResponseFactory()

);
$httpFoundationFactory = new HttpFoundationFactory();

// Optic
$opticClient = Optic::create();

// Action!
$request = SymfonyRequest::createFromGlobals();

$httpBinRequest = $psrHttpFactory->createRequest($request);

$httpBinRequest = $httpBinRequest->withUri(
    $httpBinRequest->getUri()
        ->withHost('httpbin.org')
        ->withPort(null)
);

$httpBinResponse = $client->sendAsyncRequest($httpBinRequest)->wait(true);

$opticClient->capture($httpBinRequest, $httpBinResponse);

$response = $httpFoundationFactory->createResponse($httpBinResponse);
$response->send();
