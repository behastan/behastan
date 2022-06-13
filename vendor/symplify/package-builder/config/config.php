<?php

declare (strict_types=1);
namespace EasyCI20220613;

use EasyCI20220613\SebastianBergmann\Diff\Differ;
use EasyCI20220613\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use EasyCI20220613\Symplify\PackageBuilder\Console\Formatter\ColorConsoleDiffFormatter;
use EasyCI20220613\Symplify\PackageBuilder\Console\Output\ConsoleDiffer;
use EasyCI20220613\Symplify\PackageBuilder\Diff\Output\CompleteUnifiedDiffOutputBuilderFactory;
use EasyCI20220613\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->set(ColorConsoleDiffFormatter::class);
    $services->set(ConsoleDiffer::class);
    $services->set(CompleteUnifiedDiffOutputBuilderFactory::class);
    $services->set(Differ::class);
    $services->set(PrivatesAccessor::class);
};
