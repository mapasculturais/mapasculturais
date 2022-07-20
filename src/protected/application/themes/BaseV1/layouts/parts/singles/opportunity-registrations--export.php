<div>
    <p><?php \MapasCulturais\i::_e("É possível exportar as configurações deste formulário para utilizar como modelo ao montar um novo formulário.");?> </p>

    <h5><?php \MapasCulturais\i::_e("Exportar o formulário");?></h5>

    <p>
        <?php \MapasCulturais\i::_e("Clique no botão abaixo para exportar as configurações e campos deste formulário. Salve o arquivo e o utilize para importar em outro formulário.");?>
    </p>

    <a class="btn btn-default add" title="" href="<?php echo $app->createUrl('opportunity', 'exportFields', [$entity->id]);?>">
        <?php \MapasCulturais\i::_e("Exportar formulário");?>
    </a>
</div>

<br><br>