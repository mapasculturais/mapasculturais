<?php
use MapasCulturais\i;

$url = $app->createUrl("painel","eventos");

?>

<div class="main-content registration">
    <head>
        <div>
            <h3><?= i::_e("Erros de importação do arquivo {$filename}") ?></h3>
        </div>
    </head>
    <?php foreach ($errors as $line => $values) : ?>
        <article class="objeto">
            <h1><?= i::_e("Linha {$line}") ?></h1>
            <?php foreach ($values as $error) : ?>
                <div><?= $error ?></div>
            <?php endforeach ?>
        </article>
    <?php endforeach ?>

    <div>
        <a href="<?=$url."#tab=event-importer"?>" class="btn btn-danger"><?= i::_e('Voltar')?></a>
    </div>
</div>