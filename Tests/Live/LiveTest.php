<?php

declare(strict_types=1);

namespace UniRate\Typo3\Tests\Live;

use PHPUnit\Framework\TestCase;
use UniRate\Typo3\Service\UniRateClient;
use UniRate\Typo3\Service\UniRateException;

/**
 * Live tests — exercise only free-tier endpoints:
 *   /api/rates, /api/convert, /api/currencies, /api/vat/rates
 *
 * Historical + time-series + limits are Pro-gated and 403 on free keys; they are
 * deliberately not exposed by this extension, so there is nothing Pro-gated to skip.
 *
 * Run with:
 *   UNIRATE_API_KEY=... vendor/bin/phpunit --testsuite live
 */
final class LiveTest extends TestCase
{
    private UniRateClient $client;

    protected function setUp(): void
    {
        $apiKey = getenv('UNIRATE_API_KEY');
        if ($apiKey === false || $apiKey === '') {
            $this->markTestSkipped('UNIRATE_API_KEY not set; skipping live tests.');
        }
        $this->client = new UniRateClient($apiKey);
    }

    public function testLiveGetRate(): void
    {
        $rate = $this->client->getRate('USD', 'EUR');
        self::assertGreaterThan(0.0, $rate);
    }

    public function testLiveConvert(): void
    {
        $result = $this->client->convert(100.0, 'USD', 'EUR');
        self::assertGreaterThan(0.0, $result);
    }

    public function testLiveGetCurrencies(): void
    {
        $currencies = $this->client->getCurrencies();
        self::assertContains('USD', $currencies);
        self::assertContains('EUR', $currencies);
        self::assertGreaterThan(50, count($currencies));
    }

    public function testLiveGetVatRatesAllCountries(): void
    {
        $resp = $this->client->getVatRates();
        self::assertArrayHasKey('vat_rates', $resp);
        self::assertNotEmpty($resp['vat_rates']);
    }

    public function testLiveGetVatRateForCountry(): void
    {
        $resp = $this->client->getVatRates('DE');
        self::assertSame('DE', $resp['country']);
        self::assertArrayHasKey('vat_data', $resp);
        self::assertGreaterThan(0.0, (float) $resp['vat_data']['vat_rate']);
    }

    public function testLiveUnknownCurrencyThrows(): void
    {
        $this->expectException(UniRateException::class);
        $this->client->getRate('USD', 'ZZZ');
    }
}
