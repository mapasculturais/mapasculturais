<?php
$avatar_url = $agent && $agent->avatar ? $agent->avatar->transform('avatarSmall')->url : $this->asset('img/avatar.png', false);
?>
<div>
    <h4><?php echo $label ?></h4>
    <p><?php echo $description ?></p>
    <?php if ($required): ?>
        Obrigatório
    <?php else: ?>
        Facultativo
    <?php endif; ?>
    <div id="registration-agent-<?php echo $name ?>" clas="js-registration-agent registration-agent <?php if($status < 0): ?>pending<?php endif; ?>">

        <!--
            div.registration-agent p.pending {display:none}
            div.registration-agent.pending p.pending {display:block}
        -->
        <p class="pending">Aguardando confirmação</p>

        <img src="<?php echo $avatar_url ?>" class="js-registration-agent-avatar" ng-click="openEditBox('editbox-select-registration-<?php echo $name ?>', $event)"/>
        <h5 class="js-registration-agent-name"><?php echo $agent ? $agent->name : 'Selecione' ?></h5>
        <p class="js-registration-agent-shortDescription"><?php echo $agent ? $agent->shortDescription : ''; ?></p>
    </div>

    <edit-box id="editbox-select-registration-<?php echo $name ?>" position="right" title="Selecione o agente <?php echo $label ?>." cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
        <find-entity id='find-entity-registration-<?php echo $name ?>' name='<?php echo $name ?>' type="<?php echo $type ?>" entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationAgent" api-query='data.apiQueryRegistrationAgent' spinner-condition="data.registrationSpinner"></find-entity>
    </edit-box>
</div>
