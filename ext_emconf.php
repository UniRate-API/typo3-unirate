<?php

$EM_CONF['unirate_currency'] = [
    'title' => 'UniRate Currency',
    'description' => 'Live currency exchange rates, conversion, currency lists, and VAT rates for TYPO3 via the UniRate API. Zero third-party runtime dependencies: Fluid ViewHelpers plus an injectable service, with responses cached through the TYPO3 Caching Framework.',
    'category' => 'services',
    'author' => 'Unirate Team',
    'author_email' => '',
    'author_company' => 'UniRate',
    'state' => 'stable',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'php' => '8.2.0-8.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
