<?php

declare(strict_types=1);

namespace Rector\Behastan\Reporting;

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

    public function print(MaskCollection $maskCollection): void
    {
        $this->symfonyStyle->writeln(sprintf('Found %d masks:', $maskCollection->count()));
        $this->symfonyStyle->writeln(sprintf(' * %d exact', $maskCollection->countByType(ExactMask::class)));
        $this->symfonyStyle->writeln(sprintf(' * %d /regex/', $maskCollection->countByType(RegexMask::class)));
        $this->symfonyStyle->writeln(sprintf(' * %d :named', $maskCollection->countByType(NamedMask::class)));

        $this->printSkippedMasks($maskCollection);
    }

    private function printSkippedMasks(MaskCollection $maskCollection): void
    {
        $skippedMasks = $maskCollection->byType(SkippedMask::class);
        if ($skippedMasks === []) {
            return;
        }

        $skippedMasksValues = [];
        foreach ($skippedMasks as $skippedMask) {
            $skippedMasksValues[] = $skippedMask->mask;
        }

        $skippedMasksString = implode('", "', $skippedMasksValues);

        $this->symfonyStyle->writeln(sprintf(
            ' * %d skipped ("%s")',
            $maskCollection->countByType(SkippedMask::class),
            $skippedMasksString
        ));
    }
}
