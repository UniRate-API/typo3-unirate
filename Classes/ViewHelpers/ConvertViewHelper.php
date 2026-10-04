<?php

declare(strict_types=1);

namespace UniRate\Typo3\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use UniRate\Typo3\Service\UniRateService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Converts an amount between two currencies at the current rate, or null on failure.
 *
 * Usage:
 *   {unirate:convert(amount: 100, from: 'USD', to: 'EUR')}
 *   <unirate:convert amount="100" from="USD" to="EUR" />
 */
final class ConvertViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('amount', 'float', 'Amount to convert', false, 1.0);
        $this->registerArgument('from', 'string', 'Source currency code', false, 'USD');
        $this->registerArgument('to', 'string', 'Target currency code', true);
    }

    public function render(): ?float
    {
        $service = GeneralUtility::makeInstance(UniRateService::class);

        return $service->convert(
            (float) $this->arguments['amount'],
            (string) $this->arguments['from'],
            (string) $this->arguments['to']
        );
    }
}
