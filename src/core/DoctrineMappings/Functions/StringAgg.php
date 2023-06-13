<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class StringAgg extends FunctionNode
{
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'string_agg(' . $this->expression->dispatch($sqlWalker) . ',' . $this->delimiter->dispatch($sqlWalker) .')';
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->delimiter = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}