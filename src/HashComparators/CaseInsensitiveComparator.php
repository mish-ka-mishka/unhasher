<?php

namespace Unhasher\HashComparators;

class CaseInsensitiveComparator implements HashComparatorInterface
{
    public function compare(string $hash, string $mask): bool
    {
        return strtolower($hash) === strtolower($mask);
    }
}
