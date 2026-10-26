<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
echo ini_get('display_errors') === '0' ? 'disabled' : 'enabled';
