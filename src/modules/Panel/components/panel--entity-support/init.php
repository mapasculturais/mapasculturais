<?php
$queryParams =  [
    '@select' => 'id,name,parent.{name,status},status,files.avatar,registrationFrom,registrationTo,EvaluationMethodConfiguration', 
    '@permissions' => 'support',
    'status' => 'IN(1, -1)',
];
$queryOpportunities = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Opportunity::class, $queryParams);

$this->jsObject['entitySupports'] = $queryOpportunities->getFindResult();
