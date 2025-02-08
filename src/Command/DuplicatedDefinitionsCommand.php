<?php

declare(strict_types=1);

namespace Behastan\Command;

use Behastan\Finder\BehatMetafilesFinder;
use Behastan\PhpParser\SimplePhpParser;
use Behastan\Resolver\ClassMethodMasksResolver;
use Behastan\ValueObject\ClassMethodContextDefinition;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\PrettyPrinter\Standard;
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
        private readonly BehatMetafilesFinder $behatMetafilesFinder,
        private readonly SimplePhpParser $simplePhpParser,
        private readonly NodeFinder $nodeFinder,
        private readonly Standard $printerStandard,
        private readonly ClassMethodMasksResolver $classMethodMasksResolver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('duplicated-definitions');

        $this->setDescription(
            'Find duplicated definitions in *Context.php, use just one to keep definitions clear and to the point'
        );

        $this->addArgument('test-directory', InputArgument::REQUIRED, 'Director with *.Context.php definition files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testDirectory = (string) $input->getArgument('test-directory');
        Assert::directory($testDirectory);

        $contextFileInfos = $this->behatMetafilesFinder->findContextFiles([$testDirectory]);

        if ($contextFileInfos === []) {
            $this->symfonyStyle->error('No *.Context files found. Please provide correct test directory');
            return self::FAILURE;
        }

        $classMethodContextDefinitionByClassMethodHash = [];

        foreach ($contextFileInfos as $contextFileInfo) {
            $contextClassStmts = $this->simplePhpParser->parseFilePath($contextFileInfo->getRealPath());

            $class = $this->nodeFinder->findFirstInstanceOf($contextClassStmts, Class_::class);
            if (! $class instanceof Class_ || ! $class->namespacedName instanceof Name) {
                continue;
            }

            $className = $class->namespacedName->toString();

            foreach ($class->getMethods() as $classMethod) {
                if (! $classMethod->isPublic() || $classMethod->isMagic()) {
                    continue;
                }

                $classMethodHash = $this->createClassMethodHash($classMethod);

                $rawMasks = $this->classMethodMasksResolver->resolve($classMethod);

                // no masks :(
                if (count($rawMasks) === 0) {
                    continue;
                }

                $classMethodContextDefinition = new ClassMethodContextDefinition(
                    $contextFileInfo->getRealPath(),
                    $className,
                    $classMethod->name->toString(),
                    // @todo what about multiple masks?
                    $rawMasks[0],
                    $classMethod->getStartLine()
                );

                $classMethodContextDefinitionByClassMethodHash[$classMethodHash][] = $classMethodContextDefinition;
            }
        }

        // keep only duplicated
        $classMethodContextDefinitionByClassMethodHash = $this->filterOutNotDuplicated(
            $classMethodContextDefinitionByClassMethodHash
        );

        foreach ($classMethodContextDefinitionByClassMethodHash as $i => $classAndMethods) {
            $this->symfonyStyle->section(sprintf('%d)', $i + 1));

            foreach ($classAndMethods as $classMethodContextDefinition) {
                /** @var ClassMethodContextDefinition $classMethodContextDefinition */
                $relativeFilePath = substr($classMethodContextDefinition->getFilePath(), strlen($testDirectory) + 1);

                $this->symfonyStyle->writeln(
                    $relativeFilePath . ':' . $classMethodContextDefinition->getMethodLine()
                );

                $this->symfonyStyle->writeln('Mask: <fg=green>"' . $classMethodContextDefinition->getMask() . '"</>');
                $this->symfonyStyle->newLine();
            }

            $this->symfonyStyle->newLine();
        }

        $this->symfonyStyle->error(
            sprintf('Found %d duplicated class classMethod contents', count(
                $classMethodContextDefinitionByClassMethodHash
            ))
        );

        return Command::FAILURE;
    }

    private function createClassMethodHash(ClassMethod $classMethod): string
    {
        $printedClassMethod = $this->printerStandard->prettyPrint((array) $classMethod->stmts);
        return sha1($printedClassMethod);
    }

    /**
     * @template TItem as object
     *
     * @param TItem[] $items
     * @return array<int, TItem>
     */
    private function filterOutNotDuplicated(array $items): array
    {
        foreach ($items as $hash => $classAndMethods) {
            if (count($classAndMethods) < 2) {
                unset($items[$hash]);
            }
        }

        return array_values($items);
    }
}
