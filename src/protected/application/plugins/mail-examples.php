<?php
use MapasCulturais\Entities as e;
$app = MapasCulturais\App::i();


$app->hook('entity(<<Agent|Space|Event|Project>>).insert:after',function() use($app){
    $app->createAndSendMailMessage([
        'from' => $app->config['plugins.mailExamples.from'],
        'to' => $app->config['plugins.mailExamples.to'],
        'subject' => "Novo $this->entityType criado",
        'body' => "Um $this->entityType de nome $this->name foi criado pelo usuário {$app->user->profile->name}"
    ]);
});