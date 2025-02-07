<?php

declare (strict_types=1);
namespace Behastan\PhpParser;

use Behastan202502\PhpParser\Node\Stmt;
use Behastan202502\PhpParser\NodeTraverser;
use Behastan202502\PhpParser\NodeVisitor\NameResolver;
use Behastan202502\PhpParser\Parser;
use Behastan202502\PhpParser\ParserFactory;
use Behastan202502\Webmozart\Assert\Assert;
final class SimplePhpParser
{
    /**
     * @var \PhpParser\Parser
     */
    private $phpParser;
    public function __construct()
    {
        $this->phpParser = (new ParserFactory())->createForHostVersion();
    }
    /**
     * @return Stmt[]
     */
    public function parseFilePath(string $filePath): array
    {
        Assert::fileExists($filePath);
        $fileContents = file_get_contents($filePath);
        Assert::string($fileContents);
        $stmts = $this->phpParser->parse($fileContents);
        Assert::isArray($stmts);
        $nameNodeTraverser = new NodeTraverser();
        $nameNodeTraverser->addVisitor(new NameResolver());
        $nameNodeTraverser->traverse($stmts);
        return $stmts;
    }
}
