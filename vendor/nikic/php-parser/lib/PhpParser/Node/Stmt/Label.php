<?php

declare (strict_types=1);
namespace EasyCI20220305\PhpParser\Node\Stmt;

use EasyCI20220305\PhpParser\Node\Identifier;
use EasyCI20220305\PhpParser\Node\Stmt;
class Label extends \EasyCI20220305\PhpParser\Node\Stmt
{
    /** @var Identifier Name */
    public $name;
    /**
     * Constructs a label node.
     *
     * @param string|Identifier $name       Name
     * @param array             $attributes Additional attributes
     */
    public function __construct($name, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = \is_string($name) ? new \EasyCI20220305\PhpParser\Node\Identifier($name) : $name;
    }
    public function getSubNodeNames() : array
    {
        return ['name'];
    }
    public function getType() : string
    {
        return 'Stmt_Label';
    }
}
