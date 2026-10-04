<?php

declare(strict_types=1);

defined('TYPO3') or die();

// Dedicated cache for UniRate API responses (keeps live calls off the hot path).
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['unirate'] ??= [
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
    'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
    'options' => ['defaultLifetime' => 3600],
    'groups' => ['pages'],
];

// Global Fluid namespace so templates can use <unirate:...> without a per-template import.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['unirate'][] = 'UniRate\\Typo3\\ViewHelpers';
