<?php

declare(strict_types=1);

namespace Behastan\DependencyInjection;

use Behastan\Command\DuplicatedDefinitionsCommand;
use Behastan\Command\StatsCommand;
use Behastan\Command\UnusedDefinitionsCommand;
use Illuminate\Container\Container;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            $application = new Application('Behastan');

            // register commands
            foreach ([
                DuplicatedDefinitionsCommand::class,
                UnusedDefinitionsCommand::class,
                StatsCommand::class,
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

        $container->singleton(
            SymfonyStyle::class,
            static fn (): SymfonyStyle => new SymfonyStyle(new ArrayInput([]), new ConsoleOutput())
        );

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
}
