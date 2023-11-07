<?php

$queryParams =  [
    '@permissions' => 'evaluateRegistrations',
    '@select' => 'name,parent.name,status,evaluationMethodConfiguration.{evaluationFrom,evaluationTo}', 
    'status' => 'IN(1,-1)',
];

$queryOpportunities = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Opportunity::class, $queryParams);

$this->jsObject['panelEvaluationsTabs']['evaluations'] = $queryOpportunities->getFindResult();

/* 
    http://localhost/api/opportunity/find?
    @select=name,parent.name,evaluationMethodConfiguration.{evaluationFrom,evaluationTo}&
    @permissions=evaluateRegistrations&
    status=IN(1,-1) 
*/