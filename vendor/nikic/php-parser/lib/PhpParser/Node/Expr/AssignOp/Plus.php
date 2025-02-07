<?php

declare (strict_types=1);
namespace Behastan202502\PhpParser\Node\Expr\AssignOp;

use Behastan202502\PhpParser\Node\Expr\AssignOp;
class Plus extends AssignOp
{
    public function getType(): string
    {
        return 'Expr_AssignOp_Plus';
    }
}
