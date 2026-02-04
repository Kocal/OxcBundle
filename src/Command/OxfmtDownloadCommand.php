<?php

declare(strict_types=1);

namespace Kocal\OxcBundle\Command;

use Kocal\OxcBundle\OxfmtBinary;
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
        ?HttpClientInterface $httpClient = null,
        private readonly string $appsVersion,
    ) {
        parent::__construct($this->filesystem, $httpClient);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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

        // TODO: "apps_version" is in fact corresponding to Oxlint version and not Oxfmt version.
        // Meaning that we can't check if Oxfmt needs to be updated or not. It may be improved in the future.
        //
        // if ($this->filesystem->exists($binaryDestinationPath)) {
        //     $installedVersion = $this->extractVersionFromBinary($binaryDestinationPath);
        //
        //     if ($installedVersion === $this->appsVersion) {
        //         $io->success(sprintf('Oxfmt %s is already installed.', $this->appsVersion));
        //
        //         return self::SUCCESS;
        //     }
        //
        //     $io->warning(sprintf('Oxfmt %s is already installed, but requested version is %s. Replacing it.', $installedVersion, $this->appsVersion));
        //     $this->filesystem->remove($binaryDestinationPath);
        // }

        $archiveUrl = sprintf(
            'https://github.com/oxc-project/oxc/releases/download/apps_v%s/%s',
            $this->appsVersion,
            $archiveName = OxfmtBinary::getArchiveName(),
        );
        $this->downloadArchiveAndExtractTo(
            $io,
            $archiveUrl,
            $archiveName,
            OxfmtBinary::getName(),
            $binaryDestinationPath
        );

        $io->success(sprintf('Done, you can now run Oxfmt with "%s".', Path::makeRelative($binaryDestinationPath, getcwd())));

        return self::SUCCESS;
    }
}
