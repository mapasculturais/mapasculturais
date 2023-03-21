<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class RecurringEventOcurrenceFor extends FunctionNode {

    public $dateFrom;
    public $dateTo;

    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->dateFrom = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->dateTo = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker) {
        return 'recurring_event_occurrence_for(' .
                    $this->dateFrom->dispatch($sqlWalker) . ',' .
                    $this->dateTo->dispatch($sqlWalker) . ',' .
                '\'Etc/UTC\', NULL)';
        //recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
    }

}
