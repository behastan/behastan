<?php

declare (strict_types=1);
namespace Behastan202511\PhpParser\Node\Expr\BinaryOp;

use Behastan202511\PhpParser\Node\Expr\BinaryOp;
class LogicalXor extends BinaryOp
{
    public function getOperatorSigil(): string
    {
        return 'xor';
    }
    public function getType(): string
    {
        return 'Expr_BinaryOp_LogicalXor';
    }
}
