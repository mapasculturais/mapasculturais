<?php
if ($this->isEditable() || "$entity" != "$owner"):
    $avatar_url = $owner->avatar ? $owner->avatar->transform('avatarSmall')->url : $this->asset('img/avatar.png', false);
    if ($entity->isUserProfile)
        return;
    ?>
    <footer id='entity-owner' class="meta" ng-controller="ChangeOwnerController">
        <div class="dono clearfix js-owner">
            <p class="small bottom">Publicado por</p>

            <?php if ($this->isEditable() && $entity->canUser('changeOwner')): ?>
                <h4 class="js-search js-include-editable"
                    data-field-name='ownerId'
                    data-emptytext="Selecione um agente"
                    data-search-box-width="400px"
                    data-search-box-placeholder="Selecione um agente"
                    data-entity-controller="agent"
                    data-search-result-template="#agent-search-result-template"
                    data-selection-template="#agent-response-template"
                    data-no-result-template="#agent-response-no-results-template"
                    data-selection-format="changeOwner"
                    data-auto-open="true"
                    data-value="<?php echo $owner->id ?>"
                    title="Repassar propriedade"
                    ><?php echo $owner->name ?></h4>
                <?php else: ?>
                <h4 class='js-owner-name'><a href="<?php echo $app->createUrl('agent', 'single', array($owner->id)) ?>"><?php echo $owner->name ?></a></h4>
            <?php endif; ?>

            <img src="<?php echo $avatar_url; ?>" class="avatar js-owner-avatar" />

            <p class="descricao-do-agente js-owner-description"><?php echo nl2br($owner->shortDescription); ?></p>
            <div class="clearfix">
                <?php if (!$this->isEditable() && !$app->user->is('guest')): ?>
                    <a class="action staging-hidden" href="#">Reportar erro</a>
                    <?php if($entity->canUser('@control')): ?>
                        <a id="change-owner-button" class="action" ng-click="editbox.open('editbox-change-owner', $event)">Ceder propriedade</a>
                    <?php else: ?>
                        <a id="change-owner-button" class="action" ng-click="editbox.open('editbox-change-owner', $event)">Reivindicar propriedade</a>
                    <?php endif; ?>
                    <edit-box id="editbox-change-owner" position="right" title="Selecione o agente para o qual você deseja passar a propriedade deste <?php echo strtolower($entity->getEntityType()) ?>" cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.spinner">
                        <find-entity id='find-entity-change-owner' entity="agent" no-results-text="Nenhum agente encontrado" select="requestEntity" api-query='data.apiQuery' spinner-condition="data.spinner"></find-entity>
                    </edit-box>
                <?php endif; ?>
            </div>
        </div>
    </footer>
<?php endif; ?>
