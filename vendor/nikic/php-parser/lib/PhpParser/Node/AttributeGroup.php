<?php

declare (strict_types=1);
namespace Behastan202502\PhpParser\Node;

use Behastan202502\PhpParser\NodeAbstract;
class AttributeGroup extends NodeAbstract
{
    /** @var Attribute[] Attributes */
    public $attrs;
    /**
     * @param Attribute[] $attrs PHP attributes
     * @param array<string, mixed> $attributes Additional node attributes
     */
    public function __construct(array $attrs, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->attrs = $attrs;
    }
    public function getSubNodeNames(): array
    {
        return ['attrs'];
    }
    public function getType(): string
    {
        return 'AttributeGroup';
    }
}
