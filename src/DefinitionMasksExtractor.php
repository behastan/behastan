<?php

declare(strict_types=1);

namespace Rector\Behastan;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use Rector\Behastan\Analyzer\MaskAnalyzer;
use Rector\Behastan\PhpParser\SimplePhpParser;
use Rector\Behastan\Resolver\ClassMethodMasksResolver;
use Rector\Behastan\ValueObject\ContextDefinition;
use Rector\Behastan\ValueObject\Mask\ExactMask;
use Rector\Behastan\ValueObject\Mask\NamedMask;
use Rector\Behastan\ValueObject\Mask\RegexMask;
use Rector\Behastan\ValueObject\Mask\SkippedMask;
use Rector\Behastan\ValueObject\MaskCollection;
use SplFileInfo;

/**
 * @see \Rector\Behastan\Tests\DefinitionMasksExtractor\DefinitionMasksExtractorTest
 */
final readonly class DefinitionMasksExtractor
{
    public function __construct(
        private SimplePhpParser $simplePhpParser,
        private NodeFinder $nodeFinder,
        private ClassMethodMasksResolver $classMethodMasksResolver,
    ) {
    }

    /**
     * @param SplFileInfo[] $contextFiles
     */
    public function extract(array $contextFiles): MaskCollection
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
                    $classMethodContextDefinition->getMethodLine(),
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
                    $classMethodContextDefinition->getMethodLine(),
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
                    $classMethodContextDefinition->getMethodLine(),
                    $classMethodContextDefinition->getClass(),
                    $classMethodContextDefinition->getMethodName()
                );
                continue;
            }

            // remove \/ escape from mask
            $rawMask = str_replace('\/', '/', $rawMask);

            $masks[] = new ExactMask(
                $rawMask,
                $classMethodContextDefinition->getFilePath(),
                $classMethodContextDefinition->getMethodLine(),
                $classMethodContextDefinition->getClass(),
                $classMethodContextDefinition->getMethodName()
            );
        }

        return new MaskCollection($masks);
    }

    /**
     * @param SplFileInfo[] $fileInfos
     * @return ContextDefinition[]
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
            if ($class->isAnonymous()) {
                continue;
            }

            if (! $class->namespacedName instanceof Name) {
                continue;
            }

            $className = $class->namespacedName->toString();

            foreach ($class->getMethods() as $classMethod) {
                $rawMasks = $this->classMethodMasksResolver->resolve($classMethod);

                foreach ($rawMasks as $rawMask) {
                    $classMethodContextDefinitions[] = new ContextDefinition(
                        $fileInfo->getRealPath(),
                        $className,
                        $classMethod->name->toString(),
                        $rawMask,
                        $classMethod->getStartLine()
                    );
                }
            }
        }

        return $classMethodContextDefinitions;
    }
}
