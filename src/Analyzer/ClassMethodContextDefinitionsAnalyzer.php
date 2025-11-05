<?php

declare (strict_types=1);
namespace Behastan\Analyzer;

use Behastan\PhpParser\SimplePhpParser;
use Behastan\Resolver\ClassMethodMasksResolver;
use Behastan\ValueObject\ClassMethodContextDefinition;
use Behastan202511\PhpParser\Node\Name;
use Behastan202511\PhpParser\Node\Stmt\Class_;
use Behastan202511\PhpParser\Node\Stmt\ClassMethod;
use Behastan202511\PhpParser\NodeFinder;
use Behastan202511\PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\SplFileInfo;
final class ClassMethodContextDefinitionsAnalyzer
{
    /**
     * @readonly
     * @var \Behastan\PhpParser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @readonly
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $printerStandard;
    /**
     * @readonly
     * @var \Behastan\Resolver\ClassMethodMasksResolver
     */
    private $classMethodMasksResolver;
    public function __construct(SimplePhpParser $simplePhpParser, NodeFinder $nodeFinder, Standard $printerStandard, ClassMethodMasksResolver $classMethodMasksResolver)
    {
        $this->simplePhpParser = $simplePhpParser;
        $this->nodeFinder = $nodeFinder;
        $this->printerStandard = $printerStandard;
        $this->classMethodMasksResolver = $classMethodMasksResolver;
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
            $classMethodContextDefinitions = array_merge($classMethodContextDefinitions, $classMethodContextDefinition);
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
            if (!$class instanceof Class_) {
                continue;
            }
            if (!$class->namespacedName instanceof Name) {
                continue;
            }
            $className = $class->namespacedName->toString();
            foreach ($class->getMethods() as $classMethod) {
                if (!$classMethod->isPublic()) {
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
