<?php
if ($this->isEditable() || "$entity" != "$owner"):
    $avatar_url = $owner->avatar ? $owner->avatar->transform('avatarSmall')->url : $this->asset('img/avatar--agent.png', false);
    if ($entity->isUserProfile)
        return;
    ?>
    <footer id='entity-owner' class="owner clearfix js-owner" ng-controller="ChangeOwnerController">
        <img src="<?php echo $avatar_url; ?>" class="avatar js-owner-avatar" />
        <p class="small bottom">Publicado por</p>

        <?php if ($this->isEditable() && $entity->canUser('changeOwner')): ?>
            <h6 class="js-search js-include-editable"
                data-field-name='ownerId'
                data-emptytext="Seleccione un agente"
                data-search-box-width="400px"
                data-search-box-placeholder="Seleccione un agente"
                data-entity-controller="agent"
                data-search-result-template="#agent-search-result-template"
                data-selection-template="#agent-response-template"
                data-no-result-template="#agent-response-no-results-template"
                data-selection-format="changeOwner"
                data-auto-open="true"
                data-value="<?php echo $owner->id ?>"
                title="Pasar propriedad"
                ><?php echo $owner->name ?></h6>
            <?php else: ?>
            <h6 class='js-owner-name'><a href="<?php echo $app->createUrl('agent', 'single', array($owner->id)) ?>"><?php echo $owner->name ?></a></h6>
        <?php endif; ?>



        <p class="owner-description js-owner-description"><?php echo nl2br($owner->shortDescription); ?></p>
        <div class="clearfix">
            <?php if (!$this->isEditable() && !$app->user->is('guest')): ?>
                <a class="btn btn-small btn-default staging-hidden" href="#">Reportar error</a>
                <?php if($entity->canUser('@control')): ?>
                    <a id="change-owner-button" class="btn btn-small btn-primary" ng-click="editbox.open('editbox-change-owner', $event)">Ceder propriedad</a>
                <?php else: ?>
                    <a id="change-owner-button" class="btn btn-small btn-primary" ng-click="editbox.open('editbox-change-owner', $event)">Reivindicar propriedad</a>
                <?php endif; ?>
                <edit-box id="editbox-change-owner" position="right" title="Seleccione el agente para el cual desea pasar la propriedad de este <?php echo strtolower($entity->getEntityType()) ?>" cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.spinner">
                    <find-entity id='find-entity-change-owner' entity="agent" no-results-text="Ningún agente encontrado" select="requestEntity" api-query='data.apiQuery' spinner-condition="data.spinner"></find-entity>
                </edit-box>
            <?php endif; ?>
        </div>
    </footer>
<?php endif; ?>
