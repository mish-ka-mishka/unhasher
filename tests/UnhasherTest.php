<?php

namespace Unhasher\Tests;

use PHPUnit\Framework\TestCase;
use Unhasher\LuhnChecker;
use Unhasher\Unhasher;

class UnhasherTest extends TestCase
{
    private function getFakeCardNumber(string $firstDigits): string
    {
        $cardNumber = $firstDigits . '1234567890';
        $cardNumber .= LuhnChecker::generateChecksum($cardNumber);

        return $cardNumber;
    }

    public function testUnhash()
    {
        $firstDigits = '2200';
        $cardNumber = $this->getFakeCardNumber($firstDigits);
        $lastDigits = substr($cardNumber, -4);

        $hashFunction = function ($string) {
            return sha1($string . 'salt');
        };

        $unhasher = new Unhasher($hashFunction, strlen($cardNumber));

        $unhashedCardNumber = $unhasher->unhash($hashFunction($cardNumber), $firstDigits, $lastDigits);

        $this->assertEquals($cardNumber, $unhashedCardNumber);
    }
}
