<?php
use MapasCulturais\Entities as e;
$app = MapasCulturais\App::i();

$app->hook('GET(site.index):before',function() use($app){
    $app->createAndSendMailMessage([
        'to' => ['rafael.chaves.freitas@gmail.com' => 'Rafael Chaves Freitas'],
        'from' => ['rafael@chaves' => 'Rafa'],
        'subject' => "Teste de envio - home",
        'body' => "Acesso a home do mc"
    ]);
});
//
//$app->hook('entity(<<Agent|Space|Event|Project>>).insert:after',function() use($app){
//    $app->createAndSendMailMessage([
//        'to' => ['rafael.chaves.freitas@gmail.com' => 'Rafael Chaves Freitas'],
//        'from' => ['rafael@hacklab.com.br' => 'Rafa'],
//        'subject' => "Novo $this->entityType criado",
//        'body' => "Um $this->entityType de nome $this->name foi criado pelo usuário {$app->user->profile->name}"
//    ]);
//});