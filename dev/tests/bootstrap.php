<?php

// Override is_cli()
if (! function_exists('is_cli')) {
    function is_cli(): bool
    {
        return false;
    }
}

require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';
