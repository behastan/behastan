<?php

declare(strict_types=1);

namespace Rector\Behastan\Analyzer;

use Nette\Utils\Strings;
use Rector\Behastan\DefinitionMasksResolver;
use Rector\Behastan\Reporting\MaskCollectionStatsPrinter;
use Rector\Behastan\UsedInstructionResolver;
use Rector\Behastan\ValueObject\Mask\AbstractMask;
use Rector\Behastan\ValueObject\Mask\ExactMask;
use Rector\Behastan\ValueObject\Mask\NamedMask;
use Rector\Behastan\ValueObject\Mask\RegexMask;
use Rector\Behastan\ValueObject\Mask\SkippedMask;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;

final readonly class UnusedDefinitionsAnalyzer
{
    /**
     * @var string
     */
    private const MASK_VALUE_REGEX = '#(\:[\W\w]+)#';

    public function __construct(
        private SymfonyStyle $symfonyStyle,
        private DefinitionMasksResolver $definitionMasksResolver,
        private UsedInstructionResolver $usedInstructionResolver,
        private MaskCollectionStatsPrinter $maskCollectionStatsPrinter,
    ) {
    }

    /**
     * @param SplFileInfo[] $contextFiles
     * @param SplFileInfo[] $featureFiles
     *
     * @return AbstractMask[]
     */
    public function analyse(array $contextFiles, array $featureFiles): array
    {
        $maskCollection = $this->definitionMasksResolver->resolve($contextFiles);
        $this->maskCollectionStatsPrinter->printStats($maskCollection);

        $featureInstructions = $this->usedInstructionResolver->resolveInstructionsFromFeatureFiles($featureFiles);
        $maskProgressBar = $this->symfonyStyle->createProgressBar($maskCollection->count());

        $unusedMasks = [];
        foreach ($maskCollection->all() as $mask) {
            $maskProgressBar->advance();

            if ($this->isMaskUsed($mask, $featureInstructions)) {
                continue;
            }

            $unusedMasks[] = $mask;
        }

        $maskProgressBar->finish();

        return $unusedMasks;
    }

    /**
     * @param string[] $featureInstructions
     */
    private function isRegexDefinitionUsed(string $regexBehatDefinition, array $featureInstructions): bool
    {
        foreach ($featureInstructions as $featureInstruction) {
            if (Strings::match($featureInstruction, $regexBehatDefinition)) {
                // it is used!
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $featureInstructions
     */
    private function isMaskUsed(AbstractMask $mask, array $featureInstructions): bool
    {
        if ($mask instanceof SkippedMask) {
            return true;
        }

        // is used?
        if ($mask instanceof ExactMask && in_array($mask->mask, $featureInstructions, true)) {
            return true;
        }

        // is used?
        if ($mask instanceof RegexMask && $this->isRegexDefinitionUsed($mask->mask, $featureInstructions)) {
            return true;
        }

        if ($mask instanceof NamedMask) {
            // normalize :mask definition to regex
            $regexMask = '#' . Strings::replace($mask->mask, self::MASK_VALUE_REGEX, '(.*?)') . '#';

            if ($this->isRegexDefinitionUsed($regexMask, $featureInstructions)) {
                return true;
            }
        }

        return false;
    }
}
