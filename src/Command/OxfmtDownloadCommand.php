<?php

declare(strict_types=1);

namespace Kocal\OxcBundle\Command;

use Kocal\OxcBundle\OxfmtBinary;
use Kocal\OxcBundle\Platform;
use Kocal\OxcBundle\Release;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'oxc:download:oxfmt',
    description: 'Download the Oxfmt binary for the current platform and architecture.',
)]
final class OxfmtDownloadCommand extends AbstractDownloadBinaryCommand
{
    public function __construct(
        private readonly Filesystem $filesystem,
        ?HttpClientInterface $httpClient,
        private readonly ?string $oxfmtVersion,
        private readonly ?string $appsVersion,
    ) {
        parent::__construct($this->filesystem, $httpClient);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $release = Release::forOxfmt($this->oxfmtVersion, $this->appsVersion);
        $io = new SymfonyStyle($input, $output);

        $destinationDir = $input->getArgument('destination-dir');
        if (!is_string($destinationDir)) {
            throw new \InvalidArgumentException('The destination directory must be a string.');
        }

        if (!is_dir($destinationDir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $destinationDir));
        }

        $io->title('Downloading Oxfmt...');

        $binaryName = '\\' === \DIRECTORY_SEPARATOR ? 'oxfmt.exe' : 'oxfmt';
        $binaryDestinationPath = Path::join($destinationDir, $binaryName);

        if ($this->filesystem->exists($binaryDestinationPath)) {
            $installedVersion = null === $release->version ? null : $this->findInstalledVersion($binaryDestinationPath);

            if (null !== $installedVersion && $installedVersion === $release->version) {
                $io->success(sprintf('Oxfmt %s is already installed.', $release->version));

                return self::SUCCESS;
            }

            $io->warning(null === $installedVersion
                ? 'Oxfmt is already installed, replacing it.'
                : sprintf('Oxfmt %s is already installed, but requested version is %s. Replacing it.', $installedVersion, $release->version));
            $this->filesystem->remove($binaryDestinationPath);
        }

        $platform = Platform::guess();
        $archiveName = OxfmtBinary::getArchiveName($release, $platform);
        $this->downloadArchiveAndExtractTo(
            $io,
            $release->getAssetUrl($archiveName),
            $archiveName,
            OxfmtBinary::getName($release, $platform),
            $binaryDestinationPath
        );

        $io->success(sprintf('Done, you can now run Oxfmt with "%s".', Path::makeRelative($binaryDestinationPath, (string) getcwd())));

        return self::SUCCESS;
    }
}
