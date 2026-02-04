<?php

declare(strict_types=1);

namespace Kocal\OxcBundle;

use Kocal\OxcBundle\DependencyInjection\OxcBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class KocalOxcBundle extends Bundle
{
    protected function createContainerExtension(): ExtensionInterface
    {
        return new OxcBundle();
    }
}
