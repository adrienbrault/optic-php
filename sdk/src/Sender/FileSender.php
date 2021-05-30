<?php

namespace Optic\Sdk\Sender;

class FileSender implements SenderInterface
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function send(string $message)
    {
        file_put_contents($this->path, $message.PHP_EOL, FILE_APPEND);
    }
}
