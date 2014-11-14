<?php
$avatar_url = $agent && $agent->avatar ? $agent->avatar->transform('avatarSmall')->url : $this->asset('img/avatar.png', false);
?>
<div class="registration-fieldset">
    <h4><?php echo $label ?> <?php if ($required): ?>*<?php endif; ?></h4>
    <p class="registration-help"><?php echo $description ?></p>
    
    <div id="registration-agent-<?php echo $name ?>" class="js-registration-agent registration-agent <?php if($status < 0): ?>pending<?php endif; ?>">
        <p class="alert warning">Aguardando confirmação</p>
        <div class="clearfix">
            <img src="<?php echo $avatar_url ?>" class="registration-agent-avatar js-registration-agent-avatar" />
            <div class="<?php if($this->isEditable()): ?>mc-editable<?php endif;?> js-registration-agent-name" ng-click="openEditBox('editbox-select-registration-<?php echo $name ?>', $event)"><?php echo $agent ? $agent->name : 'Selecione' ?></div>
        </div>
    </div>
    <?php if($this->isEditable()): ?>
        <div class="textright">
            <a href="#" class="botao simples hltip" title="permitir que este agente também edite essa inscrição">permitir editar</a> <a href="#" class="botao excluir hltip" title="excluir este agente">excluir</a>
        </div>
    <?php endif;?>
    <edit-box id="editbox-select-registration-<?php echo $name ?>" position="right" title="Selecione o agente <?php echo $label ?>." cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
        <find-entity id='find-entity-registration-<?php echo $name ?>' name='<?php echo $name ?>' type="<?php echo $type ?>" entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationAgent" api-query='data.apiQueryRegistrationAgent' spinner-condition="data.registrationSpinner"></find-entity>
    </edit-box>
</div>
