<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Função Doctrine para calcular ocorrências de eventos recorrentes
 * 
 * Esta classe implementa a função SQL `recurring_event_occurrence_for`
 * para uso em consultas DQL do Doctrine, permitindo calcular ocorrências
 * de eventos recorrentes dentro de um intervalo de datas.
 * 
 * @package MapasCulturais\DoctrineMappings\Functions
 */
class RecurringEventOcurrenceFor extends FunctionNode {

    /**
     * @var mixed Data de início do intervalo
     * @access public
     */
    public $dateFrom;
    
    /**
     * @var mixed Data de fim do intervalo
     * @access public
     */
    public $dateTo;

    /**
     * Analisa a sintaxe da função na consulta DQL
     * 
     * @param Parser $parser Parser do Doctrine
     * @return void
     */
    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->dateFrom = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->dateTo = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Gera a representação SQL da função
     * 
     * @param SqlWalker $sqlWalker Walker SQL do Doctrine
     * @return string Expressão SQL gerada
     */
    public function getSql(SqlWalker $sqlWalker) {
        return 'recurring_event_occurrence_for(' .
                    $this->dateFrom->dispatch($sqlWalker) . ',' .
                    $this->dateTo->dispatch($sqlWalker) . ',' .
                '\'Etc/UTC\', NULL)';
        //recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
    }

}
