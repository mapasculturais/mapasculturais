<?php
if ($this->isEditable() || "$entity" != "$owner"):
    $avatar_url = $owner->avatar ? $owner->avatar->transform('avatarSmall')->url : $this->asset('img/avatar--agent.png', false);
    if ($entity->isUserProfile)
        return;
    ?>
    <footer id='entity-owner' class="owner clearfix js-owner" ng-controller="ChangeOwnerController">
        <img src="<?php echo $avatar_url; ?>" class="avatar js-owner-avatar" />
        <p class="small bottom"><?php \MapasCulturais\i::_e("Publicado por");?></p>

        <?php if ($this->isEditable() && $entity->canUser('changeOwner')): ?>
            <h6 class="js-search js-include-editable"
                data-field-name='ownerId'
                data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione um agente");?>"
                data-search-box-width="400px"
                data-search-box-placeholder="<?php \MapasCulturais\i::esc_attr_e("Selecione um agente");?>"
                data-entity-controller="agent"
                data-search-result-template="#agent-search-result-template"
                data-selection-template="#agent-response-template"
                data-no-result-template="#agent-response-no-results-template"
                data-selection-format="changeOwner"
                data-auto-open="true"
                data-value="<?php echo $owner->id ?>"
                title="<?php \MapasCulturais\i::esc_attr_e("Repassar propriedade");?>"
                ><?php echo $owner->name ?></h6>
            <?php else: ?>
            <h6 class='js-owner-name'><a href="<?php echo $app->createUrl('agent', 'single', array($owner->id)) ?>"><?php echo $owner->name ?></a></h6>
        <?php endif; ?>



        <p class="owner-description js-owner-description"><?php echo nl2br($owner->shortDescription); ?></p>
        <div class="clearfix">
            <?php if (!$this->isEditable() && !$app->user->is('guest')): ?>
                <a class="btn btn-small btn-default staging-hidden" href="#">Reportar erro</a>
                <?php if($entity->canUser('@control')): ?>
                    <a id="change-owner-button" class="btn btn-small btn-primary" ng-click="editbox.open('editbox-change-owner', $event)"><?php \MapasCulturais\i::_e("Ceder propriedade");?></a>
                <?php else: ?>
                    <a id="change-owner-button" class="btn btn-small btn-primary" ng-click="editbox.open('editbox-change-owner', $event)"><?php \MapasCulturais\i::_e("Reivindicar propriedade");?></a>
                <?php endif; ?>
                <edit-box id="editbox-change-owner" position="right" title="<?php \MapasCulturais\i::esc_attr_e("Selecione o agente para o qual você deseja passar a propriedade deste");?> <?php echo strtolower($entity->getEntityTypeLabel()) ?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.spinner">
                    <find-entity id='find-entity-change-owner' entity="agent" no-results-text="<?php \MapasCulturais\i::esc_attr_e("Nenhum agente encontrado");?>" select="requestEntity" api-query='data.apiQuery' spinner-condition="data.spinner"></find-entity>
                </edit-box>
            <?php endif; ?>
        </div>
    </footer>
<?php endif; ?>
