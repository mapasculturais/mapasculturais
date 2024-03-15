<?php

/* Get agents from logged user */
$date = new DateTime();
$actual_date =  date_format($date,"Y-m-d");
$future_date = date_format($date->modify('+1 month'),"Y-m-d");

$queryParams =  [
    '@order' => 'registrationFrom ASC',
    '@select' => 'id,name,shortDescription,terms,seals,singleUrl,registrationFrom,registrationTo,files', 
    'registrationFrom' => 'LTE('.$future_date.')',
    'registrationTo' => 'GTE('.$actual_date.')',
];
$queryParams = array_merge($queryParams, (array) $app->config['home.opportunities.filter']);
$queryOpportunities = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Opportunity::class, $queryParams);

$this->jsObject['home']['opportunities'] =[
    'opportunities' => $queryOpportunities->getFindResult(),
];
