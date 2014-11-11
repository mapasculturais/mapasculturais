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
    <div clas="js-registration-agent">
        <img src="<?php echo $avatar_url ?>" ng-click="openEditBox('editbox-select-registration-<?php echo $name ?>', $event)"/>
        <h5><?php echo $agent ? $agent->name : 'Selecione' ?></h5>
        <p><?php echo $agent ? $agent->shortDescription : ''; ?></p>
    </div>

    <edit-box id="editbox-select-registration-<?php echo $name ?>" position="right" title="Selecione o agente <?php echo $label ?>." cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
        <find-entity id='find-entity-registration-<?php echo $name ?>' name='<?php echo $name ?>' entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationAgent" api-query='data.apiQueryRegistrationAgent' spinner-condition="data.registrationSpinner"></find-entity>
    </edit-box>
</div>
