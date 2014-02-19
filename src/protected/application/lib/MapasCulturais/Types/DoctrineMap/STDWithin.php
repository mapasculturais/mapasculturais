<?php

namespace MapasCulturais\Types\DoctrineMap;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

class STDWithin extends AbstractSpatialDQLFunction
{
    protected $platforms = array('postgresql');

    protected $functionName = 'ST_DWithin';

    protected $minGeomExpr = 3;

    protected $maxGeomExpr = 3;
}
