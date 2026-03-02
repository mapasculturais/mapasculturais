<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Classe para mapeamento da função ST_Envelope do PostGIS no Doctrine
 * 
 * Esta função retorna o envelope (retângulo delimitador) de uma geometria
 * 
 * @package MapasCulturais\DoctrineMappings\Functions
 */
class STEnvelope extends AbstractSpatialDQLFunction
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
    protected $functionName = 'ST_Envelope';

    /**
     * Número mínimo de expressões geométricas
     * @var int
     */
    protected $minGeomExpr = 1;

    /**
     * Número máximo de expressões geométricas
     * @var int
     */
    protected $maxGeomExpr = 1;

    /**
     * Obtém o SQL da função
     * 
     * @param SqlWalker $sqlWalker Walker SQL do Doctrine
     * @return string SQL da função
     */
    public function getSql(SqlWalker $sqlWalker) {
        $sql = parent::getSql($sqlWalker);
        return "{$sql}::geometry";
    }
    
}
