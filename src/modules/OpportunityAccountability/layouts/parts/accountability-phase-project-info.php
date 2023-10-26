<?php
use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('accountability-phase-project-info','before'); ?>
<div id="status-info" class="alert info">
    <?php $this->applyTemplateHook('accountability-phase-project-info','begin'); ?>
    <p><?php i::_e('Seu projeto foi contemplado! O próximo passo é mantê-lo atualizado para a fase de prestação de contas, que começa em'); ?> <?=$from?></p>

    <p><?php i::_e('Para começar:')?></p>
    <ul>
        <li><span><?php i::_e('Edite este projeto, preencha os dados solicitados e publique. Atualizações podem ser realizadas com frequência!')?> </span></li>
        <li><span> <?php i::_e('Quando chegar o momento da prestação de contas, você precisa sair do modo de edição e acessar a aba <strong>"Prestação de contas"</strong> para preencher os dados solicitados e enviá-lo')?>"</li>
    </ul>
    <p></p>
    <div class="close" style="cursor: pointer;"></div>
    <?php $this->applyTemplateHook('accountability-phase-project-info','end'); ?>
</div>
<?php $this->applyTemplateHook('accountability-phase-project-info','after'); ?>