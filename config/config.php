<?php

declare (strict_types=1);
namespace EasyCI20220509;

use EasyCI20220509\Composer\Semver\Semver;
use EasyCI20220509\Composer\Semver\VersionParser;
use EasyCI20220509\Nette\Neon\Decoder;
use EasyCI20220509\PhpParser\NodeFinder;
use EasyCI20220509\PhpParser\Parser;
use EasyCI20220509\PhpParser\ParserFactory;
use EasyCI20220509\PhpParser\PrettyPrinter\Standard;
use EasyCI20220509\Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCI\Console\EasyCIApplication;
use EasyCI20220509\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use function EasyCI20220509\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/config-packages.php');
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Symplify\\EasyCI\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject']);
    // for autowired commands
    $services->alias(\EasyCI20220509\Symfony\Component\Console\Application::class, \Symplify\EasyCI\Console\EasyCIApplication::class);
    $services->set(\EasyCI20220509\Composer\Semver\VersionParser::class);
    $services->set(\EasyCI20220509\Composer\Semver\Semver::class);
    // neon
    $services->set(\EasyCI20220509\Nette\Neon\Decoder::class);
    // php-parser
    $services->set(\EasyCI20220509\PhpParser\ParserFactory::class);
    $services->set(\EasyCI20220509\PhpParser\Parser::class)->factory([\EasyCI20220509\Symfony\Component\DependencyInjection\Loader\Configurator\service(\EasyCI20220509\PhpParser\ParserFactory::class), 'create'])->args([\EasyCI20220509\PhpParser\ParserFactory::PREFER_PHP7]);
    $services->set(\EasyCI20220509\PhpParser\PrettyPrinter\Standard::class);
    $services->set(\EasyCI20220509\PhpParser\NodeFinder::class);
    $services->set(\EasyCI20220509\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker::class);
};
