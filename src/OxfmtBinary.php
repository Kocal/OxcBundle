<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
final class OxfmtBinary
{
    public static function getName(string $appsVersion): string
    {
        $platform = Platform::guess();

        return sprintf('oxfmt-%s%s', $platform->getAssetTarget($appsVersion), 'win32' === $platform->os ? '.exe' : '');
    }

    public static function getArchiveName(string $appsVersion): string
    {
        $platform = Platform::guess();

        return sprintf('oxfmt-%s.%s', $platform->getAssetTarget($appsVersion), 'win32' === $platform->os ? 'zip' : 'tar.gz');
    }
}
