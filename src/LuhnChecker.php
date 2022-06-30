<?php

namespace Unhasher;

use InvalidArgumentException;

class LuhnChecker
{
    public static function check(string $numeric): bool
    {
        $extractedChecksum = substr($numeric, -1);
        $extractedNumber = substr($numeric, 0, -1);

        return self::generateChecksum($extractedNumber) === $extractedChecksum;
    }

    public static function generateChecksum(string $numeric): string
    {
        if (!is_numeric($numeric)) {
            throw new InvalidArgumentException('$numeric is not numeric');
        }

        $length = strlen($numeric);
        $parity = $length % 2;

        $sum = 0;

        for ($i = $length - 1; $i >= 0; --$i) {
            $char = $numeric[$i];

            if ($i % 2 != $parity) {
                $char *= 2;
                if ($char > 9) {
                    $char -= 9;
                }
            }

            $sum += $char;
        }

        return ($sum * 9) % 10;
    }
}
