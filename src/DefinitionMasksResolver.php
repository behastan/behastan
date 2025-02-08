<?php

declare(strict_types=1);

namespace Behastan;

use Behastan\Analyzer\MaskAnalyzer;
use Behastan\PhpParser\SimplePhpParser;
use Behastan\Resolver\ClassMethodMasksResolver;
use Behastan\ValueObject\ClassMethodContextDefinition;
use Behastan\ValueObject\Mask\ExactMask;
use Behastan\ValueObject\Mask\NamedMask;
use Behastan\ValueObject\Mask\RegexMask;
use Behastan\ValueObject\Mask\SkippedMask;
use Behastan\ValueObject\MaskCollection;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use SplFileInfo;

final class DefinitionMasksResolver
{
    public function __construct(
        private readonly SimplePhpParser $simplePhpParser,
        private readonly NodeFinder $nodeFinder,
        private readonly ClassMethodMasksResolver $classMethodMasksResolver,
    ) {
    }

    /**
     * @param SplFileInfo[] $contextFiles
     */
    public function resolve(array $contextFiles): MaskCollection
    {
        $masks = [];

        $classMethodContextDefinitions = $this->resolveMasksFromFiles($contextFiles);

        foreach ($classMethodContextDefinitions as $classMethodContextDefinition) {
            $rawMask = $classMethodContextDefinition->getMask();

            // @todo edge case - handle next
            if (str_contains($rawMask, ' [:')) {
                $masks[] = new SkippedMask(
                    $rawMask,
                    $classMethodContextDefinition->getFilePath(),
                    $classMethodContextDefinition->getClass(),
                    $classMethodContextDefinition->getMethodName()
                );
                continue;
            }

            // regex pattern, handled else-where
            if (MaskAnalyzer::isRegex($rawMask)) {
                $masks[] = new RegexMask(
                    $rawMask,
                    $classMethodContextDefinition->getFilePath(),
                    $classMethodContextDefinition->getClass(),
                    $classMethodContextDefinition->getMethodName()
                );
                continue;
            }

            // handled in mask one
            if (MaskAnalyzer::isValueMask($rawMask)) {
                //  if (str_contains($rawMask, ':')) {
                $masks[] = new NamedMask(
                    $rawMask,
                    $classMethodContextDefinition->getFilePath(),
                    $classMethodContextDefinition->getClass(),
                    $classMethodContextDefinition->getMethodName()
                );
                continue;
            }

            $masks[] = new ExactMask(
                $rawMask,
                $classMethodContextDefinition->getFilePath(),
                $classMethodContextDefinition->getClass(),
                $classMethodContextDefinition->getMethodName()
            );
        }

        return new MaskCollection($masks);
    }

    /**
     * @param SplFileInfo[] $fileInfos
     * @return ClassMethodContextDefinition[]
     */
    private function resolveMasksFromFiles(array $fileInfos): array
    {
        $classMethodContextDefinitions = [];

        foreach ($fileInfos as $fileInfo) {
            $stmts = $this->simplePhpParser->parseFilePath($fileInfo->getRealPath());

            // 1. get class name
            $class = $this->nodeFinder->findFirstInstanceOf($stmts, Class_::class);
            if (! $class instanceof Class_) {
                continue;
            }

            // is magic class?
            if ($class->isAnonymous() || ! $class->namespacedName instanceof Name) {
                continue;
            }

            $className = $class->namespacedName->toString();

            foreach ($class->getMethods() as $classMethod) {
                $rawMasks = $this->classMethodMasksResolver->resolve($classMethod);

                foreach ($rawMasks as $rawMask) {
                    $classMethodContextDefinitions[] = new ClassMethodContextDefinition(
                        $fileInfo->getRealPath(),
                        $className,
                        $classMethod->name->toString(),
                        $rawMask
                    );
                }
            }
        }

        return $classMethodContextDefinitions;
    }
}
