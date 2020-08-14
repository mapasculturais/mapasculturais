<div id="registration-attachments" class="registration-fieldset">

    <h4 class="opportunity-toggle-form" ng-click="showImpExpForm = !showImpExpForm">
        <?php \MapasCulturais\i::_e("Importar / Exportar o formulário");?>
    </h4>

    <div class="opportunity-togglable-form-content" ng-show="showImpExpForm">

        <p><?php \MapasCulturais\i::_e("É possível exportar as configurações deste formulário para utilizar como modelo ao montar um novo formulário.");?> </p>

        <h5><?php \MapasCulturais\i::_e("Exportar");?></h5>

        <p><?php \MapasCulturais\i::_e("Clique no botão abaixo para exportar as configurações e campos deste formulário. Salve o arquivo e o utilize para importar em outro formulário.");?> </p>

        <a class="btn btn-default add" title="" href="<?php echo $app->createUrl('opportunity', 'exportFields', [$entity->id]);?>"><?php \MapasCulturais\i::_e("Exportar formulário");?></a>

        <br/><br/>

        <h5><?php \MapasCulturais\i::_e("Importar");?></h5>

        <p><?php \MapasCulturais\i::_e("Selecione abaixo um arquivo exportado de outro formulário. As configurações e campos deste formulário serão substituídas pelas do arquivo importado.");?> </p>

        <!--    <a class="btn btn-default add" title="" ng-click="showImportFields = !showImportFields" rel='noopener noreferrer'>--><?php //\MapasCulturais\i::_e("Importar Campos");?><!--</a>-->

        <?php if ($entity->canUser('modifyRegistrationFields')): ?>
            <div > <!-- ng-show="showImportFields" -->
                <form name="impotFields" action="<?php echo $app->createUrl('opportunity', 'importFields', [$entity->id]);?>" method="POST" enctype="multipart/form-data">
                    <input type="file" name="fieldsFile" />
                    <input type="submit" value="<?php echo \MapasCulturais\i::esc_attr_e('Enviar campos');?>" />
                </form>
            </div>
        <?php else: ?>
            <i><?php \MapasCulturais\i::_e('Não é possível importar porque você não possui permissão para alterar os campos deste formulário'); ?></i>
        <?php endif; ?>

    </div>

</div>
