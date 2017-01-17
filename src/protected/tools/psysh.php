<?php
require __DIR__ . '/../application/bootstrap.php';

function login($user_id){
    $app = MapasCulturais\App::i();
    $app->auth->login($user_id);
}

function api($entity, $params){
    return new MapasCulturais\ApiQuery("MapasCulturais\Entities\\$entity", $params);
}

$em = $app->em;

echo "
================================
VARIÁVEIS DISPONÍVEIS: 
  \$app, \$em
  
para logar: login(id do usuário);

para criar uma ApiQuery: api(\$entity, \$params); (exemplo: api('agent', ['@select' => 'id,name'])

";

eval(\psy\sh());
