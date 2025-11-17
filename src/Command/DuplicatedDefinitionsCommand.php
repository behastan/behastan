<?php

declare(strict_types=1);

namespace Rector\Behastan\Command;

use Rector\Behastan\Enum\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @deprecated This command was deprecated, to avoid granular rules. Use "analyze" command instead
 */
final class DuplicatedDefinitionsCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('duplicated-definitions');

        $this->setDescription(
            '[DEPRECATED] Find duplicated definitions in *Context.php, use just one to keep definitions clear and to the point'
        );

        $this->addArgument(
            Option::PROJECT_DIRECTORY,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Director with *.Context.php definition files'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->error(
            'This command was deprecated, to avoid granular rules. Use "analyze" command that runs them all instead'
        );

        return self::FAILURE;
    }
}
