<?php

namespace Unhasher;

use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{
    public function log($level, $message, array $context = []): void
    {
        if ($level === 'debug') {
            return;
        }

        echo '[' . $level . '] ' . $message . PHP_EOL;
    }
}
