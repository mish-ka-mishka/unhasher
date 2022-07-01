<?php

use Unhasher\EchoLogger;
use Unhasher\HashComparators\MaskedComparator;
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


file_put_contents('output.csv', '');


$input = new SplFileObject('input.csv');
$output = new SplFileObject('output.csv', 'a');


$hashIndex = 0;
$lastDigitsIndex = 1;
$forksNumber = 10;


$rowsNumber = 0;
while (! $input->eof()) {
    $rowsNumber++;
    $input->fgets();
}
$input->rewind();
$rowsNumber -= 1; // header


$header = $input->fgetcsv();
array_splice($header, $hashIndex, 0, 'PAN');

$output->fputcsv($header);


$pid = 0;
$min = 0;
$max = $rowsNumber - 1;
$chunkSize = $forksNumber === 0 ? $max : ceil($rowsNumber / $forksNumber);
$pids = [];


for ($i = 0; $i < $forksNumber; $i++) {
    $pid = pcntl_fork();

    if ($pid === -1) {
        throw new RuntimeException('Could not fork');
    }

    if ($pid === 0) {
        // child

        $min = $chunkSize * $i;
        $max = min($chunkSize * ($i + 1), $rowsNumber) - 1;

        if ($min > $max) {
            $logger->debug('Child terminated with empty chunk');

            exit(0);
        }

        break;
    } else {
        // parent

        $pids[] = $pid;
        $logger->debug('forked. pid: ' . $pid);
    }
}


if ($pid === 0) {
    // child

    $logger->debug('child (' . $min . ' ... ' . $max . ')');

    $input->seek($min);
    $i = $min;

    while (! $input->eof() && $i <= $max) {
        $i++;

        $row = $input->fgetcsv();

        if (empty($row[$hashIndex])) {
            continue;
        }

        $hash = $row[$hashIndex];
        $lastDigits = $row[$lastDigitsIndex];

        $cardNumber = $unhasher->unhash($hash, '220220', $lastDigits);

        array_splice($row, $hashIndex, 0, (string)$cardNumber);

        $output->flock(LOCK_SH);
        $output->fputcsv($row);
        $output->flock(LOCK_UN);

        $logger->info($hash . ' -> ' . $cardNumber);
    }
} else {
    // parent

    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
    }
}
