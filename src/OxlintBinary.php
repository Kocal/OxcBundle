<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
final class OxlintBinary
{
    public static function getName(): string
    {
        $platform = Platform::guess();

        return match ($platform->os) {
            'darwin' => sprintf('oxlint-darwin-%s', $platform->machine),
            'linux' => sprintf('oxlint-linux-%s-%s', $platform->machine, $platform->isMusl ? 'musl' : 'gnu'),
            'win32' => sprintf('oxlint-win32-%s.exe', $platform->machine),
            default => throw new \RuntimeException(sprintf('Unsupported platform (%s).', $platform)),
        };
    }

    public static function getArchiveName(): string
    {
        $platform = Platform::guess();

        return match ($platform->os) {
            'darwin' => sprintf('oxlint-darwin-%s.tar.gz', $platform->machine),
            'linux' => sprintf('oxlint-linux-%s-%s.tar.gz', $platform->machine, $platform->isMusl ? 'musl' : 'gnu'),
            'win32' => sprintf('oxlint-win32-%s.zip', $platform->machine),
            default => throw new \RuntimeException(sprintf('Unsupported platform (%s).', $platform)),
        };
    }
}
