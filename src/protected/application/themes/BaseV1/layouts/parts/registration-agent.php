<?php
$avatar_url = $agent && $agent->avatar ? $agent->avatar->transform('avatarSmall')->url : $this->asset('img/avatar--agent.png', false);
?>
<li class="registration-list-item">
    <div class="registration-label"><?php echo $label ?> <?php if ($required): ?>*<?php endif; ?></div>
    <div class="registration-description"><?php echo $description ?></div>

    <div id="registration-agent-<?php echo $name ?>" class="js-registration-agent registration-agent <?php if($status < 0): ?>pending<?php endif; ?>">
        <p class="alert warning">Aguardando confirmação</p>
        <div class="clearfix">
            <img src="<?php echo $avatar_url ?>" class="registration-agent-avatar js-registration-agent-avatar" />
            <div class="js-registration-agent-name"><?php echo $agent ? '<a href="'.$agent->singleUrl.'">'.$agent->name.'</a>' : 'Não informado' ?></div>
        </div>
    </div>
    <?php if($this->isEditable()): ?>
        <div class="btn-group">
            <?php if($agent): ?>
                <a class="botao editar hltip" ng-click="openEditBox('editbox-select-registration-<?php echo $name ?>', $event)" title="Editar <?php echo $label ?>">editar</a>
                <a ng-click="unsetRegistrationAgent(<?php echo $agent->id; ?>, '<?php echo $name; ?>')" class="botao excluir hltip" title="Excluir <?php echo $label ?>">excluir</a>
            <?php else: ?>
                <a class="botao adicionar hltip" ng-click="openEditBox('editbox-select-registration-<?php echo $name ?>', $event)" title="Adicionar <?php echo $label ?>">adicionar</a>
            <?php endif; ?>
        </div>
    <?php endif;?>
    <edit-box id="editbox-select-registration-<?php echo $name ?>" position="right" title="Selecionar <?php echo $label ?>" cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
        <p><label><input type="checkbox"> Permitir que este agente também edite essa inscrição.</label></p>
        <find-entity id='find-entity-registration-<?php echo $name ?>' name='<?php echo $name ?>' type="<?php echo $type ?>" entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationAgent" api-query='data.apiQueryRegistrationAgent' spinner-condition="data.registrationSpinner"></find-entity>
    </edit-box>
</li>