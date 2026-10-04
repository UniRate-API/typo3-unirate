<?php

declare(strict_types=1);

namespace UniRate\Typo3\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use UniRate\Typo3\Service\UniRateService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the current exchange rate for a currency pair, or null on failure.
 *
 * Usage:
 *   {unirate:rate(from: 'USD', to: 'EUR')}
 *   <unirate:rate from="USD" to="EUR" />
 */
final class RateViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('from', 'string', 'Source currency code', false, 'USD');
        $this->registerArgument('to', 'string', 'Target currency code', true);
    }

    public function render(): ?float
    {
        $service = GeneralUtility::makeInstance(UniRateService::class);

        return $service->getRate((string) $this->arguments['from'], (string) $this->arguments['to']);
    }
}
