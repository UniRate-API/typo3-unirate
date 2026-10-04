<?php

declare(strict_types=1);

/*
 * Test bootstrap.
 *
 * In a full composer install (e.g. CI) vendor/autoload.php provides the PSR-4
 * map. The fallback autoloader below lets the dependency-free client tests run
 * in a lean, hermetic environment WITHOUT a composer install of the TYPO3
 * framework — only the plain-PHP Service classes are ever loaded by it.
 */

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require $vendorAutoload;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'UniRate\\Typo3\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = __DIR__ . '/../Classes/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});
