<?php

namespace MapasCulturais\DoctrineMappings\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

class STDWithin extends AbstractSpatialDQLFunction
{
    protected $platforms = ['postgresql'];

    protected $functionName = 'ST_DWithin';

    protected $minGeomExpr = 3;

    protected $maxGeomExpr = 3;
}
