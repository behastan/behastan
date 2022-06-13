<?php

declare (strict_types=1);
namespace EasyCI20220613\PhpParser\Node\Expr\AssignOp;

use EasyCI20220613\PhpParser\Node\Expr\AssignOp;
class BitwiseOr extends AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_BitwiseOr';
    }
}
