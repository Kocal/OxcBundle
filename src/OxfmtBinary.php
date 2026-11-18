<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
final class OxfmtBinary
{
    public static function getName(Release $release, Platform $platform): string
    {
        return sprintf('oxfmt-%s%s', $release->getAssetTarget($platform), 'win32' === $platform->os ? '.exe' : '');
    }

    public static function getArchiveName(Release $release, Platform $platform): string
    {
        return sprintf('oxfmt-%s.%s', $release->getAssetTarget($platform), 'win32' === $platform->os ? 'zip' : 'tar.gz');
    }
}
