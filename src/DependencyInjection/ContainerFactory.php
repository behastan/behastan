<?php

declare(strict_types=1);

namespace Rector\Behastan\DependencyInjection;

use Illuminate\Container\Container;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Rector\Behastan\Analyzer\ContextDefinitionsAnalyzer;
use Rector\Behastan\Command\AnalyzeCommand;
use Rector\Behastan\Command\DuplicatedDefinitionsCommand;
use Rector\Behastan\Command\UnusedDefinitionsCommand;
use Rector\Behastan\Contract\RuleInterface;
use Rector\Behastan\Rule\DuplicatedContextDefinitionContentsRule;
use Rector\Behastan\Rule\DuplicatedMaskRule;
use Rector\Behastan\Rule\UnusedContextDefinitionsRule;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class ContainerFactory
{
    /**
     * @api used in bin and tests
     */
    public function create(): Container
    {
        $container = new Container();

        // console
        $container->singleton(Application::class, function (Container $container): Application {
            $application = new Application('Behastan', '0.4');

            // register commands
            foreach ([
                DuplicatedDefinitionsCommand::class,
                UnusedDefinitionsCommand::class,
                AnalyzeCommand::class,
            ] as $commandClass) {
                $command = $container->make($commandClass);
                $application->add($command);
            }

            // remove basic command to make output clear
            $this->hideDefaultCommands($application);

            return $application;
        });

        // parser
        $container->singleton(Parser::class, static function (): Parser {
            $phpParserFactory = new ParserFactory();
            return $phpParserFactory->createForHostVersion();
        });

        // to re-use
        $container->singleton(ContextDefinitionsAnalyzer::class);

        // silence in PHPUnit tests to keep output clear
        $consoleOutput = new ConsoleOutput();
        $consoleOutput->setVerbosity(
            defined('PHPUNIT_COMPOSER_INSTALL') ? ConsoleOutput::VERBOSITY_QUIET : ConsoleOutput::VERBOSITY_NORMAL
        );

        $container->singleton(
            SymfonyStyle::class,
            static fn (): SymfonyStyle => new SymfonyStyle(new ArrayInput([]), $consoleOutput)
        );

        $this->registerRule($container, DuplicatedMaskRule::class);
        $this->registerRule($container, DuplicatedContextDefinitionContentsRule::class);
        $this->registerRule($container, UnusedContextDefinitionsRule::class);

        $container->when(AnalyzeCommand::class)
            ->needs('$rules')
            ->giveTagged(RuleInterface::class);

        return $container;
    }

    public function hideDefaultCommands(Application $application): void
    {
        $application->get('list')
            ->setHidden();

        $application->get('completion')
            ->setHidden();

        $application->get('help')
            ->setHidden();
    }

    private function registerRule(Container $container, string $ruleClass): void
    {
        Assert::isAOf($ruleClass, RuleInterface::class);

        $container->singleton($ruleClass);
        $container->tag($ruleClass, RuleInterface::class);
    }
}
