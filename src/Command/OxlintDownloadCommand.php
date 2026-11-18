<?php

declare(strict_types=1);

namespace Kocal\OxcBundle\Command;

use Kocal\OxcBundle\OxlintBinary;
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
    name: 'oxc:download:oxlint',
    description: 'Download the Oxlint binary for the current platform and architecture.',
)]
final class OxlintDownloadCommand extends AbstractDownloadBinaryCommand
{
    public function __construct(
        private readonly Filesystem $filesystem,
        ?HttpClientInterface $httpClient,
        private readonly ?string $oxlintVersion,
        private readonly ?string $appsVersion,
    ) {
        parent::__construct($this->filesystem, $httpClient);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $release = Release::forOxlint($this->oxlintVersion, $this->appsVersion);
        $io = new SymfonyStyle($input, $output);

        $destinationDir = $input->getArgument('destination-dir');
        if (!is_string($destinationDir)) {
            throw new \InvalidArgumentException('The destination directory must be a string.');
        }

        if (!is_dir($destinationDir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $destinationDir));
        }

        $io->title('Downloading Oxlint...');

        $binaryName = '\\' === \DIRECTORY_SEPARATOR ? 'oxlint.exe' : 'oxlint';
        $binaryDestinationPath = Path::join($destinationDir, $binaryName);

        if ($this->filesystem->exists($binaryDestinationPath)) {
            $installedVersion = $this->findInstalledVersion($binaryDestinationPath);

            if (null !== $installedVersion && $installedVersion === $release->version) {
                $io->success(sprintf('Oxlint %s is already installed.', $release->version));

                return self::SUCCESS;
            }

            $io->warning(null === $installedVersion
                ? 'Oxlint is already installed, replacing it.'
                : sprintf('Oxlint %s is already installed, but requested version is %s. Replacing it.', $installedVersion, $release->version));
            $this->filesystem->remove($binaryDestinationPath);
        }

        $platform = Platform::guess();
        $archiveName = OxlintBinary::getArchiveName($release, $platform);
        $this->downloadArchiveAndExtractTo(
            $io,
            $release->getAssetUrl($archiveName),
            $archiveName,
            OxlintBinary::getName($release, $platform),
            $binaryDestinationPath
        );

        $io->success(sprintf('Done, you can now run Oxlint with "%s".', Path::makeRelative($binaryDestinationPath, (string) getcwd())));

        return self::SUCCESS;
    }
}
