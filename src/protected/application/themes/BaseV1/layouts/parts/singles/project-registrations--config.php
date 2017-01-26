<div ng-if="data.projectRegistrationsEnabled" id="inscricoes" class="aba-content">
    <?php if ($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
        <p ng-if="data.isEditable" class="alert info">
            <?php \MapasCulturais\i::_e("Utilize este espaço caso queira abrir inscrições para Agentes Culturais cadastrados na plataforma.");?>
            <span class="close"></span>
        </p>
    <?php endif; ?>

    <?php $this->part('singles/project-registrations--user-registrations', ['entity' => $entity]) ?>

    <?php $this->part('singles/project-registrations--intro', ['entity' => $entity]); ?>

    <?php $this->part('singles/project-registrations--rules', ['entity' => $entity]); ?>

    <?php if ($this->isEditable()): ?>

        <?php $this->part('singles/project-registrations--categories', ['entity' => $entity]) ?>

        <?php $this->part('singles/project-registrations--agent-relations', ['entity' => $entity]) ?>
        
        <?php $this->part('singles/project-registrations--seals', ['entity' => $entity]) ?>

        <?php $this->part('singles/project-registrations--fields', ['entity' => $entity]) ?>
        
        
        <div id="registration-attachments" class="registration-fieldset">
            
            <h4><?php \MapasCulturais\i::_e("Importar / Exportar o formulário");?></h4>
            
            <p><?php \MapasCulturais\i::_e("É possível exportar as configurações deste formulário ...");?> </p>
            
            <h5><?php \MapasCulturais\i::_e("Exportar");?></h5>
            
            <p><?php \MapasCulturais\i::_e("Clique no botão abaixo para exportar as configurações e campos deste formulário. Salve o arquivo e o utilize para importar em outro formulário.");?> </p>
            
            <a class="btn btn-default add" title="" href="<?php echo $app->createUrl('project', 'exportFields', [$entity->id]);?>"><?php \MapasCulturais\i::_e("Exportar formulário");?></a>
            
            <br/><br/>            
            
            <h5><?php \MapasCulturais\i::_e("Importar");?></h5>
            
            <p><?php \MapasCulturais\i::_e("Selecione abaixo um arquivo exportado de outro formulário. As configurações e campos deste formulário serão substituídas pelas do arquivo importado.");?> </p>
            
            
            <!--
            <a class="btn btn-default add" title="" ng-click="showImportFields = !showImportFields"><?php \MapasCulturais\i::_e("Importar Campos");?></a>
            -->
            
            <div > <!-- ng-show="showImportFields" -->
                <form name="impotFields" action="<?php echo $app->createUrl('project', 'importFields', [$entity->id]);?>" method="POST" enctype="multipart/form-data">
                    <input type="file" name="fieldsFile" />
                    <input type="submit" value="<?php echo \MapasCulturais\i::esc_attr_e('Enviar campos');?>" />
                </form>
            </div>
            
        </div>
        
        
        
        

    <?php endif; ?>

    <?php $this->part('singles/project-registrations--form', ['entity' => $entity]) ?>
</div>
<!--#inscricoes-->
