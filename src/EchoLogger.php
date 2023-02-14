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

        $datetime = date('Y-m-d H:i:s');

        echo '[' . $datetime . ' ' . $level . '] ' . $message . PHP_EOL;
    }
}
