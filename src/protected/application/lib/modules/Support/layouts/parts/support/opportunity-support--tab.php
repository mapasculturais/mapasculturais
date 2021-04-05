<?php
$anchor = null;
$tab = null;
?>
<?php $this->applyTemplateHook('opportunity-support--tab', 'before'); ?>
<?php $this->applyTemplateHook('opportunity-support--tab', 'begin'); ?>
    <?php
        if($this->isEditable()) {
            $anchor = "support-settings";
            $tab = \MapasCulturais\i::__("Configuração do Suporte");
        } else if($module->isSupportUser($entity, $user)){
            $anchor = "support";
            $tab = \MapasCulturais\i::__("Suporte");
        }
    ?>
    
    <?php if($anchor && $tab){ ?>
        <li><a href="#<?=$anchor?>" rel='noopener noreferrer'><?= $tab ?></a></li>
    <?php } ?>

<?php $this->applyTemplateHook('opportunity-support--tab', 'end'); ?>
<?php $this->applyTemplateHook('opportunity-support--tab', 'after'); ?>