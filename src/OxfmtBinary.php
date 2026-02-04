<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
final class OxfmtBinary
{
    public static function getName(): string
    {
        $platform = Platform::guess();

        return match ($platform->os) {
            'darwin' => sprintf('oxfmt-darwin-%s', $platform->machine),
            'linux' => sprintf('oxfmt-linux-%s-%s', $platform->machine, $platform->isMusl ? 'musl' : 'gnu'),
            'win32' => sprintf('oxfmt-win32-%s.exe', $platform->machine),
            default => throw new \RuntimeException(sprintf('Unsupported platform (%s).', $platform)),
        };
    }

    public static function getArchiveName(): string
    {
        $platform = Platform::guess();

        return match ($platform->os) {
            'darwin' => sprintf('oxfmt-darwin-%s.tar.gz', $platform->machine),
            'linux' => sprintf('oxfmt-linux-%s-%s.tar.gz', $platform->machine, $platform->isMusl ? 'musl' : 'gnu'),
            'win32' => sprintf('oxfmt-win32-%s.zip', $platform->machine),
            default => throw new \RuntimeException(sprintf('Unsupported platform (%s).', $platform)),
        };
    }
}
