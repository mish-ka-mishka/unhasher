<?php

namespace Unhasher;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Unhasher\HashComparators\ExactComparator;
use Unhasher\HashComparators\HashComparatorInterface;

class Unhasher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $hashFunction;
    private int $cardNumberLength;

    private array $suggestedPans = [];

    private HashComparatorInterface $hashComparator;

    public function __construct(
        callable $hashFunction,
        int $cardNumberLength = 16,
        ?HashComparatorInterface $hashComparator = null,
        ?LoggerInterface $logger = null
    ) {
        $this->hashFunction = $hashFunction;
        $this->cardNumberLength = $cardNumberLength;

        $this->hashComparator = $hashComparator ?? new ExactComparator();
        $this->setLogger($logger ?? new NullLogger());
    }

    public function setSuggestedPans(array $suggestedPans): void
    {
        $this->suggestedPans = $suggestedPans;
    }

    public function unhash(string $hashMask, string $firstDigits = '', string $lastDigits = ''): ?string
    {
        $firstDigitsLength = strlen($firstDigits);
        $lastDigitsLength = strlen($lastDigits);

        $haveToGuessDigits = $this->cardNumberLength - $firstDigitsLength - $lastDigitsLength;

        if ($haveToGuessDigits < 0) {
            $this->logger->error('Card number length is less than first digits length and last digits length');
            return null;
        }

        $max = pow(10, $haveToGuessDigits) - 1;

        $suggestedPansCount = count($this->suggestedPans);
        foreach ($this->suggestedPans as $i => $suggestedPan) {
            $this->logger->debug('Suggested pan ' . $i . ' / ' . $suggestedPansCount . ': ' . $suggestedPan);

            if ($this->hashComparator->compare($this->hash($suggestedPan), $hashMask)) {
                return $suggestedPan;
            }
        }

        for ($i = 0; $i <= $max; $i++) {
            $this->logger->debug($i . ' / ' . $max);

            $guess = str_pad($i, $haveToGuessDigits, '0', STR_PAD_LEFT);
            $cardNumber = $firstDigits . $guess . $lastDigits;

            if (! LuhnChecker::check($cardNumber)) {
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

    public function getHashComparator(): HashComparatorInterface
    {
        return $this->hashComparator;
    }

    public function setHashComparator(HashComparatorInterface $hashComparator): void
    {
        $this->hashComparator = $hashComparator;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
