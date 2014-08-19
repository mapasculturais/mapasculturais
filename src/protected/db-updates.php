<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();
return array(
    'alter table space add column public' => function() use ($conn){
        $conn->executeQuery('ALTER TABLE space ADD COLUMN public BOOLEAN NOT NULL DEFAULT false;');
    }
);
