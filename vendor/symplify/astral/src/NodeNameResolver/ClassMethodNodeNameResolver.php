<?php

declare (strict_types=1);
namespace EasyCI20220613\Symplify\Astral\NodeNameResolver;

use EasyCI20220613\PhpParser\Node;
use EasyCI20220613\PhpParser\Node\Stmt\ClassMethod;
use EasyCI20220613\Symplify\Astral\Contract\NodeNameResolverInterface;
final class ClassMethodNodeNameResolver implements NodeNameResolverInterface
{
    public function match(Node $node) : bool
    {
        return $node instanceof ClassMethod;
    }
    /**
     * @param ClassMethod $node
     */
    public function resolve(Node $node) : ?string
    {
        return $node->name->toString();
    }
}
