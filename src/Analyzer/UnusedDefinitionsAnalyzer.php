<?php

declare (strict_types=1);
namespace Behastan\Analyzer;

use Behastan202511\Behastan\DefinitionMasksResolver;
use Behastan\Reporting\MaskCollectionStatsPrinter;
use Behastan202511\Behastan\UsedInstructionResolver;
use Behastan\ValueObject\Mask\AbstractMask;
use Behastan\ValueObject\Mask\ExactMask;
use Behastan\ValueObject\Mask\NamedMask;
use Behastan\ValueObject\Mask\RegexMask;
use Behastan\ValueObject\Mask\SkippedMask;
use Behastan202511\Nette\Utils\Strings;
use Behastan202511\Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;
final class UnusedDefinitionsAnalyzer
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Behastan\DefinitionMasksResolver
     */
    private $definitionMasksResolver;
    /**
     * @readonly
     * @var \Behastan\UsedInstructionResolver
     */
    private $usedInstructionResolver;
    /**
     * @readonly
     * @var \Behastan\Reporting\MaskCollectionStatsPrinter
     */
    private $maskCollectionStatsPrinter;
    /**
     * @var string
     */
    private const MASK_VALUE_REGEX = '#(\:[\W\w]+)#';
    public function __construct(SymfonyStyle $symfonyStyle, DefinitionMasksResolver $definitionMasksResolver, UsedInstructionResolver $usedInstructionResolver, MaskCollectionStatsPrinter $maskCollectionStatsPrinter)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->definitionMasksResolver = $definitionMasksResolver;
        $this->usedInstructionResolver = $usedInstructionResolver;
        $this->maskCollectionStatsPrinter = $maskCollectionStatsPrinter;
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
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $featureInstructions
     */
    private function isMaskUsed(AbstractMask $mask, array $featureInstructions): bool
    {
        if ($mask instanceof SkippedMask) {
            return \true;
        }
        // is used?
        if ($mask instanceof ExactMask && in_array($mask->mask, $featureInstructions, \true)) {
            return \true;
        }
        // is used?
        if ($mask instanceof RegexMask && $this->isRegexDefinitionUsed($mask->mask, $featureInstructions)) {
            return \true;
        }
        if ($mask instanceof NamedMask) {
            // normalize :mask definition to regex
            $regexMask = '#' . Strings::replace($mask->mask, self::MASK_VALUE_REGEX, '(.*?)') . '#';
            if ($this->isRegexDefinitionUsed($regexMask, $featureInstructions)) {
                return \true;
            }
        }
        return \false;
    }
}
