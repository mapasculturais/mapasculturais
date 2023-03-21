<?php

use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'before'); ?>
<!-- Modal -->
<div class="fields-association-modal">
    <?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'begin'); ?>

    <header>
        <h2 class="support-modal-title"><?php i::_e("Selecione os campos que o agente poderá manipular"); ?></h2>
        <a ng-click="data.openModal = false" class="x"><i class="fas fa-times x"></i></a>
        <div class="multiple-fields">
            <label>
                <input type="checkbox" ng-click="selectAll()" type="checkbox" id="field-{{field.id}}" ng-model="all.checked">
                <?php i::_e("Selecionar todos"); ?>
            </label>
            <select ng-change="setPermissionsOnSelected({{agentRelation.agent.id}})" ng-model="data.permission" ng-if="data.selectedExist">
                <option value=""><?php i::_e("Selecione"); ?></option>
                <option value=""><?php i::_e("Sem permissão"); ?></option>
                <option value="ro"><?php i::_e("Visualizar"); ?></option>
                <option value="rw"><?php i::_e("Modificar"); ?></option>
            </select>
        </div>
        <hr>
    </header>
    <!--Content modal -->
    <div class="modal-body">
        <ul>
            <li ng-repeat="(key,field) in data.fields" ng-class="type-{{field.fieldType}}" ng-if="field.fieldType != 'section'">
                <label>
                    <div>
                        <span class="field-selected"><input type="checkbox" ng-model="field.checked" type="checkbox" id="field-{{field.id}}" ng-click="selectedExist()"></span>
                        <code class="field-id">#{{field.id}}</code>
                        <span class="field-title">{{field.title}}</span>
                        <span class="field-type"><strong><?php i::_e('Tipo:'); ?></strong> {{field.typeDescription}}</span>
                    </div>
                    <select ng-change="savePermission({{agentRelation.agent.id}})" ng-model="data.userPermissions[field.ref]">
                        <option ng-selected="agentRelation.metadata.registrationPermissions[field.ref] === ''" value=""><?php i::_e("Sem permissão"); ?></option>
                        <option ng-selected="agentRelation.metadata.registrationPermissions[field.ref] === 'ro'" value="ro"><?php i::_e("Visualizar"); ?></option>
                        <option ng-selected="agentRelation.metadata.registrationPermissions[field.ref] === 'rw'"value="rw"><?php i::_e("Modificar"); ?></option>
                    </select>
                </label>
            </li>
        </ul>
    </div><!--Fim content modal -->
    <footer>
    </footer>

    <?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'end'); ?>
</div><!--Fim modal -->
<?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'after'); ?>