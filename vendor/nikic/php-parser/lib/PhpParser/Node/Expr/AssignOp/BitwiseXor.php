<?php

declare (strict_types=1);
namespace Behastan202511\PhpParser\Node\Expr\AssignOp;

use Behastan202511\PhpParser\Node\Expr\AssignOp;
class BitwiseXor extends AssignOp
{
    public function getType(): string
    {
        return 'Expr_AssignOp_BitwiseXor';
    }
}
