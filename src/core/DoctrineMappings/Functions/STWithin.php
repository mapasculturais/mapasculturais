<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

/**
 * Função Doctrine para operação ST_Within do PostGIS
 * 
 * Esta classe implementa a função espacial ST_Within para uso
 * em consultas DQL do Doctrine, permitindo verificar se uma
 * geometria está dentro de outra geometria.
 * 
 * ST_Within retorna true se a geometria A estiver completamente
 * dentro da geometria B.
 * 
 * @package MapasCulturais\DoctrineMappings\Functions
 */
class STWithin extends AbstractSpatialDQLFunction
{
    /**
     * @var array Plataformas de banco suportadas
     * @access protected
     */
    protected $platforms = ['postgresql'];

    /**
     * @var string Nome da função no banco de dados
     * @access protected
     */
    protected $functionName = 'ST_Within';

    /**
     * @var int Número mínimo de expressões geométricas
     * @access protected
     */
    protected $minGeomExpr = 1;

    /**
     * @var int Número máximo de expressões geométricas
     * @access protected
     */
    protected $maxGeomExpr = 3;
}