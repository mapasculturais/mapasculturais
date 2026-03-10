<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Classe para mapeamento da função unaccent do PostgreSQL no Doctrine
 * 
 * Esta função remove acentos de strings para buscas case-insensitive
 * 
 * @package MapasCulturais\DoctrineMappings\Functions
 */
class Unaccent extends FunctionNode {

    /**
     * String a ser processada
     * @var mixed
     */
    public $string;

    /**
     * Analisa a expressão da função
     * 
     * @param Parser $parser Parser do Doctrine
     * @return void
     */
    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->string = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Obtém o SQL da função
     * 
     * @param SqlWalker $sqlWalker Walker SQL do Doctrine
     * @return string SQL da função
     */
    public function getSql(SqlWalker $sqlWalker) {
        return 'unaccent(' . $this->string->dispatch($sqlWalker) . ')';
    }

}
