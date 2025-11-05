<?php

declare (strict_types=1);
namespace Behastan\Command;

use Behastan\Analyzer\ClassMethodContextDefinitionsAnalyzer;
use Behastan\Enum\Option;
use Behastan\Finder\BehatMetafilesFinder;
use Behastan202511\Symfony\Component\Console\Command\Command;
use Behastan202511\Symfony\Component\Console\Input\InputArgument;
use Behastan202511\Symfony\Component\Console\Input\InputInterface;
use Behastan202511\Symfony\Component\Console\Output\OutputInterface;
use Behastan202511\Symfony\Component\Console\Style\SymfonyStyle;
use Behastan202511\Webmozart\Assert\Assert;
final class DuplicatedDefinitionsCommand extends Command
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Behastan\Analyzer\ClassMethodContextDefinitionsAnalyzer
     */
    private $classMethodContextDefinitionsAnalyzer;
    public function __construct(SymfonyStyle $symfonyStyle, ClassMethodContextDefinitionsAnalyzer $classMethodContextDefinitionsAnalyzer)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->classMethodContextDefinitionsAnalyzer = $classMethodContextDefinitionsAnalyzer;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('duplicated-definitions');
        $this->setDescription('Find duplicated definitions in *Context.php, use just one to keep definitions clear and to the point');
        $this->addArgument(Option::TEST_DIRECTORY, InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Director with *.Context.php definition files');
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
        $classMethodContextDefinitionByClassMethodHash = $this->classMethodContextDefinitionsAnalyzer->resolveAndGroupByContentHash($contextFileInfos);
        // keep only duplicated
        $classMethodContextDefinitionByClassMethodHash = $this->filterOutNotDuplicated($classMethodContextDefinitionByClassMethodHash);
        $i = 0;
        foreach ($classMethodContextDefinitionByClassMethodHash as $classMethodContextDefinition) {
            $this->symfonyStyle->section(sprintf('%d)', $i + 1));
            foreach ($classMethodContextDefinition as $classAndMethod) {
                $relativeFilePath = (string) substr($classAndMethod->getFilePath(), strlen((string) $testDirectories[0]) + 1);
                $this->symfonyStyle->writeln($relativeFilePath . ':' . $classAndMethod->getMethodLine());
                $this->symfonyStyle->writeln('Mask: <fg=green>"' . $classAndMethod->getMask() . '"</>');
                $this->symfonyStyle->newLine();
            }
            $this->symfonyStyle->newLine();
            ++$i;
        }
        $this->symfonyStyle->error(sprintf('Found %d duplicated class method contents', count($classMethodContextDefinitionByClassMethodHash)));
        return Command::FAILURE;
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
}
