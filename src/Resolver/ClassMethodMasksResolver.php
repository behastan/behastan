<?php

declare(strict_types=1);

namespace Behastan\Resolver;

use PhpParser\Comment\Doc;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Webmozart\Assert\Assert;

final class ClassMethodMasksResolver
{
    /**
     * @var string
     */
    private const INSTRUCTION_DOCBLOCK_REGEX = '#\@(Given|Then|When)\s+(?<instruction>.*?)\n#m';

    /**
     * @var string[]
     */
    private const ATTRIBUTE_NAMES = ['Behat\Step\Then', 'Behat\Step\Given', 'Behat\Step\And'];

    /**
     * @return string[]
     */
    public function resolve(ClassMethod $classMethod): array
    {
        $rawMasks = [];

        // 1. collect from docblock
        if ($classMethod->getDocComment() instanceof Doc) {
            preg_match_all(self::INSTRUCTION_DOCBLOCK_REGEX, $classMethod->getDocComment()->getText(), $match);

            foreach ($match['instruction'] as $instruction) {
                $rawMasks[] = $this->clearMask($instruction);
            }
        }

        // 2. collect from attributes
        foreach ($classMethod->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attributeName = $attr->name->toString();
                if (! in_array($attributeName, self::ATTRIBUTE_NAMES)) {
                    continue;
                }

                $firstArgValue = $attr->args[0]->value;
                if (! $firstArgValue instanceof String_) {
                    continue;
                }

                $rawMasks[] = $firstArgValue->value;
            }
        }

        return $rawMasks;
    }

    private function clearMask(string $mask): string
    {
        $mask = trim($mask);

        // clear extra quote escaping that would cause miss-match with feature masks
        $mask = str_replace('\\\'', "'", $mask);
        return str_replace('\\/', '/', $mask);
    }
}
