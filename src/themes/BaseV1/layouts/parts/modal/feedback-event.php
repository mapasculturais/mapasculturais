<?php
use MapasCulturais\i;
$success = \MapasCulturais\i::esc_attr__('Entidade criada com sucesso!');
$avatar = "/img/avatar--$entity_name.png";
?>
<div class="modal-feedback-event header-content hidden">

    <div class="avatar">
        <img class="js-avatar-img" src="<?php $this->asset($avatar); ?>">
    </div>

    <div class="entity-type <?php echo $entity_name; ?>-type">
        <div class="icon icon-<?php echo $entity_name; ?>"></div>
        <a href="#" class="entity-url" rel='noopener noreferrer'>
            <?php echo \MapasCulturais\i::esc_attr__('Novo ') . $label; ?>
        </a>
    </div>

    <h2><span class="entidade"><?php echo $success; ?></span></h2>

    <div class="options" style="width: 100%; float: left;">
        <a href='javascript:void(0)' class="btn btn-default close-modal" rel='noopener noreferrer'>
            <?php \MapasCulturais\i::_e("Continuar navegando"); ?>
        </a>

        <a href='javascript:void(0)' class='view-entity btn btn-default' rel='noopener noreferrer'>
            <?php echo \MapasCulturais\i::__("Ver ") . $label; ?>
        </a>
        
        <a class="btn btn-primary js-open-dialog hltip create-occurrence" data-dialog="#dialog-event-occurrence"
            data-dialog-callback="MapasCulturais.eventOccurrenceUpdateDialog"
            data-dialog-title="<?php \MapasCulturais\i::esc_attr_e("Completar criação agora");?>"
            data-form-action='insert'
            title="<?php \MapasCulturais\i::esc_attr_e('Clique para inserir o local e data data'); ?>">
            <?php i::_e("Inserir local e data");?>
        </a>

        <a href='javascript:void(0)' class='edit-entity btn btn-primary' rel='noopener noreferrer'>
            <?php \MapasCulturais\i::_e("Completar edição agora"); ?>
        </a>
    </div>
</div>
