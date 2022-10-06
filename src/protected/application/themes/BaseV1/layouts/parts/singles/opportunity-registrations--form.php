<?php

use MapasCulturais\i;

if ($entity->isRegistrationOpen() && $entity->canUser('register')): ?>
    <?php if ($app->auth->isUserAuthenticated()): ?>
        <form id="opportunity-registration" class="registration-form clearfix">
            <p class="registration-help"><?php \MapasCulturais\i::_e("Para iniciar sua inscrição, selecione o agente responsável. Ele deve ser um agente individual (pessoa física), com um CPF válido preenchido.");?></p>
            <div>
                <div id="select-registration-owner-button" class="input-text" ng-click="editbox.open('editbox-select-registration-owner_<?php echo $entity->id; ?>', $event)">{{data.registration.owner ? data.registration.owner.name : data.registration.owner_default_label}}</div>
                <edit-box id="editbox-select-registration-owner_<?php echo $entity->id; ?>" position="top" title="<?php \MapasCulturais\i::esc_attr_e("Selecione o agente responsável pela inscrição.");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                    <find-entity id='find-entity-registration-owner' entity="agent" no-results-text="<?php \MapasCulturais\i::esc_attr_e("Nenhum agente encontrado");?>" select="setRegistrationOwner" opportunityid="<?php echo $entity->id; ?>" editbox-id="editbox-select-registration-owner_<?php echo $entity->id; ?>" api-query='data.relationApiQuery.owner' spinner-condition="data.registrationSpinner"></find-entity>
                    <strong><?php \MapasCulturais\i::_e("Apenas são visíveis os agentes publicados.");?> <a target="_blank" href="<?php echo $app->createUrl('panel', 'agents') ?>"><?php \MapasCulturais\i::_e("Ver mais.");?></a></strong>
                </edit-box>
            </div>
            <div>
                <a class="btn btn-primary" ng-click="register(<?php echo $entity->id; ?>)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Fazer inscrição");?></a>
            </div>
        </form>
    <?php else: ?>
        <p><?php \MapasCulturais\i::_e("Para se inscrever é preciso ter uma conta e estar logado nesta plataforma. Clique no botão abaixo para criar uma conta ou fazer login.");?></p>
        <a class="btn btn-primary" ng-click="setRedirectUrl()" <?php echo $this->getLoginLinkAttributes() ?>>
            <?php \MapasCulturais\i::_e("Entrar");?>
        </a>
    <?php endif; ?>
<?php elseif ($entity->isRegistrationOpen() && !$entity->canUser('register')): ?>
    <?php if ($app->user->is('admin')): ?>
        <p class='alert warning'><?php i::_e('Admins não podem se inscrever em oportunidades.'); ?></p>
    <?php elseif ($entity->canUser('@control')): ?>
        <p class='alert warning'><?php i::_e('Gestores da oportunidade não podem se inscrever.'); ?></p>
    <?php elseif ($entity->canUser('viewEvaluations')): ?>
        <p class='alert warning'><?php i::_e('Avaliadores da oportunidade não podem se inscrever.') ?></p>
    <?php endif; ?>
<?php endif; ?>
