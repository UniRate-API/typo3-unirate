# Changelog

All notable changes to this extension are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/) and the project adheres to
[Semantic Versioning](https://semver.org/).

## [0.1.0] - 2026-10-04

### Added
- Initial release.
- `UniRate\Typo3\Service\UniRateService` — injectable, cached facade over the
  free-tier UniRate API (exchange rate, conversion, supported currencies, VAT
  rates). API errors are logged and degrade to `null`/`[]` so templates never
  throw at a visitor.
- Fluid ViewHelpers: `<unirate:rate>`, `<unirate:convert>`,
  `<unirate:currencies>`, `<unirate:vatRate>`.
- Extension configuration for API key, base URL, request timeout, and cache
  lifetime.
- Dependency-free HTTP client (`ext-curl` + `ext-json` only); responses cached
  through the TYPO3 Caching Framework.
- Mock unit test suite and free-tier live test suite.
