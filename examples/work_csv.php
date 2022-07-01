<?php

use Unhasher\HashComparators\MaskedComparator;
use Unhasher\EchoLogger;
use Unhasher\Unhasher;

require '../vendor/autoload.php';

$logger = new EchoLogger();

$unhasher = new Unhasher(
    function ($string) {
        return hash_hmac('sha1', $string, 'key12345');
    },
    16,
    (new MaskedComparator()),
    $logger
);


$input = new SplFileObject('input.csv');
$output = new SplFileObject('output.csv', 'a');

$hashIndex = 0;
$lastDigitsIndex = 1;


$header = $input->fgetcsv();
array_splice($header, $hashIndex, 0, 'PAN');

$output->fputcsv($header);

while (!$input->eof()) {
    $row = $input->fgetcsv();

    $hash = $row[$hashIndex];
    $lastDigits = $row[$lastDigitsIndex];

    $cardNumber = $unhasher->unhash($hash, '220220', $lastDigits);

    array_splice($row, $hashIndex, 0, (string)$cardNumber);

    $output->flock(LOCK_SH);
    $output->fputcsv($row);
    $output->flock(LOCK_UN);

    $logger->info($hash . ' -> ' . $cardNumber);
}
