<?php

namespace Optic\Sdk\Sender;

class ConsoleSender implements SenderInterface
{
    public function send(string $message)
    {
        error_log($message).PHP_EOL;
    }
}
