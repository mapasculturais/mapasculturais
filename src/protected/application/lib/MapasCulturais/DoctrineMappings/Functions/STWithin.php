<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

class STWithin extends AbstractSpatialDQLFunction
{
    protected $platforms = ['postgresql'];

    protected $functionName = 'ST_Within';

    protected $minGeomExpr = 1;

    protected $maxGeomExpr = 3;
}