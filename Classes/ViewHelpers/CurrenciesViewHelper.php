<?php

declare(strict_types=1);

namespace UniRate\Typo3\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use UniRate\Typo3\Service\UniRateService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the list of supported currency codes (empty array on failure).
 *
 * Usage:
 *   <f:for each="{unirate:currencies()}" as="code">{code}</f:for>
 */
final class CurrenciesViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @return string[]
     */
    public function render(): array
    {
        $service = GeneralUtility::makeInstance(UniRateService::class);

        return $service->getCurrencies();
    }
}
