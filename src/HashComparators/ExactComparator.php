<?php

namespace Unhasher\HashComparators;

class ExactComparator implements HashComparatorInterface
{
    public function compare(string $hash, string $mask): bool
    {
        return $hash === $mask;
    }
}
