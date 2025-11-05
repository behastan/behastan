<?php

declare(strict_types=1);

namespace Rector\Behastan\Analyzer;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\PrettyPrinter\Standard;
use Rector\Behastan\PhpParser\SimplePhpParser;
use Rector\Behastan\Resolver\ClassMethodMasksResolver;
use Rector\Behastan\ValueObject\ClassMethodContextDefinition;
use Symfony\Component\Finder\SplFileInfo;

final readonly class ClassMethodContextDefinitionsAnalyzer
{
    public function __construct(
        private SimplePhpParser $simplePhpParser,
        private NodeFinder $nodeFinder,
        private Standard $printerStandard,
        private ClassMethodMasksResolver $classMethodMasksResolver,
    ) {
    }

    /**
     * @param SplFileInfo[] $contextFileInfos
     * @return ClassMethodContextDefinition[]
     */
    public function resolve(array $contextFileInfos): array
    {
        $classMethodContextDefinitionByClassMethodHash = $this->resolveAndGroupByContentHash($contextFileInfos);

        $classMethodContextDefinitions = [];
        foreach ($classMethodContextDefinitionByClassMethodHash as $classMethodContextDefinition) {
            $classMethodContextDefinitions = array_merge(
                $classMethodContextDefinitions,
                $classMethodContextDefinition
            );
        }

        return $classMethodContextDefinitions;
    }

    /**
     * @param SplFileInfo[] $contextFileInfos
     * @return array<string, ClassMethodContextDefinition[]>
     */
    public function resolveAndGroupByContentHash(array $contextFileInfos): array
    {
        $classMethodContextDefinitionByClassMethodHash = [];

        foreach ($contextFileInfos as $contextFileInfo) {
            $contextClassStmts = $this->simplePhpParser->parseFilePath($contextFileInfo->getRealPath());

            $class = $this->nodeFinder->findFirstInstanceOf($contextClassStmts, Class_::class);
            if (! $class instanceof Class_) {
                continue;
            }
            if (! $class->namespacedName instanceof Name) {
                continue;
            }

            $className = $class->namespacedName->toString();

            foreach ($class->getMethods() as $classMethod) {
                if (! $classMethod->isPublic()) {
                    continue;
                }
                if ($classMethod->isMagic()) {
                    continue;
                }
                $classMethodHash = $this->createClassMethodHash($classMethod);

                $rawMasks = $this->classMethodMasksResolver->resolve($classMethod);

                // no masks :(
                if ($rawMasks === []) {
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

        return $classMethodContextDefinitionByClassMethodHash;
    }

    private function createClassMethodHash(ClassMethod $classMethod): string
    {
        $printedClassMethod = $this->printerStandard->prettyPrint((array) $classMethod->stmts);
        return sha1($printedClassMethod);
    }
}
