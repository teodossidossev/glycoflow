<?php

declare(strict_types=1);

/**
 * Cross-platform PHP syntax checker.
 *
 * Recursively lints every PHP file under the backend source directories plus
 * bootstrap.php, excluding vendor/. Returns a non-zero exit code when any file
 * fails. Uses only portable PHP APIs so it runs on Windows, macOS, and Linux.
 */

$baseDir = dirname(__DIR__);
$directories = ['src', 'public', 'tests', 'tools'];
$extraFiles = ['bootstrap.php'];

$files = [];

foreach ($directories as $directory) {
    $path = $baseDir . DIRECTORY_SEPARATOR . $directory;
    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    /** @var SplFileInfo $fileInfo */
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'php') {
            $files[] = $fileInfo->getPathname();
        }
    }
}

foreach ($extraFiles as $extraFile) {
    $path = $baseDir . DIRECTORY_SEPARATOR . $extraFile;
    if (is_file($path)) {
        $files[] = $path;
    }
}

// Exclude anything under a vendor/ directory as a safety net.
$vendorFragment = DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
$files = array_values(array_filter(
    $files,
    static fn (string $file): bool => !str_contains($file, $vendorFragment)
));

sort($files);

$failures = [];

foreach ($files as $file) {
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file);
    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        $failures[$file] = implode(PHP_EOL, $output);
    }
}

$total = count($files);

if ($failures === []) {
    fwrite(STDOUT, sprintf('Syntax OK: %d file(s) checked.%s', $total, PHP_EOL));
    exit(0);
}

fwrite(STDERR, sprintf('Syntax errors in %d of %d file(s):%s', count($failures), $total, PHP_EOL));
foreach ($failures as $file => $message) {
    fwrite(STDERR, sprintf('- %s%s%s%s', $file, PHP_EOL, $message, PHP_EOL));
}

exit(1);
