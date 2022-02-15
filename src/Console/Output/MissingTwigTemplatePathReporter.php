<?php

declare (strict_types=1);
namespace Symplify\EasyCI\Console\Output;

use EasyCI20220215\Symfony\Component\Console\Command\Command;
use EasyCI20220215\Symfony\Component\Console\Style\SymfonyStyle;
final class MissingTwigTemplatePathReporter
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\EasyCI20220215\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @param string[] $errorMessages
     */
    public function report(array $errorMessages) : int
    {
        if ($errorMessages === []) {
            $this->symfonyStyle->success('All templates exists');
            return \EasyCI20220215\Symfony\Component\Console\Command\Command::SUCCESS;
        }
        foreach ($errorMessages as $errorMessage) {
            $this->symfonyStyle->note($errorMessage);
        }
        $missingTemplatesMessage = \sprintf('Found %d missing templates', \count($errorMessages));
        $this->symfonyStyle->error($missingTemplatesMessage);
        return \EasyCI20220215\Symfony\Component\Console\Command\Command::FAILURE;
    }
}
