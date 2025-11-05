<?php

declare(strict_types=1);

namespace Rector\Behastan\Reporting;

use Rector\Behastan\ValueObject\Mask\AbstractMask;
use Rector\Behastan\ValueObject\Mask\ExactMask;
use Rector\Behastan\ValueObject\Mask\NamedMask;
use Rector\Behastan\ValueObject\Mask\RegexMask;
use Rector\Behastan\ValueObject\Mask\SkippedMask;
use Rector\Behastan\ValueObject\MaskCollection;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class MaskCollectionStatsPrinter
{
    public function __construct(
        private SymfonyStyle $symfonyStyle
    ) {
    }

    public function printStats(MaskCollection $maskCollection): void
    {
        $this->symfonyStyle->writeln(sprintf('Found %d masks:', $maskCollection->count()));
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->writeln(sprintf(' * %d exact', $maskCollection->countByType(ExactMask::class)));
        $this->symfonyStyle->writeln(sprintf(' * %d /regex/', $maskCollection->countByType(RegexMask::class)));
        $this->symfonyStyle->writeln(sprintf(' * %d :named', $maskCollection->countByType(NamedMask::class)));
        $this->symfonyStyle->writeln(sprintf(' * %d skipped', $maskCollection->countByType(SkippedMask::class)));

        $skippedMasks = $maskCollection->byType(SkippedMask::class);
        if ($skippedMasks !== []) {
            $this->symfonyStyle->newLine();

            foreach ($skippedMasks as $skippedMask) {
                $this->printMask($skippedMask);
            }

            $this->symfonyStyle->newLine();
        }
    }

    private function printMask(AbstractMask $unusedMask): void
    {
        $this->symfonyStyle->writeln($unusedMask->mask);

        // make path relative
        $relativeFilePath = str_replace(getcwd() . '/', '', $unusedMask->filePath);
        $this->symfonyStyle->writeln($relativeFilePath);
        $this->symfonyStyle->newLine();
    }
}
