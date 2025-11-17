<?php

declare(strict_types=1);

namespace Rector\Behastan\Command;

use Rector\Behastan\Contract\RuleInterface;
use Rector\Behastan\DefinitionMasksExtractor;
use Rector\Behastan\Enum\Option;
use Rector\Behastan\Finder\BehatMetafilesFinder;
use Rector\Behastan\Reporting\MaskCollectionStatsPrinter;
use Rector\Behastan\ValueObject\RuleError;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class AnalyzeCommand extends Command
{
    /**
     * @param RuleInterface[] $rules
     */
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly DefinitionMasksExtractor $definitionMasksExtractor,
        private readonly MaskCollectionStatsPrinter $maskCollectionStatsPrinter,
        private readonly array $rules
    ) {
        parent::__construct();

        Assert::allObject($rules);
        Assert::allIsInstanceOf($rules, RuleInterface::class);
        Assert::notEmpty($rules);
        Assert::greaterThan(count($rules), 2);
    }

    protected function configure(): void
    {
        $this->setName('analyze');
        $this->setDescription('Run complete static analysis on Behat definitions and features');

        $this->addArgument(
            Option::PROJECT_DIRECTORY,
            InputArgument::OPTIONAL,
            'Project directory (we find *.Context.php definition files and *.feature script files there)',
            getcwd()
        );

        $this->addOption(
            'skip',
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Skip a rule by identifier'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testDirectory = $input->getArgument(Option::PROJECT_DIRECTORY);
        Assert::directory($testDirectory);

        $contextFileInfos = BehatMetafilesFinder::findContextFiles([$testDirectory]);
        if ($contextFileInfos === []) {
            $this->symfonyStyle->error(sprintf(
                'No *.Context files found in "%s". Please provide correct test directory',
                $testDirectory
            ));
            return self::FAILURE;
        }

        $featureFileInfos = BehatMetafilesFinder::findFeatureFiles([$testDirectory]);
        if ($featureFileInfos === []) {
            $this->symfonyStyle->error(sprintf(
                'No *.feature files found in "%s". Please provide correct test directory',
                $testDirectory
            ));
            return self::FAILURE;
        }

        $this->symfonyStyle->writeln(sprintf(
            '<fg=green>Found %d Context and %d feature files</>',
            count($contextFileInfos),
            count($featureFileInfos)
        ));
        $this->symfonyStyle->writeln('<fg=yellow>Extracting definitions masks...</>');

        $maskCollection = $this->definitionMasksExtractor->extract($contextFileInfos);
        $this->symfonyStyle->newLine();

        $this->maskCollectionStatsPrinter->print($maskCollection);

        $this->symfonyStyle->newLine();

        // @todo skip by "--skip" option

        $this->symfonyStyle->writeln('<fg=yellow>Running analysis...</>');

        /** @var RuleError[] $allRuleErrors */
        $allRuleErrors = [];
        foreach ($this->rules as $rule) {
            $ruleErrors = $rule->process($contextFileInfos, $featureFileInfos, $maskCollection, $testDirectory);
            $allRuleErrors = array_merge($allRuleErrors, $ruleErrors);
        }

        if ($allRuleErrors === []) {
            $this->symfonyStyle->success('No errors found. Good job!');

            return self::SUCCESS;
        }

        $this->symfonyStyle->newLine(2);

        $i = 1;
        foreach ($allRuleErrors as $allRuleError) {
            $this->symfonyStyle->writeln(sprintf('<fg=yellow>%d) %s</>', $i, $allRuleError->getMessage()));
            foreach ($allRuleError->getLineFilePaths() as $lineFilePath) {
                // compared to listing() this allow to make paths clickable in IDE
                $this->symfonyStyle->writeln($lineFilePath);
            }

            $this->symfonyStyle->newLine(2);

            ++$i;
        }

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->error(sprintf(
            'Found %d error%s',
            count($allRuleErrors),
            count($allRuleErrors) > 1 ? 's' : ''
        ));

        return self::FAILURE;
    }
}
