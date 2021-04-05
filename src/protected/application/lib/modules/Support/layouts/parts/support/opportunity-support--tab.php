<?php $this->applyTemplateHook('opportunity-support--tab', 'before'); ?>
<?php $this->applyTemplateHook('opportunity-support--tab', 'begin'); ?>
    
    <?php
        if($this->isEditable()) {
            $tab = \MapasCulturais\i::__("Configuração do Suporte");
        } else {
            $tab = \MapasCulturais\i::__("Suporte");
        }
    ?>
    
    <li><a href="#support" rel='noopener noreferrer'><?= $tab ?></a></li>

<?php $this->applyTemplateHook('opportunity-support--tab', 'end'); ?>
<?php $this->applyTemplateHook('opportunity-support--tab', 'after'); ?>