<?php

namespace Optic\Sdk\Sender;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Optic\Sdk\IngestUrlResolver;
use Psr\Http\Message\RequestFactoryInterface;

class HttpSender implements SenderInterface
{
    /**
     * @var IngestUrlResolver
     */
    private $ingestUrlResolver;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    public function __construct(
        IngestUrlResolver $ingestUrlResolver,
        HttpClient $httpClient = null,
        RequestFactoryInterface $requestFactory = null
    ) {
        $this->ingestUrlResolver = $ingestUrlResolver;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
    }

    public function send(string $message)
    {
        $ingestUrl = $this->ingestUrlResolver->resolve();

        $request = $this->requestFactory->createRequest(
            'POST',
            $ingestUrl
        );
        $request = $request->withBody($message);
        $request = $request->withHeader('Content-Type', 'application/json');

        $response = $this->httpClient->sendRequest($request);

        assert($response->getStatusCode() === 200);
    }
}
