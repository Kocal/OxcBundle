<?php

declare(strict_types=1);

namespace Kocal\OxcBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractDownloadBinaryCommand extends Command
{
    protected HttpClientInterface $httpClient;

    public function __construct(
        private Filesystem $filesystem,
        ?HttpClientInterface $httpClient = null,
    ) {
        parent::__construct();

        $this->httpClient = $httpClient ?? new RetryableHttpClient(HttpClient::create());
    }

    protected function configure(): void
    {
        $this
            ->addArgument('destination-dir', InputArgument::OPTIONAL, 'Destination folder', default: getcwd() . '/bin')
            ->addUsage('./bin')
            ->addUsage('./path/to/bin');
    }

    protected function extractVersionFromBinary(string $binaryPath): string
    {
        $process = new Process([$binaryPath, '--version']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('Failed to get version from binary: %s', $process->getErrorOutput()));
        }

        // The version format is "Version: 1.2.3"
        if (!preg_match('/Version:\s*(?P<version>\d+\.\d+\.\d+)/', $process->getOutput(), $matches)) {
            throw new \RuntimeException(sprintf('Could not extract version from binary output: %s', $process->getOutput()));
        }

        return trim($matches['version']);
    }

    protected function downloadArchiveAndExtractTo(SymfonyStyle $io, string $archiveUrl, string $archiveName, string $binaryName, string $binaryDestinationPath): void
    {
        $io->note(sprintf('Downloading %s', $archiveUrl));

        // Download archive...

        $progressBar = null;
        $response = $this->httpClient->request('GET', $archiveUrl, [
            'on_progress' => function (int $dlNow, int $dlSize, array $info) use ($io, &$progressBar): void {
                if (0 === $dlSize) {
                    return;
                }

                if (!$progressBar) {
                    $progressBar = $io->createProgressBar($dlSize);
                    $progressBar->start();
                }

                $progressBar->setProgress($dlNow);
            },
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(sprintf('Failed to download, received status code %d.', $response->getStatusCode()));
        }

        // Write archive to temp file
        $archivePath = tempnam(sys_get_temp_dir(), 'oxc_' . $archiveName);
        $archiveHandler = fopen($archivePath, 'w');
        if (!is_resource($archiveHandler)) {
            throw new \RuntimeException(sprintf('Cannot open file "%s" for writing.', $archivePath));
        }
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($archiveHandler, $chunk->getContent());
        }
        fclose($archiveHandler);

        // Extract archive
        $destinationDir = dirname($binaryDestinationPath);
        $io->note(sprintf('Extract to "%s"...', $destinationDir));

        $phar = new \PharData($archivePath);
        $phar->extractTo($destinationDir, null, true);

        // Move binary to desired location
        $this->filesystem->rename(Path::join($destinationDir, $binaryName), $binaryDestinationPath);
        $this->filesystem->chmod($binaryDestinationPath, 0755);

        // Clean up
        $this->filesystem->remove($archivePath);

        $progressBar?->finish();
        $io->newLine(2);
    }
}
