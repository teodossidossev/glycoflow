<?php

declare(strict_types=1);

use GlycoFlow\Http\JsonResponse;

require __DIR__ . '/../../bootstrap.php';

// Only GET is supported. Unexpected failures are handled by the bootstrap's
// centralized exception handler.
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method !== 'GET') {
    JsonResponse::error(
        'METHOD_NOT_ALLOWED',
        'Only the GET method is supported for this endpoint.',
        405,
        null,
        null,
        ['Allow' => 'GET'],
    )->emit();

    return;
}

// Static liveness payload only — no environment, version, or configuration data.
JsonResponse::success([
    'status' => 'ok',
    'service' => 'glycoflow-api',
])->emit();
