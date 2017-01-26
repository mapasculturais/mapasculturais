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
        
        
        
        <a class="btn btn-default add" title="" href="<?php echo $app->createUrl('project', 'exportFields', [$entity->id]);?>"><?php \MapasCulturais\i::_e("Exportar formulário");?></a>
        
        <!--
        <a class="btn btn-default add" title="" ng-click="showImportFields = !showImportFields"><?php \MapasCulturais\i::_e("Importar Campos");?></a>
        -->
        
        <div > <!-- ng-show="showImportFields" -->
            <?php \MapasCulturais\i::_e("Importar: Selecione o arquivo com os campos exportados de outro formulário.");?>
            <form name="impotFields" action="<?php echo $app->createUrl('project', 'importFields', [$entity->id]);?>" method="POST" enctype="multipart/form-data">
                <input type="file" name="fieldsFile" />
                <input type="submit" value="<?php echo \MapasCulturais\i::esc_attr_e('Enviar campos');?>" />
            </form>
        </div>
        

    <?php endif; ?>

    <?php $this->part('singles/project-registrations--form', ['entity' => $entity]) ?>
</div>
<!--#inscricoes-->
