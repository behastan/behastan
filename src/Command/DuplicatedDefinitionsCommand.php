<?php

declare(strict_types=1);

namespace Rector\Behastan\Command;

use Rector\Behastan\Analyzer\ClassMethodContextDefinitionsAnalyzer;
use Rector\Behastan\Enum\Option;
use Rector\Behastan\Finder\BehatMetafilesFinder;
use Rector\Behastan\ValueObject\ClassMethodContextDefinition;
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

        // 1. find duplicated masks, e.g. if 2 methods have the same mask, its a race condition problem
        $classMethodContextDefinitions = $this->classMethodContextDefinitionsAnalyzer->resolve($contextFileInfos);
        $groupedByMask = [];
        foreach ($classMethodContextDefinitions as $classMethodContextDefinition) {
            $groupedByMask[$classMethodContextDefinition->getMask()][] = $classMethodContextDefinition;
        }

        foreach ($groupedByMask as $mask => $sameMaksClassMethodContextDefinitions) {
            if (count($sameMaksClassMethodContextDefinitions) === 1) {
                continue;
            }

            // two or more methods have the same mask
            $this->symfonyStyle->section('Duplicated mask: "' . $mask . '"');
            foreach ($sameMaksClassMethodContextDefinitions as $classMethodContextDefinition) {
                $relativeFilePath = substr(
                    $classMethodContextDefinition->getFilePath(),
                    strlen((string) $testDirectories[0]) + 1
                );
                $this->symfonyStyle->writeln($relativeFilePath . ':' . $classMethodContextDefinition->getMethodLine());
            }

            $this->symfonyStyle->newLine();
        }

        // 2. find duplicate method contents
        $classMethodContextDefinitionByClassMethodHash = $this->classMethodContextDefinitionsAnalyzer->resolveAndGroupByContentHash(
            $contextFileInfos
        );

        return $this->reportDuplicateMethodBodyContents(
            $classMethodContextDefinitionByClassMethodHash,
            $testDirectories[0]
        );
    }

    /**
     * @template TItem as object
     *
     * @param array<string, TItem[]> $items
     * @return array<string, TItem[]>
     */
    private function filterOutNotDuplicated(array $items): array
    {
        foreach ($items as $hash => $classAndMethods) {
            if (count($classAndMethods) < 2) {
                unset($items[$hash]);
            }
        }

        return $items;
    }

    /**
     * @param array<string, ClassMethodContextDefinition[]> $classMethodContextDefinitionByClassMethodHash
     */
    private function reportDuplicateMethodBodyContents(
        array $classMethodContextDefinitionByClassMethodHash,
        string $testDirectory
    ): int {
        // keep only duplicated
        $classMethodContextDefinitionByClassMethodHash = $this->filterOutNotDuplicated(
            $classMethodContextDefinitionByClassMethodHash
        );

        if ($classMethodContextDefinitionByClassMethodHash === []) {
            return self::SUCCESS;
        }

        $i = 0;
        foreach ($classMethodContextDefinitionByClassMethodHash as $classMethodContextDefinition) {
            $this->symfonyStyle->writeln(str_repeat('-', 80));
            $this->symfonyStyle->newLine();

            foreach ($classMethodContextDefinition as $classAndMethod) {
                $relativeFilePath = substr($classAndMethod->getFilePath(), strlen((string) $testDirectory) + 1);

                $this->symfonyStyle->writeln('Mask: <fg=green>"' . $classAndMethod->getMask() . '"</>');
                $this->symfonyStyle->writeln($relativeFilePath . ':' . $classAndMethod->getMethodLine());

                $this->symfonyStyle->newLine();
            }

            ++$i;
        }

        $this->symfonyStyle->newLine();

        $this->symfonyStyle->error(
            sprintf('Found %d definitions with different masks, but same method body', count(
                $classMethodContextDefinitionByClassMethodHash
            ))
        );

        return self::FAILURE;
    }
}
