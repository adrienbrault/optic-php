<?php

namespace Optic\Sdk;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ECSSerializer
{
    public function serialize(RequestInterface $request, ResponseInterface $response): string
    {
        // See https://github.com/elastic/ecs-logging-nodejs/blob/cfeebe3d51c894a4a1cfaa5c3a5b19881e6c78e8/helpers/lib/http-formatters.js#L23-L156
        $body = [
            '@timestamp' => date(\DateTime::ISO8601),
            'url' => [
                'full' => (string) $request->getUri(),
                'path' => $request->getUri()->getPath(),
                'query' => $request->getUri()->getQuery(),
                'fragment' => $request->getUri()->getFragment(),
                'domain' => $request->getUri()->getHost(),
                'port' => $request->getUri()->getPort(),
            ],
            'user_agent' => [
                'original' => $request->getHeaderLine('User-Agent'),
            ],
            'http' => [
                'version' => $request->getProtocolVersion(),
                'request' => [
                    'method' => $request->getMethod(),
                    'headers' => $this->toEcsHeaders($request->getHeaders()),
                    'body' => [
                        'content' => $request->getBody()->getContents(), // TODO memory ?
                    ],
                ],
                'response' => [
                    'status_code' => $response->getStatusCode(),
                    'headers' => $this->toEcsHeaders($response->getHeaders()),
                    'body' => [
                        'content' => $response->getBody()->getContents(), // TODO?
                    ]
                ],
            ],
        ];

        return json_encode($body);
    }

    /**
     * @param array<string, array<string>> $psrHeaders
     * @return array<string, string>
     */
    private function toEcsHeaders(array $psrHeaders): array
    {
        return array_map(
            function (array $values) {
                return join(', ', $values);
            },
            $psrHeaders
        );
    }
}
