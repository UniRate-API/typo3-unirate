<?php

declare(strict_types=1);

namespace UniRate\Typo3\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use UniRate\Typo3\Service\UniRateClient;
use UniRate\Typo3\Service\UniRateException;

final class UniRateClientTest extends TestCase
{
    /**
     * Build a client whose transport returns a canned [status, body], and capture
     * the URL it was called with.
     *
     * @param array{0:int,1:string} $response
     */
    private function clientReturning(array $response, ?string &$captured = null): UniRateClient
    {
        $transport = function (string $url) use ($response, &$captured): array {
            $captured = $url;

            return $response;
        };

        return new UniRateClient('test-key', 'https://api.unirateapi.com', 15, $transport);
    }

    public function testGetRate(): void
    {
        $client = $this->clientReturning([200, '{"rate": "0.85"}'], $url);
        self::assertSame(0.85, $client->getRate('usd', 'eur'));
        self::assertStringContainsString('/api/rates?', $url);
        self::assertStringContainsString('from=USD', $url);
        self::assertStringContainsString('to=EUR', $url);
        self::assertStringContainsString('api_key=test-key', $url);
    }

    public function testGetRateParsesStringValues(): void
    {
        // The API returns rate values as JSON strings; the client must coerce to float.
        $client = $this->clientReturning([200, '{"rate": "1.23456"}']);
        self::assertSame(1.23456, $client->getRate('USD', 'JPY'));
    }

    public function testConvert(): void
    {
        $client = $this->clientReturning([200, '{"result": "85.0"}'], $url);
        self::assertSame(85.0, $client->convert(100, 'USD', 'EUR'));
        self::assertStringContainsString('/api/convert?', $url);
        self::assertStringContainsString('amount=100', $url);
        self::assertStringContainsString('from=USD', $url);
        self::assertStringContainsString('to=EUR', $url);
    }

    public function testConvertUppercasesInputs(): void
    {
        $client = $this->clientReturning([200, '{"result": "10"}'], $url);
        $client->convert(1, 'gbp', 'chf');
        self::assertStringContainsString('from=GBP', $url);
        self::assertStringContainsString('to=CHF', $url);
    }

    public function testGetCurrencies(): void
    {
        $client = $this->clientReturning([200, '{"currencies": ["USD","EUR","GBP"]}'], $url);
        self::assertSame(['USD', 'EUR', 'GBP'], $client->getCurrencies());
        self::assertStringContainsString('/api/currencies?', $url);
    }

    public function testGetVatRatesForCountry(): void
    {
        $client = $this->clientReturning(
            [200, '{"country":"DE","vat_data":{"country_code":"DE","vat_rate":19.0}}'],
            $url
        );
        $result = $client->getVatRates('de');
        self::assertSame('DE', $result['country']);
        self::assertStringContainsString('country=DE', $url);
    }

    public function testGetVatRatesAllOmitsCountryParam(): void
    {
        $client = $this->clientReturning([200, '{"vat_rates":{}}'], $url);
        $client->getVatRates();
        self::assertStringNotContainsString('country=', $url);
    }

    public function testAcceptHeaderAndUserAgentDefaultPathUnused(): void
    {
        // The injected transport bypasses cURL; this guards the public method surface
        // stays callable with the documented argument order.
        $client = $this->clientReturning([200, '{"rate":"1.0"}']);
        self::assertIsFloat($client->getRate('USD', 'USD'));
    }

    public function testMissingApiKeyThrows(): void
    {
        $client = new UniRateClient('', 'https://api.unirateapi.com', 15, static function (): array {
            return [200, '{}'];
        });
        $this->expectException(UniRateException::class);
        $client->getRate('USD', 'EUR');
    }

    public function testUnauthorizedThrows(): void
    {
        $client = $this->clientReturning([401, 'Unauthorized']);
        $this->expectException(UniRateException::class);
        $this->expectExceptionCode(401);
        $client->getRate('USD', 'EUR');
    }

    public function testProGatedEndpointThrows(): void
    {
        // Free-tier key hitting a Pro-gated path returns 403 — surface it clearly.
        $client = $this->clientReturning([403, 'Forbidden']);
        $this->expectException(UniRateException::class);
        $this->expectExceptionCode(403);
        $client->getRate('USD', 'EUR');
    }

    public function testNotFoundThrows(): void
    {
        $client = $this->clientReturning([404, 'Not found']);
        $this->expectException(UniRateException::class);
        $this->expectExceptionCode(404);
        $client->getRate('USD', 'ZZZ');
    }

    public function testRateLimitThrows(): void
    {
        $client = $this->clientReturning([429, 'Too many requests']);
        $this->expectException(UniRateException::class);
        $this->expectExceptionCode(429);
        $client->getRate('USD', 'EUR');
    }

    public function testGenericServerErrorThrows(): void
    {
        $client = $this->clientReturning([503, 'Service unavailable']);
        $this->expectException(UniRateException::class);
        $this->expectExceptionCode(503);
        $client->getRate('USD', 'EUR');
    }

    public function testInvalidJsonThrows(): void
    {
        $client = $this->clientReturning([200, '<html>not json</html>']);
        $this->expectException(UniRateException::class);
        $client->getRate('USD', 'EUR');
    }
}
