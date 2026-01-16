<?php
declare(strict_types=1);

final class Env
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;

            [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
            $_ENV[trim($k)] = trim($v, "\"' ");
        }
    }
}
