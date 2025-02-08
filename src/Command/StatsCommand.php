<?php

declare(strict_types=1);

namespace Behastan\Command;

use Behastan\Analyzer\ClassMethodContextDefinitionsAnalyzer;
use Behastan\Finder\BehatMetafilesFinder;
use Behastan\UsedInstructionResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class StatsCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly UsedInstructionResolver $usedInstructionResolver,
        private readonly ClassMethodContextDefinitionsAnalyzer $classMethodContextDefinitionsAnalyzer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('stats');

        $this->setDescription('Get Definition usage stats');

        $this->addArgument(
            'test-directory',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Directories with *.Context.php and feature.yml files'
        );
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

        foreach ($classMethodContextDefinitions as $i => $classMethodContextDefinition) {
            $section = sprintf('%d) %s', $i + 1, $classMethodContextDefinition->getMask());
            $this->symfonyStyle->section($section);
            $this->symfonyStyle->newLine();
        }

        dump(123);
        die;

        return Command::SUCCESS;
    }
}
