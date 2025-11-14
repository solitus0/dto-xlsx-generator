<?php

declare(strict_types=1);

use Solitus0\DtoXlsxGenerator\AttributesResolver\SpreadsheetResolverInterface;
use Solitus0\DtoXlsxGenerator\Services\SpreadsheetGenerator;
use Solitus0\DtoXlsxGenerator\Validator\DtoConfigurationValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container
        ->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->instanceof(SpreadsheetResolverInterface::class)
        ->tag('solitus0.dto.attributes_resolver');

    $services
        ->load('Solitus0\\DtoXlsxGenerator\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/Attributes/',
        ]);

    $services
        ->set(SpreadsheetGenerator::class)
        ->arg('$resolvers', tagged_iterator('solitus0.dto.attributes_resolver'));

    $services->alias('solitus0.dto_configuration_validator', DtoConfigurationValidator::class);
};
