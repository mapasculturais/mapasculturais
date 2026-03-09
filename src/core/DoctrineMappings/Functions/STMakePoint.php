<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

/**
 * Classe para mapeamento da função ST_MakePoint do PostGIS no Doctrine
 * 
 * Esta função cria um ponto a partir de coordenadas X e Y
 * 
 * @package MapasCulturais\DoctrineMappings\Functions
 */
class STMakePoint extends AbstractSpatialDQLFunction
{
    /**
     * Plataformas de banco de dados suportadas
     * @var array
     */
    protected $platforms = ['postgresql'];

    /**
     * Nome da função no banco de dados
     * @var string
     */
    protected $functionName = 'ST_MakePoint';

    /**
     * Número mínimo de expressões geométricas
     * @var int
     */
    protected $minGeomExpr = 2;

    /**
     * Número máximo de expressões geométricas
     * @var int
     */
    protected $maxGeomExpr = 2;
}
