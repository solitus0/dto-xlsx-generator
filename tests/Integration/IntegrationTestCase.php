<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Solitus0\DtoXlsxGenerator\Services\SpreadsheetGenerator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

abstract class IntegrationTestCase extends TestCase
{
    private ?ContainerBuilder $container = null;

    protected function container(): ContainerBuilder
    {
        if ($this->container !== null) {
            return $this->container;
        }

        $container = new ContainerBuilder();
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 1) . '/../config')
        );
        $loader->load('services.php');

        if ($container->hasDefinition(SpreadsheetGenerator::class)) {
            $container
                ->getDefinition(SpreadsheetGenerator::class)
                ->setPublic(true);
        }

        $container->compile();

        return $this->container = $container;
    }

    protected function resolveSpreadsheetAttributesUseCase(): SpreadsheetGenerator
    {
        return $this->container()->get(SpreadsheetGenerator::class);
    }
}
