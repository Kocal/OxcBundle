<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
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
            str_contains($machine, 'arm64') || str_contains($machine, 'aarch64') => 'aarch64',
            str_contains($machine, 'x86_64') || str_contains($machine, 'amd64') => 'x86_64',
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
     * @param 'aarch64'|'x86_64'       $machine
     */
    public function __construct(
        public readonly string $os,
        public readonly string $machine,
        public readonly bool $isMusl = false,
    ) {
    }

    public function getLegacyAssetTarget(): string
    {
        $machine = 'aarch64' === $this->machine ? 'arm64' : 'x64';

        return match ($this->os) {
            'darwin' => sprintf('darwin-%s', $machine),
            'linux' => sprintf('linux-%s-%s', $machine, $this->isMusl ? 'musl' : 'gnu'),
            'win32' => sprintf('win32-%s', $machine),
        };
    }

    public function getAssetTarget(): string
    {
        return match ($this->os) {
            'darwin' => sprintf('%s-apple-darwin', $this->machine),
            'linux' => sprintf('%s-unknown-linux-%s', $this->machine, $this->isMusl ? 'musl' : 'gnu'),
            'win32' => sprintf('%s-pc-windows-msvc', $this->machine),
        };
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
