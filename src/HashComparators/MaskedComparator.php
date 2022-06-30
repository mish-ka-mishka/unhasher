<?php

namespace Unhasher\HashComparators;

class MaskedComparator implements HashComparatorInterface
{
    public function compare(string $hash, string $mask): bool
    {
        $mask = strtolower($mask);

        if ($hash === $mask) {
            return true;
        }

        $exploded = array_values(array_filter(explode('*', $mask)));

        if (count($exploded) === 2) {
            $start = $exploded[0];
            $end = $exploded[1];

            return substr($hash, 0, strlen($start)) === $start
                && substr($hash, -strlen($end)) === $end;
        }

        return false;
    }
}
