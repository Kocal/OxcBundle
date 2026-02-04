<?php

declare(strict_types=1);

use Kocal\OxcBundle\Command\AbstractDownloadBinaryCommand;
use Kocal\OxcBundle\Command\OxfmtDownloadCommand;
use Kocal\OxcBundle\Command\OxlintDownloadCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('oxc.command.abstract_download_binary', AbstractDownloadBinaryCommand::class)
        ->abstract()
        ->args([
            service('filesystem'),
            service('http_client')->nullOnInvalid(),
        ])

        ->set('oxc.command.download_oxlint', OxlintDownloadCommand::class)
        ->parent('oxc.command.abstract_download_binary')
        ->arg(2, abstract_arg('Apps version'))
        ->tag('console.command')

        ->set('oxc.command.download_oxfmt', OxfmtDownloadCommand::class)
        ->parent('oxc.command.abstract_download_binary')
        ->arg(2, abstract_arg('Apps version'))
        ->tag('console.command')
    ;
};
