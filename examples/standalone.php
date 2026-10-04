<?php

declare(strict_types=1);

/*
 * Runnable, framework-free example of the dependency-free UniRate client that
 * powers this extension. Uses only the free-tier endpoints.
 *
 *   UNIRATE_API_KEY=your-key php examples/standalone.php
 */

require __DIR__ . '/../Classes/Service/UniRateException.php';
require __DIR__ . '/../Classes/Service/UniRateClient.php';

use UniRate\Typo3\Service\UniRateClient;
use UniRate\Typo3\Service\UniRateException;

$apiKey = getenv('UNIRATE_API_KEY') ?: '';
if ($apiKey === '') {
    fwrite(STDERR, "Set UNIRATE_API_KEY first.\n");
    exit(1);
}

$client = new UniRateClient($apiKey);

try {
    printf("USD -> EUR rate: %s\n", $client->getRate('USD', 'EUR'));
    printf("100 USD in JPY:  %s\n", $client->convert(100, 'USD', 'JPY'));

    $currencies = $client->getCurrencies();
    printf("Supported currencies: %d (e.g. %s)\n", count($currencies), implode(', ', array_slice($currencies, 0, 5)));

    $vat = $client->getVatRates('DE');
    printf("Germany VAT rate: %s%%\n", $vat['vat_data']['vat_rate'] ?? 'n/a');
} catch (UniRateException $e) {
    fwrite(STDERR, 'UniRate error (' . $e->getCode() . '): ' . $e->getMessage() . "\n");
    exit(1);
}
