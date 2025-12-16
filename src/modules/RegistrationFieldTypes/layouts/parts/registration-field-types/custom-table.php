<?php
use MapasCulturais\i;
?>

<div ng-class="field.error ? 'invalidField': ''" ng-if="::field.fieldType == 'custom-table'" id="field_{{::field.id}}" class="custom-table-field">
    <span class="label">
        {{::field.title}}
        <div ng-if="requiredField(field)" class="field-required">
            <span class="description"><?php i::_e('obrigatório') ?></span>
            <span class="icon-required">*</span>
        </div>
    </span>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <!-- Inicialização do array de dados -->
    <div ng-init="initCustomTable(field, entity)"></div>

    <!-- Tabela -->
    <div style="overflow-x: auto; margin-top: 15px;">
        <table class="custom-table" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th ng-repeat="column in ::field.config.columns" style="border: 1px solid #ddd; padding: 10px; text-align: left;">
                        {{::column.name}}
                        <span ng-if="::column.required" style="color: red;">*</span>
                    </th>
                    <th style="border: 1px solid #ddd; padding: 10px; text-align: center; width: 100px;">
                        <?= i::__('Ações') ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="row in entity[fieldName] track by $index">
                    <td ng-repeat="column in ::field.config.columns track by $index" style="border: 1px solid #ddd; padding: 8px;">
                        
                        <!-- Campo de texto -->
                        <input ng-if="::column.type === 'text'" 
                               type="text" 
                               ng-model="row['col' + $index]"
                               ng-blur="saveField(field, entity[fieldName])"
                               ng-required="::column.required"
                               style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                        
                        <!-- Campo de número -->
                        <input ng-if="::column.type === 'number'" 
                               type="number" 
                               ng-model="row['col' + $index]"
                               ng-blur="saveField(field, entity[fieldName])"
                               ng-required="::column.required"
                               style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                        
                        <!-- Campo de e-mail -->
                        <input ng-if="::column.type === 'email'" 
                               type="email" 
                               ng-model="row['col' + $index]"
                               ng-blur="saveField(field, entity[fieldName])"
                               ng-required="::column.required"
                               style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                        
                        <!-- Campo de CPF -->
                        <input ng-if="::column.type === 'cpf'" 
                               type="text" 
                               ng-model="row['col' + $index]"
                               ng-blur="saveField(field, entity[fieldName])"
                               ng-required="::column.required"
                               js-mask="999.999.999-99" 
                               placeholder="___.___.___-__"
                               style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                        
                        <!-- Campo de data -->
                        <input ng-if="::column.type === 'date'" 
                               type="date" 
                               ng-model="row['col' + $index]"
                               ng-blur="saveField(field, entity[fieldName])"
                               ng-required="::column.required"
                               style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                        
                        <!-- Campo de seleção -->
                        <select ng-if="::column.type === 'select'" 
                                ng-model="row['col' + $index]"
                                ng-change="saveField(field, entity[fieldName])"
                                ng-blur="saveField(field, entity[fieldName])"
                                ng-required="::column.required"
                                style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                            <option value=""><?= i::__('Selecione...') ?></option>
                            <option ng-repeat="option in ::column.options.split('\n') track by $index" 
                                    ng-value="::option.trim()" 
                                    ng-if="::option.trim()">
                                {{::option.trim()}}
                            </option>
                        </select>
                    </td>
                    
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                        <button type="button"
                                ng-click="remove(entity[fieldName], $index); saveField(field, entity[fieldName], 0);" 
                                ng-disabled="entity[fieldName].length <= (field.config.minRows || 0)"
                                class="btn btn-danger btn-sm"
                                style="padding: 5px 10px; font-size: 12px;">
                            <?= i::__('Remover') ?>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <button type="button"
            ng-click="entity[fieldName] = entity[fieldName].concat([{}]); saveField(field, entity[fieldName], 0);" 
            ng-disabled="field.config.maxRows && entity[fieldName].length >= field.config.maxRows"
            class="btn btn-primary"
            style="margin-top: 10px;">
        <?= i::__('Adicionar Linha') ?>
    </button>

    <!-- Contador de linhas -->
    <small style="display: block; margin-top: 5px; color: #666;">
        {{entity[fieldName].length}} 
        <?= i::__('linha(s)') ?>
        <span ng-if="field.config.minRows > 0">
            - <?= i::__('Mínimo') ?>: {{::field.config.minRows}}
        </span>
        <span ng-if="field.config.maxRows">
            - <?= i::__('Máximo') ?>: {{::field.config.maxRows}}
        </span>
    </small>
</div>

<script>
// Função para inicializar a tabela customizável
if (typeof window.initCustomTable === 'undefined') {
    window.initCustomTable = function(field, entity) {
        var fieldName = field.fieldName;
        var minRows = field.config.minRows || 0;
        
        // Inicializa array se não existir
        if (!entity[fieldName]) {
            entity[fieldName] = [];
        }
        
        // Garante o mínimo de linhas
        while (entity[fieldName].length < minRows) {
            entity[fieldName].push({});
        }
    };
    
    // Adiciona a função ao escopo Angular
    if (typeof angular !== 'undefined') {
        angular.element(document).ready(function() {
            var scope = angular.element('[ng-controller="RegistrationController"]').scope();
            if (scope) {
                scope.initCustomTable = window.initCustomTable;
            }
        });
    }
}
</script>
