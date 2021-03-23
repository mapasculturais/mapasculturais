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
    </header>
    <!--Content modal -->
    <div class="modal-body">
        <ul>
            <li ng-repeat="(key,field) in data.fields" ng-class="type-{{field.fieldType}}" ng-if="field.fieldType != 'section'">
                <label>
                    <div>
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