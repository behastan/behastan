<?php

declare(strict_types=1);

namespace Behastan\Command;

use Behastan\Analyzer\ClassMethodContextDefinitionsAnalyzer;
use Behastan\Enum\Option;
use Behastan\Finder\BehatMetafilesFinder;
use Behastan\ValueObject\ClassMethodContextDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class DuplicatedDefinitionsCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly ClassMethodContextDefinitionsAnalyzer $classMethodContextDefinitionsAnalyzer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('duplicated-definitions');

        $this->setDescription(
            'Find duplicated definitions in *Context.php, use just one to keep definitions clear and to the point'
        );

        $this->addArgument(
            Option::TEST_DIRECTORY,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Director with *.Context.php definition files'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testDirectories = (array) $input->getArgument(Option::TEST_DIRECTORY);
        Assert::allDirectory($testDirectories);

        $contextFileInfos = BehatMetafilesFinder::findContextFiles($testDirectories);
        if ($contextFileInfos === []) {
            $this->symfonyStyle->error('No *.Context files found. Please provide correct test directory');
            return self::FAILURE;
        }

        $classMethodContextDefinitionByClassMethodHash = $this->classMethodContextDefinitionsAnalyzer->resolveAndGroupByContentHash(
            $contextFileInfos
        );

        // keep only duplicated
        $classMethodContextDefinitionByClassMethodHash = $this->filterOutNotDuplicated(
            $classMethodContextDefinitionByClassMethodHash
        );

        foreach ($classMethodContextDefinitionByClassMethodHash as $i => $classAndMethods) {
            $this->symfonyStyle->section(sprintf('%d)', $i + 1));

            foreach ($classAndMethods as $classMethodContextDefinition) {
                /** @var ClassMethodContextDefinition $classMethodContextDefinition */
                $relativeFilePath = substr(
                    $classMethodContextDefinition->getFilePath(),
                    strlen($testDirectories[0]) + 1
                );

                $this->symfonyStyle->writeln(
                    $relativeFilePath . ':' . $classMethodContextDefinition->getMethodLine()
                );

                $this->symfonyStyle->writeln('Mask: <fg=green>"' . $classMethodContextDefinition->getMask() . '"</>');
                $this->symfonyStyle->newLine();
            }

            $this->symfonyStyle->newLine();
        }

        $this->symfonyStyle->error(
            sprintf('Found %d duplicated class method contents', count(
                $classMethodContextDefinitionByClassMethodHash
            ))
        );

        return Command::FAILURE;
    }

    /**
     * @template TItem as object
     *
     * @param TItem[] $items
     * @return array<int, TItem>
     */
    private function filterOutNotDuplicated(array $items): array
    {
        foreach ($items as $hash => $classAndMethods) {
            if (count($classAndMethods) < 2) {
                unset($items[$hash]);
            }
        }

        return array_values($items);
    }
}
