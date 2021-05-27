<?php

namespace Optic\Sdk;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Optic
{
    public const VERSION = '0.1.0-dev';

    /**
     * @var bool
     */
    private $dev;

    /**
     * @var bool
     */
    private $local;

    /**
     * @var bool
     */
    private $console;

    /**
     * @var bool
     */
    private $log;

    public static function create(
        ?bool $dev = null,
        ?bool $local = null,
        ?bool $console = null,
        ?bool $log = null
    ): self {
        $dev = $dev ?? ((bool) getenv('OPTIC_DEV')) ?? false;
        $local = $local ?? ((bool) getenv('OPTIC_LOCAL')) ?? false;
        $console = $console ?? ((bool) getenv('OPTIC_CONSOLE')) ?? false;
        $log = $log ?? ((bool) getenv('OPTIC_LOG')) ?? false;

        return new self($dev, $local, $console, $log);
    }

    public function __construct(bool $dev, bool $local, bool $console, bool $log)
    {
        $this->dev = $dev;
        $this->local = $local;
        $this->console = $console;
        $this->log = $log;
    }

    public function capture(RequestInterface $request, ResponseInterface $response): void
    {
        $message = $this->serialize($request, $response);

        if ($this->console) {
            $this->sendToConsole($message);
        }

        if ($this->log) {
            $this->sendToLog($message);
        }
    }

    private function sendToConsole(string $message): void
    {
        error_log($message).PHP_EOL;
    }

    private function sendToLog(string $message): void
    {
        file_put_contents('./optic.log', $message.PHP_EOL, FILE_APPEND);
    }

    private function serialize(RequestInterface $request, ResponseInterface $response): string
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
