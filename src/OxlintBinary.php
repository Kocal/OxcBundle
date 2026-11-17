<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
final class OxlintBinary
{
    public static function getName(string $appsVersion): string
    {
        $platform = Platform::guess();

        return sprintf('oxlint-%s%s', $platform->getAssetTarget($appsVersion), 'win32' === $platform->os ? '.exe' : '');
    }

    public static function getArchiveName(string $appsVersion): string
    {
        $platform = Platform::guess();

        return sprintf('oxlint-%s.%s', $platform->getAssetTarget($appsVersion), 'win32' === $platform->os ? 'zip' : 'tar.gz');
    }
}
