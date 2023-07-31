<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;
use Doctrine\ORM\Query\SqlWalker;

class STEnvelope extends AbstractSpatialDQLFunction
{
    protected $platforms = ['postgresql'];

    protected $functionName = 'ST_Envelope';

    protected $minGeomExpr = 1;

    protected $maxGeomExpr = 1;

    public function getSql(SqlWalker $sqlWalker) {
        $sql = parent::getSql($sqlWalker);
        return "{$sql}::geometry";
    }
    
}

