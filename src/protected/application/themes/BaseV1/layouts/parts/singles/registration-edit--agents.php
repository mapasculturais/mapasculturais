<div class="registration-fieldset">
    <h4><?php \MapasCulturais\i::_e("Agentes (proponentes)");?></h4>
    <p class="registration-help"><?php \MapasCulturais\i::_e("Relacione os agentes responsáveis pela inscrição.");?></p>
    <!-- agentes relacionados a inscricao -->
    <ul class="registration-list">
        <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
        <li ng-repeat="def in data.entity.registrationAgents" ng-if="def.use != 'dontUse'" class="registration-list-item registration-edit-mode">
            <div class="registration-label">{{def.label}} <span ng-if="def.use === 'required'" class="required">*</span></div>
            <div class="registration-description">{{def.description}}</div>

            <div id="registration-agent-{{def.agentRelationGroupName}}" class="js-registration-agent registration-agent" ng-class="{pending: def.relationStatus < 0}">
                <p ng-if="def.relationStatus < 0" class="alert warning" style="display:block !important /* está oculto no scss */" ><?php \MapasCulturais\i::_e("Aguardando confirmação");?></p>
                <div class="clearfix">
                    <img ng-src="{{def.agent.avatarUrl || data.assets.avatarAgent}}" class="registration-agent-avatar" />
                    <div>
                        <a ng-if="def.agent" href="{{def.agent.singleUrl}}">{{def.agent.name}}</a>
                        <span ng-if="!def.agent"><?php \MapasCulturais\i::_e("Não informado");?></span>
                    </div>
                </div>
            </div>

            <div ng-if="data.isEditable" class="btn-group">
                <span ng-if="def.agent">
                    <a class="btn btn-default edit hltip" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="<?php \MapasCulturais\i::esc_attr_e("Editar");?> {{def.label}}"><?php \MapasCulturais\i::_e("Trocar agente");?></a>
                    <a class="btn btn-default delete hltip" ng-if="def.agentRelationGroupName != 'owner' && def.use != 'required'" ng-click="unsetRegistrationAgent(def.agent.id, def.agentRelationGroupName)" title="<?php \MapasCulturais\i::esc_attr_e("Excluir");?> {{def.label}}"<?php \MapasCulturais\i::esc_attr_e("Excluir");?></a>
                </span>
                <a class="btn btn-default add hltip" ng-if="!def.agent" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar");?> {{def.label}}"><?php \MapasCulturais\i::_e("Adicionar");?></a>
            </div>

            <edit-box id="editbox-select-registration-{{def.agentRelationGroupName}}" position="left" title="<?php \MapasCulturais\i::esc_attr_e("Selecionar");?> {{def.label}}" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                <!-- <p ng-if='def.agentRelationGroupName != "owner"'><label><input type="checkbox"> Permitir que este agente também edite essa inscrição.</label></p> -->
                <find-entity id='find-entity-registration-{{def.agentRelationGroupName}}' name='{{def.agentRelationGroupName}}' api-query="data.relationApiQuery[def.agentRelationGroupName]" entity="agent" no-results-text="<?php \MapasCulturais\i::esc_attr_e("Nenhum agente encontrado");?>" select="setRegistrationAgent" spinner-condition="data.registrationSpinner"></find-entity>
            </edit-box>
        </li>
    </ul>
</div>