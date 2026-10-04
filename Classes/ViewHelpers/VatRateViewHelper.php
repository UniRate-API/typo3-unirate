<?php

declare(strict_types=1);

namespace UniRate\Typo3\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use UniRate\Typo3\Service\UniRateService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the VAT rate (as a float percentage) for a single country, or null on failure.
 *
 * Usage:
 *   {unirate:vatRate(country: 'DE')}
 *   <unirate:vatRate country="DE" />
 */
final class VatRateViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('country', 'string', 'ISO-3166 alpha-2 country code', true);
    }

    public function render(): ?float
    {
        $service = GeneralUtility::makeInstance(UniRateService::class);
        $response = $service->getVatRates((string) $this->arguments['country']);

        if (isset($response['vat_data']['vat_rate'])) {
            return (float) $response['vat_data']['vat_rate'];
        }

        return null;
    }
}
