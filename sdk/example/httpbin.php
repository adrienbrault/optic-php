<?php

require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Handler\StreamHandler;
use Http\Adapter\Guzzle7\Client;
use Http\Discovery\Psr17FactoryDiscovery;
use Optic\Sdk\Optic;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

// Setup
$client = Client::createWithConfig([
    'handler' => new StreamHandler(),
]);

$psrHttpFactory = new PsrHttpFactory(
    Psr17FactoryDiscovery::findServerRequestFactory(),
    Psr17FactoryDiscovery::findStreamFactory(),
    Psr17FactoryDiscovery::findUploadedFileFactory(),
    Psr17FactoryDiscovery::findResponseFactory()

);
$httpFoundationFactory = new HttpFoundationFactory();

// Optic
$opticClient = new Optic();

// Action!
$request = SymfonyRequest::createFromGlobals();

$httpBinRequest = $psrHttpFactory->createRequest($request);

$httpBinRequest = $httpBinRequest->withUri(
    $httpBinRequest->getUri()
        ->withHost('httpbin.org')
        ->withPort(null)
);

$httpBinResponse = $client->sendRequest($httpBinRequest);

$opticClient->sendToConsole($httpBinRequest, $httpBinResponse);

$response = $httpFoundationFactory->createResponse($httpBinResponse);
$response->send();
