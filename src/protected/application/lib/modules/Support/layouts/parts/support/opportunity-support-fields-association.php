<?php

use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'before'); ?>
<div class="fields-association-modal">
    <?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'begin'); ?>

    <header>
        <h3><?php i::_e("Selecione os campos que o agente poderá manipular"); ?></h3>
        <a ng-click="data.openModal = false" class="icon icon-close" rel="noopener noreferrer"></a>
    </header>

    <div class="modal-body">
        <ul>
            <li ng-repeat="(key,field) in data.fields">
                <label>
                    <span class="field-title">{{field.title}}</span>
                    <select ng-change="savePermission(field.id)" ng-model="data.userPermissions[field.fieldName]">
                        <option value="">Sem permissão</option>
                        <option value="ro">Visualizar</option>
                        <option value="rw">Modificar</option>
                    </select>
                </label>
            </li>
        </ul>

    </div>
    <footer></footer>

    <?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'end'); ?>
</div>
<?php $this->applyTemplateHook('opportunity-support-fields-association-modal', 'after'); ?>