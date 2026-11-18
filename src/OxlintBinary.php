<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
final class OxlintBinary
{
    public static function getName(Release $release, Platform $platform): string
    {
        return sprintf('oxlint-%s%s', $release->getAssetTarget($platform), 'win32' === $platform->os ? '.exe' : '');
    }

    public static function getArchiveName(Release $release, Platform $platform): string
    {
        return sprintf('oxlint-%s.%s', $release->getAssetTarget($platform), 'win32' === $platform->os ? 'zip' : 'tar.gz');
    }
}
