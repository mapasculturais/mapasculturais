<?php
use MapasCulturais\Entities as e;
$app = MapasCulturais\App::i();

// envia email quando uma entidade for criada
$app->hook('entity(<<Agent|Space|Event|Project>>).insert:after',function() use($app){
    $app->createAndSendMailMessage([
        'from' => $app->config['plugins.mailExamples.from'],
        'to' => $app->config['plugins.mailExamples.to'],
        'subject' => "Novo {$this->entityTypeLabel()} criado",
        'body' => "Um {$this->entityTypeLabel()} de nome $this->name foi criado pelo usuário {$app->user->profile->name}"
    ]);
});


// envia email a cada acesso a home do site
$app->hook('GET(site.index):before',function() use($app){
    $app->createAndSendMailMessage([
        'from' => $app->config['plugins.mailExamples.from'],
        'to' => $app->config['plugins.mailExamples.to'],
        'subject' => "Teste de envio - home",
        'body' => "Acesso a home do $app->siteName"
    ]);
});
