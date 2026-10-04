# UniRate Currency for TYPO3

Live currency **exchange rates**, **conversion**, **currency lists**, and **VAT
rates** in TYPO3, backed by the [UniRate API](https://unirateapi.com).

- **Zero third-party runtime dependencies** — HTTP is done with PHP's bundled
  `ext-curl` + `ext-json`. Nothing is added to your project's dependency tree.
- **Fluid ViewHelpers** for templates, plus an **injectable service** for PHP.
- **Cached** through the TYPO3 Caching Framework; API errors are logged and
  degrade to `null`/`[]` so a template never throws at a visitor.
- Only **free-tier** endpoints are exposed (rates / convert / currencies / VAT)
  — the Pro-gated historical/timeseries endpoints are intentionally omitted, so
  the extension never surfaces a `403` to a site visitor.

Compatible with **TYPO3 12.4 LTS and 13.4 LTS**, PHP 8.2–8.4.

## Installation

### Composer (recommended)

```bash
composer require unirate/typo3-unirate
```

### TYPO3 Extension Manager / TER

Install the extension key **`unirate`** from the Extensions module, or download
it from the [TYPO3 Extension Repository](https://extensions.typo3.org/).

Then set your API key under **Admin Tools → Settings → Extension Configuration →
`unirate`** (get a free key at <https://unirateapi.com>).

## Configuration

| Setting         | Default                       | Description                          |
|-----------------|-------------------------------|--------------------------------------|
| `apiKey`        | *(empty)*                     | Your UniRate API key.                |
| `baseUrl`       | `https://api.unirateapi.com`  | API base URL.                        |
| `timeout`       | `15`                          | HTTP request timeout (seconds).      |
| `cacheLifetime` | `3600`                        | Response cache lifetime (seconds).   |

## Usage in Fluid

The `unirate` ViewHelper namespace is registered globally — no import needed.

```html
<p>1 USD = {unirate:rate(from: 'USD', to: 'EUR')} EUR</p>

<p>{unirate:convert(amount: 100, from: 'USD', to: 'JPY')} JPY</p>

<p>Germany VAT: {unirate:vatRate(country: 'DE')}%</p>

<ul>
  <f:for each="{unirate:currencies()}" as="code">
    <li>{code}</li>
  </f:for>
</ul>
```

## Usage in PHP

Inject `UniRate\Typo3\Service\UniRateService` via constructor DI, or fetch it:

```php
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UniRate\Typo3\Service\UniRateService;

$unirate = GeneralUtility::makeInstance(UniRateService::class);

$rate       = $unirate->getRate('USD', 'EUR');        // ?float
$amount     = $unirate->convert(100.0, 'USD', 'JPY'); // ?float
$currencies = $unirate->getCurrencies();              // string[]
$vat        = $unirate->getVatRates('DE');            // array<string,mixed>
```

Every method returns a safe default (`null` / `[]`) and logs a warning if the
API call fails, so your rendering never breaks.

### Low-level client

For scripts outside the TYPO3 request lifecycle, the dependency-free
`UniRate\Typo3\Service\UniRateClient` can be used directly (it throws
`UniRateException` on failure). See [`examples/standalone.php`](examples/standalone.php).

## Error handling

The low-level `UniRateClient` maps HTTP status codes to a `UniRateException`
whose code carries the HTTP status:

| Status | Meaning                                   |
|--------|-------------------------------------------|
| 401    | Missing or invalid API key                |
| 403    | Endpoint requires a Pro subscription      |
| 404    | Currency not found / no data available    |
| 429    | Rate limit exceeded                       |
| other  | Generic API/transport error               |

`UniRateService` catches these, logs a warning, and returns a safe default.

## Development

```bash
# Hermetic mock tests (no framework install required)
composer --working-dir=/tmp/pu require phpunit/phpunit:^11.5
/tmp/pu/vendor/bin/phpunit --testsuite unit

# Free-tier live tests
UNIRATE_API_KEY=your-key /tmp/pu/vendor/bin/phpunit --testsuite live
```

## Related UniRate clients

Official UniRate libraries exist for Python, Node/TypeScript, PHP, Swift, Java,
Go, Rust, Ruby, .NET, and more — see the
[UniRate-API organization](https://github.com/UniRate-API).

## License

MIT © 2026 Unirate Team. See [LICENSE](LICENSE).
