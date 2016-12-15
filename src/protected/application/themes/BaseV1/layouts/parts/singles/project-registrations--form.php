<?php if ($entity->isRegistrationOpen() && !$this->isEditable() && $entity->useRegistrations): ?>
    <?php if ($app->auth->isUserAuthenticated()): ?>
        <div class="registration-fieldset hide-tablet">
            <p class="registration-help"><?php \MapasCulturais\i::_e("Não é possível realizar as inscrições online através desse dispositivo. Tente se inscrever a partir de um dispositivo com a tela maior.");?></p>
        </div>
        <form id="project-registration" class="registration-form clearfix">
            <p class="registration-help"><?php \MapasCulturais\i::_e("Para iniciar sua inscrição, selecione o agente responsável. Ele deve ser um agente individual (pessoa física), com um CPF válido preenchido.");?></p>
            <div>
                <div id="select-registration-owner-button" class="input-text" ng-click="editbox.open('editbox-select-registration-owner', $event)">{{data.registration.owner ? data.registration.owner.name : data.registration.owner_default_label}}</div>
                <edit-box id="editbox-select-registration-owner" position="bottom" title="<?php \MapasCulturais\i::esc_attr_e("Selecione o agente responsável pela inscrição.");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                    <find-entity id='find-entity-registration-owner' entity="agent" no-results-text="<?php \MapasCulturais\i::esc_attr_e("Nenhum agente encontrado");?>" select="setRegistrationOwner" api-query='data.relationApiQuery.owner' spinner-condition="data.registrationSpinner"></find-entity>
                </edit-box>
            </div>
            <div>
                <a class="btn btn-primary" ng-click="register()"><?php \MapasCulturais\i::_e("Fazer inscrição");?></a>
            </div>
        </form>
    <?php else: ?>
        <p><?php \MapasCulturais\i::_e("Para se inscrever é preciso ter uma conta e estar logado nesta plataforma. Clique no botão abaixo para criar uma conta ou fazer login.");?></p>
        <a class="btn btn-primary" href="<?php echo $app->createUrl('auth', 'login') ?>?redirectTo=<?php echo $entity->singleUrl, urlencode("#tab=inscricoes") ?>"><?php \MapasCulturais\i::_e("Entrar");?></a>
    <?php endif; ?>
<?php endif; ?>
