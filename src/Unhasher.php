<?php

namespace Unhasher;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Unhasher\HashComparators\ExactComparator;
use Unhasher\HashComparators\HashComparatorInterface;

class Unhasher
{
    private $hashFunction;
    private int $cardNumberLength;

    private HashComparatorInterface $hashComparator;
    private LoggerInterface $logger;

    public function __construct(
        callable $hashFunction,
        int $cardNumberLength = 16,
        ?HashComparatorInterface $hashComparator = null,
        ?LoggerInterface $logger = null
    ) {
        $this->hashFunction = $hashFunction;
        $this->cardNumberLength = $cardNumberLength;

        $this->hashComparator = $hashComparator ?? new ExactComparator();
        $this->logger = $logger ?? new NullLogger();
    }

    public function unhash(string $hashMask, string $firstDigits, string $lastDigits): ?string
    {
        $firstDigitsLength = strlen($firstDigits);
        $lastDigitsLength = strlen($lastDigits);

        $haveToGuessDigits = $this->cardNumberLength - $firstDigitsLength - $lastDigitsLength;

        $max = pow(10, $haveToGuessDigits) - 1;

        for ($i = 0; $i <= $max; $i++) {
            $this->logger->debug($i . ' / ' . $max);

            $guess = str_pad($i, $haveToGuessDigits, '0', STR_PAD_LEFT);
            $cardNumber = $firstDigits . $guess . $lastDigits;

            if (!LuhnChecker::check($cardNumber)) {
                continue;
            }

            $hashedCardNumber = $this->hash($cardNumber);

            if ($this->hashComparator->compare($hashedCardNumber, $hashMask)) {
                return $cardNumber;
            }
        }

        $this->logger->error($hashMask . ' not found');

        return null;
    }

    private function hash(string $cardNumber): string
    {
        return call_user_func($this->hashFunction, $cardNumber);
    }

    public function setHashComparator(HashComparatorInterface $hashComparator): void
    {
        $this->hashComparator = $hashComparator;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
