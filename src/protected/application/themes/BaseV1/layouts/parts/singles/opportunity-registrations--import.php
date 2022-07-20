<div id="registration-attachments" class="registration-fieldset">

    <div>

        <h5><?php \MapasCulturais\i::_e("Importar o formulário");?></h5>

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
