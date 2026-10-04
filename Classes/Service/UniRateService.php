<?php

declare(strict_types=1);

namespace UniRate\Typo3\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * TYPO3-wired facade over {@see UniRateClient}.
 *
 * Reads the extension configuration (API key / base URL / timeout / cache
 * lifetime), caches successful responses through the TYPO3 Caching Framework,
 * and degrades gracefully: on any API error it logs a warning and returns a
 * null/empty default so a Fluid template or controller never throws at a site
 * visitor.
 *
 * Inject it anywhere via DI, or fetch it with
 * GeneralUtility::makeInstance(UniRateService::class).
 */
final class UniRateService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private UniRateClient $client;
    private FrontendInterface $cache;
    private int $cacheLifetime;

    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        CacheManager $cacheManager
    ) {
        try {
            $config = $extensionConfiguration->get('unirate_currency');
        } catch (\Throwable) {
            $config = [];
        }

        $apiKey = (string) ($config['apiKey'] ?? '');
        $baseUrl = (string) ($config['baseUrl'] ?? '');
        if ($baseUrl === '') {
            $baseUrl = 'https://api.unirateapi.com';
        }
        $timeout = (int) ($config['timeout'] ?? 0);
        if ($timeout <= 0) {
            $timeout = 15;
        }
        $this->cacheLifetime = (int) ($config['cacheLifetime'] ?? 0);
        if ($this->cacheLifetime <= 0) {
            $this->cacheLifetime = 3600;
        }

        $this->client = new UniRateClient($apiKey, $baseUrl, $timeout);
        $this->cache = $cacheManager->getCache('unirate');
    }

    /**
     * Replace the underlying client (used for integration overrides and tests).
     */
    public function setClient(UniRateClient $client): void
    {
        $this->client = $client;
    }

    /**
     * Current exchange rate, or null if the request failed.
     */
    public function getRate(string $from, string $to): ?float
    {
        $value = $this->remember(
            'rate_' . $this->safe($from) . '_' . $this->safe($to),
            fn (): float => $this->client->getRate($from, $to)
        );

        return $value === null ? null : (float) $value;
    }

    /**
     * Converted amount, or null if the request failed.
     */
    public function convert(float $amount, string $from, string $to): ?float
    {
        $value = $this->remember(
            'convert_' . $this->safe((string) $amount) . '_' . $this->safe($from) . '_' . $this->safe($to),
            fn (): float => $this->client->convert($amount, $from, $to)
        );

        return $value === null ? null : (float) $value;
    }

    /**
     * Supported currency codes, or an empty array if the request failed.
     *
     * @return string[]
     */
    public function getCurrencies(): array
    {
        $value = $this->remember('currencies', fn (): array => $this->client->getCurrencies());

        return is_array($value) ? $value : [];
    }

    /**
     * VAT rates for all countries, or a single country when a code is given.
     * Returns an empty array if the request failed.
     *
     * @return array<string,mixed>
     */
    public function getVatRates(?string $country = null): array
    {
        $key = 'vat_' . ($country === null || $country === '' ? 'all' : $this->safe($country));
        $value = $this->remember($key, fn (): array => $this->client->getVatRates($country));

        return is_array($value) ? $value : [];
    }

    /**
     * Fetch from cache, or run the producer and cache its result. Returns null
     * (uncached) when the producer throws a {@see UniRateException}.
     *
     * @param callable():mixed $producer
     */
    private function remember(string $id, callable $producer): mixed
    {
        $cached = $this->cache->get($id);
        if ($cached !== false) {
            return $cached;
        }

        try {
            $value = $producer();
        } catch (UniRateException $e) {
            $this->logger?->warning(
                'UniRate request failed: ' . $e->getMessage(),
                ['status' => $e->getCode()]
            );

            return null;
        }

        $this->cache->set($id, $value, [], $this->cacheLifetime);

        return $value;
    }

    /**
     * Normalise a value into a safe cache-identifier fragment.
     */
    private function safe(string $value): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '_', strtoupper($value)) ?? '';
    }
}
