<?php

declare (strict_types=1);
namespace Behastan\Command;

use Behastan\Analyzer\ClassMethodContextDefinitionsAnalyzer;
use Behastan\Analyzer\MaskAnalyzer;
use Behastan\Finder\BehatMetafilesFinder;
use Behastan202511\Behastan\UsedInstructionResolver;
use Behastan202511\Symfony\Component\Console\Command\Command;
use Behastan202511\Symfony\Component\Console\Input\InputArgument;
use Behastan202511\Symfony\Component\Console\Input\InputInterface;
use Behastan202511\Symfony\Component\Console\Output\OutputInterface;
use Behastan202511\Symfony\Component\Console\Style\SymfonyStyle;
use Behastan202511\Webmozart\Assert\Assert;
final class StatsCommand extends Command
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Behastan\UsedInstructionResolver
     */
    private $usedInstructionResolver;
    /**
     * @readonly
     * @var \Behastan\Analyzer\ClassMethodContextDefinitionsAnalyzer
     */
    private $classMethodContextDefinitionsAnalyzer;
    public function __construct(SymfonyStyle $symfonyStyle, UsedInstructionResolver $usedInstructionResolver, ClassMethodContextDefinitionsAnalyzer $classMethodContextDefinitionsAnalyzer)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->usedInstructionResolver = $usedInstructionResolver;
        $this->classMethodContextDefinitionsAnalyzer = $classMethodContextDefinitionsAnalyzer;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('stats');
        $this->setDescription('Get Definition usage stats');
        $this->addArgument('test-directory', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Directories with *.Context.php and feature.yml files');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testDirectories = (array) $input->getArgument('test-directory');
        Assert::allDirectory($testDirectories);
        $featureFiles = BehatMetafilesFinder::findFeatureFiles($testDirectories);
        if ($featureFiles === []) {
            $this->symfonyStyle->error('No *.feature files found. Please provide correct test directory');
            return self::FAILURE;
        }
        $contextFiles = BehatMetafilesFinder::findContextFiles($testDirectories);
        if ($contextFiles === []) {
            $this->symfonyStyle->error('No *Context.php files found. Please provide correct test directory');
            return self::FAILURE;
        }
        $this->symfonyStyle->title('Usage stats for PHP definitions in *Feature files');
        $featureInstructions = $this->usedInstructionResolver->resolveInstructionsFromFeatureFiles($featureFiles);
        $classMethodContextDefinitions = $this->classMethodContextDefinitionsAnalyzer->resolve($contextFiles);
        $i = 0;
        foreach ($classMethodContextDefinitions as $classMethodContextDefinition) {
            // @todo handle later, as dynamic
            if (MaskAnalyzer::isRegex($classMethodContextDefinition->getMask())) {
                continue;
            }
            if (MaskAnalyzer::isValueMask($classMethodContextDefinition->getMask())) {
                continue;
            }
            $this->symfonyStyle->writeln(sprintf('%d) <fg=green>%s</>', $i + 1, $classMethodContextDefinition->getMask()));
            $classMethodContextDefinition->recordUsage($featureInstructions);
            $this->symfonyStyle->writeln(' * ' . $classMethodContextDefinition->getUsageCount() . ' usages');
            $this->symfonyStyle->newLine();
            ++$i;
        }
        return Command::SUCCESS;
    }
}
