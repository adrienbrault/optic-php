<?php

namespace Optic\Sdk;

class IngestUrlResolver
{
    private $dev;

    public function __construct(bool $dev)
    {
        $this->dev = $dev;
    }

    public function resolve(): string
    {
        $command = $this->getOpticCommand();

        if (!$this->commandExists($command)) {
            throw new \Exception('Please install the Optic CLI: https://useoptic.com/docs/');
        }

        $output = shell_exec(sprintf('%s ingest:ingest-url', $command));

        $matches = [];
        if (!is_string($output)
            || !preg_match('/^ingestUrl:\s+(?P<url>\S+)/', $output, $matches)
        ) {
            throw new \Exception('Ingest url could not be found running %s', $command);
        }

        return $matches['url'];
    }

    private function getOpticCommand(): string
    {
        if ($this->dev) {
            return 'apidev';
        }

        return 'api';
    }

    private function commandExists(string $command): bool
    {
        return null !== shell_exec(sprintf('command -v %s', $command));
    }
}
