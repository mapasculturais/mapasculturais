<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

/**
 * Classe para mapeamento da função ST_DWithin do PostGIS no Doctrine
 * 
 * Esta função verifica se duas geometrias estão dentro de uma distância especificada
 * 
 * @package MapasCulturais\DoctrineMappings\Functions
 */
class STDWithin extends AbstractSpatialDQLFunction
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
    protected $functionName = 'ST_DWithin';

    /**
     * Número mínimo de expressões geométricas
     * @var int
     */
    protected $minGeomExpr = 3;

    /**
     * Número máximo de expressões geométricas
     * @var int
     */
    protected $maxGeomExpr = 3;
}
