<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

final class Platform
{
    /**
     * @throws \RuntimeException when the OS or machine type is unsupported
     */
    public static function guess(): self
    {
        $os = strtolower(\PHP_OS);
        $os = match (true) {
            str_contains($os, 'darwin') => 'darwin',
            str_contains($os, 'linux') => 'linux',
            str_contains($os, 'win') => 'win32',
            default => throw new \RuntimeException(sprintf('Unsupported platform (OS: %s).', $os)),
        };

        $machine = strtolower(php_uname('m'));
        $machine = match (true) {
            str_contains($machine, 'arm64') || str_contains($machine, 'aarch64') => 'arm64',
            str_contains($machine, 'x86_64') || str_contains($machine, 'amd64') => 'x64',
            default => throw new \RuntimeException(sprintf('Unsupported platform (machine: %s).', $machine)),
        };

        return new self(
            os: $os,
            machine: $machine,
            isMusl: self::guessMusl()
        );
    }

    /**
     * @param 'darwin'|'linux'|'win32' $os
     * @param 'arm64'|'x64'            $machine
     */
    public function __construct(
        public readonly string $os,
        public readonly string $machine,
        public readonly bool $isMusl = false,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s-%s', $this->os, $this->machine);
    }

    /**
     * Whether the current PHP environment is using musl libc.
     * This is used to determine the correct Oxfmt binary to download.
     */
    private static function guessMusl(): bool
    {
        if (!\function_exists('phpinfo')) {
            return false;
        }

        ob_start();
        phpinfo(\INFO_GENERAL);

        return 1 === preg_match('/--build=.*?-linux-musl/', ob_get_clean() ?: '');
    }
}
