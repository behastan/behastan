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

        $this->addArgument(
            'test-directory',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'One or more paths to check or *.Context.php and feature.yml files'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testDirectories = (array) $input->getArgument('test-directory');
        Assert::allDirectory($testDirectories);

        $contextFileInfos = $this->behatMetafilesFinder->findContextFiles($testDirectories);

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
        foreach ($classMethodContextDefinitionByClassMethodHash as $hash => $classAndMethods) {
            if (count($classAndMethods) < 2) {
                unset($classMethodContextDefinitionByClassMethodHash[$hash]);
            }

        }

        foreach ($classMethodContextDefinitionByClassMethodHash as $classAndMethods) {
            $this->symfonyStyle->warning('Found duplicated class classMethod contents');

            foreach ($classAndMethods as $classMethodContextDefinition) {
                /** @var ClassMethodContextDefinition $classMethodContextDefinition */
                $this->symfonyStyle->writeln(
                    ' * ' . $classMethodContextDefinition->getClass() . '::' . $classMethodContextDefinition->getMethodName() . ' in '
                );
                $this->symfonyStyle->writeln(
                    $classMethodContextDefinition->getFilePath() . ':' . $classMethodContextDefinition->getMethodLine()
                );

                $this->symfonyStyle->writeln('Mask: <fg=green>' . $classMethodContextDefinition->getMask() . '</>');
                $this->symfonyStyle->newLine();
            }
        }

        //        $this->symfonyStyle->error(sprintf('Found %d duplicated class classMethod contents', count($classMethodContextDefinitionByClassMethodHash)));

        return Command::FAILURE;
    }

    private function createClassMethodHash(ClassMethod $classMethod): string
    {
        $printedClassMethod = $this->printerStandard->prettyPrint((array) $classMethod->stmts);
        return sha1($printedClassMethod);
    }
}
