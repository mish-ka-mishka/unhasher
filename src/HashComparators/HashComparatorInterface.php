<?php

namespace Unhasher\HashComparators;

interface HashComparatorInterface
{
    public function compare(string $hash, string $mask): bool;
}
