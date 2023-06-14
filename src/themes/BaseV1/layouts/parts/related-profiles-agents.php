<?php
if($this->controller->action === 'create' || !$this->isEditable())
    return;

$this->addSubsiteAdminsToJs($entity);
?>
<div class="agentes-relacionados" ng-controller="SubsiteAdminsController">
    <div class="widget">
        <h3><?php \MapasCulturais\i::_e('Super Administradores'); ?></h3>
        <div class="agentes clearfix">
            <div class="avatar" ng-repeat="admin in superAdmins">
                <img ng-src="{{avatarUrl(admin.profile)}}"  style="width:48px; height:48px"/>

                <div class="descricao-do-agente">
                    <h1><a href="{{admin.profile.singleUrl}}" rel='noopener noreferrer'>{{admin.profile.name}}</a></h1>
                    <div class="objeto-meta">
                        <div ng-if="admin.profile.terms.area">
                            <span class="label"><?php echo strtolower($this->dict('taxonomies:area: name', true)) ?>:</span>
                            <span ng-repeat="area in admin.profile.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                        </div>
                        <div><span class="label"><?php \MapasCulturais\i::_e('tipo:'); ?></span> {{admin.profile.type.name}}</div>
                    </div>
                    <div>
                        <a href="#" class="btn btn-danger delete" ng-click="deleteSuperAdmin(admin)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Excluir'); ?></a>
                    </div>
                </div>
            </div>
            <div ng-click="editbox.open('add-super-admin', $event)" class="hltip editable editable-empty" title="<?php \MapasCulturais\i::esc_attr_e('Adicionar Super Administrador'); ?>"></div>

            <edit-box id="add-super-admin" position="left" title="Adicionar agente relacionado" cancel-label="Cancelar" close-on-cancel='true'>
                <find-entity entity="agent" no-results-text="Nenhum agente encontrado" description="" spinner-condition="false" select="createSuperAdminRole"></find-entity>
            </edit-box>
        </div>
    </div>
    <div class="widget">
        <h3><?php \MapasCulturais\i::_e('Administradores'); ?></h3>
        <div class="agentes clearfix">
            <div class="avatar" ng-repeat="admin in admins">
                <img ng-src="{{avatarUrl(admin.profile)}}" style="width:48px; height:48px"/>

                <div class="descricao-do-agente">
                    <h1><a href="{{admin.profile.singleUrl}}" rel='noopener noreferrer'>{{admin.profile.name}}</a></h1>
                    <div class="objeto-meta">
                        <div ng-if="admin.profile.terms.area">
                            <span class="label"><?php echo strtolower($this->dict('taxonomies:area: name', true)) ?>:</span>
                            <span ng-repeat="area in admin.profile.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                        </div>
                        <div><span class="label"><?php \MapasCulturais\i::_e('tipo:'); ?></span> {{admin.profile.type.name}}</div>
                    </div>
                    <div>
                        <a href="#" class="btn btn-danger delete" ng-click="deleteAdmin(admin)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Excluir'); ?></a>
                    </div>
                </div>
            </div>
            <div ng-click="editbox.open('add-admin', $event)" class="hltip editable editable-empty" title="<?php \MapasCulturais\i::esc_attr_e('Adicionar Administrador'); ?>"></div>

            <edit-box id="add-admin" position="left" title="Adicionar agente relacionado" cancel-label="Cancelar" close-on-cancel='true'>
                <find-entity entity="agent" no-results-text="Nenhum agente encontrado" description="" spinner-condition="false" select="createAdminRole"></find-entity>
            </edit-box>
        </div>
    </div>
</div>
