<?php

class Env
{
    public static function load(string $path): void
    {
        if (!is_readable($path)) {
            throw new RuntimeException("A .env fájl nem olvasható: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));

            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return $value !== false && $value !== null ? $value : $default;
    }
}