<?php if ($entity->isRegistrationOpen()): ?>
    <?php if ($app->auth->isUserAuthenticated()): ?>
        <form id="opportunity-registration" class="registration-form clearfix">
            <p class="registration-help"><?php \MapasCulturais\i::_e("Para iniciar sua inscrição, selecione o agente responsável. Ele deve ser um agente individual (pessoa física), com um CPF válido preenchido.");?></p>
            <div>
                <div id="select-registration-owner-button" class="input-text" ng-click="editbox.open('editbox-select-registration-owner', $event)">{{data.registration.owner ? data.registration.owner.name : data.registration.owner_default_label}}</div>
                <edit-box id="editbox-select-registration-owner" position="top" title="<?php \MapasCulturais\i::esc_attr_e("Selecione o agente responsável pela inscrição.");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                    <find-entity id='find-entity-registration-owner' entity="agent" no-results-text="<?php \MapasCulturais\i::esc_attr_e("Nenhum agente encontrado");?>" select="setRegistrationOwner" api-query='data.relationApiQuery.owner' spinner-condition="data.registrationSpinner"></find-entity>
                    <strong><?php \MapasCulturais\i::_e("Apenas são visíveis os agentes publicados.");?> <a target="_blank" href="<?php echo $app->createUrl('panel', 'agents') ?>"><?php \MapasCulturais\i::_e("Ver mais.");?></a></strong>
                </edit-box>
            </div>
            <div>
                <a class="btn btn-primary" ng-click="register()" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Fazer inscrição");?></a>
            </div>
        </form>
    <?php else: ?>
        <p><?php \MapasCulturais\i::_e("Para se inscrever é preciso ter uma conta e estar logado nesta plataforma. Clique no botão abaixo para criar uma conta ou fazer login.");?></p>
        <a class="btn btn-primary" ng-click="setRedirectUrl()" <?php echo $this->getLoginLinkAttributes() ?>>
            <?php \MapasCulturais\i::_e("Entrar");?>
        </a>
    <?php endif; ?>
<?php endif; ?>
