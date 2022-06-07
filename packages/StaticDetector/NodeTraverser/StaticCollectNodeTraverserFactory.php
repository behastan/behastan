<?php

declare (strict_types=1);
namespace Symplify\EasyCI\StaticDetector\NodeTraverser;

use EasyCI20220607\PhpParser\NodeVisitor\NameResolver;
use Symplify\EasyCI\StaticDetector\NodeVisitor\FilePathNodeVisitor;
use Symplify\EasyCI\StaticDetector\NodeVisitor\StaticCollectNodeVisitor;
final class StaticCollectNodeTraverserFactory
{
    /**
     * @var \Symplify\EasyCI\StaticDetector\NodeVisitor\StaticCollectNodeVisitor
     */
    private $staticCollectNodeVisitor;
    /**
     * @var \Symplify\EasyCI\StaticDetector\NodeVisitor\FilePathNodeVisitor
     */
    private $filePathNodeVisitor;
    public function __construct(StaticCollectNodeVisitor $staticCollectNodeVisitor, FilePathNodeVisitor $filePathNodeVisitor)
    {
        $this->staticCollectNodeVisitor = $staticCollectNodeVisitor;
        $this->filePathNodeVisitor = $filePathNodeVisitor;
    }
    public function create() : \Symplify\EasyCI\StaticDetector\NodeTraverser\StaticCollectNodeTraverser
    {
        $staticCollectNodeTraverser = new \Symplify\EasyCI\StaticDetector\NodeTraverser\StaticCollectNodeTraverser();
        $staticCollectNodeTraverser->addVisitor(new NameResolver());
        $staticCollectNodeTraverser->addVisitor($this->staticCollectNodeVisitor);
        $staticCollectNodeTraverser->addVisitor($this->filePathNodeVisitor);
        return $staticCollectNodeTraverser;
    }
}
