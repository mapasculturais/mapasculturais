<?php
if(is_editable() || "$entity" != "$owner"):
    $avatar_url = $owner->avatar ? $owner->avatar->transform('avatarSmall')->url : "{$app->assetUrl}/img/avatar.png";
    if($entity->isUserProfile)
        return;
    ?>
    <footer class="meta">
		<div class="dono clearfix js-owner">
			<p class="small bottom">Publicado por</p>

            <?php if(is_editable() && $entity->canUser('changeOwner')): ?>
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
                <h4><a href="<?php echo $app->createUrl('agent', 'single', array($owner->id)) ?>"><?php echo $owner->name ?></a></h4>
            <?php endif; ?>

            <img src="<?php echo $avatar_url; ?>" class="avatar js-owner-avatar" />

			<p class="descricao-do-agente js-owner-description"><?php echo nl2br($owner->shortDescription); ?></p>
            <div class="clearfix staging-hidden">
                <?php if(!is_editable()): ?>
                    <a class="action" href="#">Reportar erro</a>
                    <a class="action" href="#">Reivindicar propriedade</a>
                <?php endif; ?>
			</div>
		</div>
	</footer>
<?php endif; ?>
