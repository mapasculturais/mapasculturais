<?php

namespace MapasCulturais\DoctrineMappings\Functions;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
    
class Cast extends FunctionNode
{
  /** @var \Doctrine\ORM\Query\AST\PathExpression */
  protected $first;
  /** @var string */
  protected $second;

  /**
   * @param SqlWalker $sqlWalker
   *
   * @return string
   */
  public function getSql(SqlWalker $sqlWalker)
  {
    return sprintf(
      "CAST(%s AS %s)",
      $this->first->dispatch($sqlWalker),
      $this->second
    );
  }
   
  /**
   * @param Parser $parser
   *
   * @return void
   */
  public function parse(Parser $parser)
  {
    $parser->match(Lexer::T_IDENTIFIER);
    $parser->match(Lexer::T_OPEN_PARENTHESIS);
    $this->first = $parser->ArithmeticPrimary();
    $parser->match(Lexer::T_AS);
    $parser->match(Lexer::T_IDENTIFIER);
    $this->second = $parser->getLexer()->token['value'];
    $parser->match(Lexer::T_CLOSE_PARENTHESIS);
  }
}
