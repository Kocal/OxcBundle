<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

/**
 * @internal
 */
final class Release
{
    private function __construct(
        public readonly string $tag,
        public readonly bool $hasLegacyAssetNames,
        public readonly ?string $version,
    ) {
    }

    public static function forOxlint(?string $oxlintVersion, ?string $appsVersion): self
    {
        if (null !== $oxlintVersion) {
            $oxlintVersion = ltrim($oxlintVersion, 'v');

            if (version_compare($oxlintVersion, '1.85.0', '>=')) {
                return new self(sprintf('oxlint_v%s', $oxlintVersion), false, $oxlintVersion);
            }

            if (version_compare($oxlintVersion, '1.28.0', '<')) {
                throw new \InvalidArgumentException('Oxlint versions older than 1.28.0 are not supported.');
            }

            return self::fromAppsVersion($oxlintVersion, $oxlintVersion);
        }

        if (null !== $appsVersion) {
            $appsVersion = ltrim($appsVersion, 'v');

            return self::fromAppsVersion($appsVersion, $appsVersion);
        }

        throw new \LogicException('Configure "kocal_oxc.oxlint_version" to download Oxlint.');
    }

    public static function forOxfmt(?string $oxfmtVersion, ?string $appsVersion): self
    {
        if (null !== $oxfmtVersion) {
            $oxfmtVersion = ltrim($oxfmtVersion, 'v');

            if (version_compare($oxfmtVersion, '0.70.0', '<')) {
                throw new \InvalidArgumentException('Oxfmt versions older than 0.70.0 are only published under "apps_v*" tags, use "kocal_oxc.apps_version" instead.');
            }

            return new self(sprintf('oxfmt_v%s', $oxfmtVersion), false, $oxfmtVersion);
        }

        if (null !== $appsVersion) {
            return self::fromAppsVersion(ltrim($appsVersion, 'v'), null);
        }

        throw new \LogicException('Configure "kocal_oxc.oxfmt_version" to download Oxfmt.');
    }

    public function getAssetUrl(string $assetName): string
    {
        return sprintf('https://github.com/oxc-project/oxc/releases/download/%s/%s', $this->tag, $assetName);
    }

    public function getAssetTarget(Platform $platform): string
    {
        return $this->hasLegacyAssetNames ? $platform->getLegacyAssetTarget() : $platform->getAssetTarget();
    }

    private static function fromAppsVersion(string $appsVersion, ?string $binaryVersion): self
    {
        return new self(
            sprintf('apps_v%s', $appsVersion),
            // Release assets are named after Rust target triples since apps_v1.46.0
            version_compare($appsVersion, '1.46.0', '<'),
            $binaryVersion,
        );
    }
}
