<?php

namespace Optic\Sdk;

use Http\Client\HttpAsyncClient;
use Optic\Sdk\Sender\ConsoleSender;
use Optic\Sdk\Sender\FileSender;
use Optic\Sdk\Sender\HttpSender;
use Optic\Sdk\Sender\SenderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Optic
{
    public const VERSION = '0.1.0-dev';

    /**
     * @var array<SenderInterface>
     */
    private $senders;

    /**
     * @var ECSSerializer
     */
    private $serializer;

    public static function create(
        ?bool $dev = null,
        ?bool $local = null,
        ?bool $console = null,
        ?bool $log = null,
        HttpAsyncClient $httpClient = null
    ): self {
        $dev = $dev ?? ((bool) getenv('OPTIC_DEV')) ?? false;
        $local = $local ?? ((bool) getenv('OPTIC_LOCAL')) ?? false;
        $console = $console ?? ((bool) getenv('OPTIC_CONSOLE')) ?? false;
        $log = $log ?? ((bool) getenv('OPTIC_LOG')) ?? false;

        $senders = [];
        if ($console) {
            $senders[] = new ConsoleSender();
        }
        if ($log) {
            $senders[] = new FileSender('./optic.log');
        }
        if ($local) {
            $senders[] = new HttpSender(
                new IngestUrlResolver($dev),
                $httpClient
            );
        }

        return new self($senders);
    }

    /**
     * @param array<SenderInterface> $senders
     */
    public function __construct(array $senders = [], ECSSerializer $serializer = null)
    {
        $this->senders = $senders;
        $this->serializer = $serializer ?: new ECSSerializer();
    }

    public function capture(RequestInterface $request, ResponseInterface $response): void
    {
        $message = $this->serializer->serialize($request, $response);

        foreach ($this->senders as $sender) {
            $sender->send($message);
        }
    }
}
