<?php

declare(strict_types=1);

use GlycoFlow\Config\Environment;
use GlycoFlow\Http\ExceptionHandler;

require_once __DIR__ . '/vendor/autoload.php';

// The .env file (when present) lives at the repository root, one level above backend/.
$rootPath = dirname(__DIR__);
$environment = Environment::load($rootPath);

// Resolve the application timezone, falling back to UTC for unknown values.
$timezone = $environment->getOptionalString('APP_TIMEZONE', 'UTC') ?? 'UTC';
if (!in_array($timezone, timezone_identifiers_list(), true)) {
    $timezone = 'UTC';
}
date_default_timezone_set($timezone);

// Register centralized error and exception handling for every endpoint.
(new ExceptionHandler())->register();

// Returned so endpoints can reuse the loaded configuration without re-reading it.
return $environment;
