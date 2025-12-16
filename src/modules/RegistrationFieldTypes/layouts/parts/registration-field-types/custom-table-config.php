<?php
use MapasCulturais\i;
?>

<div ng-if="field.fieldType === 'custom-table'">
    <h4><?= i::__('Configuração das Colunas da Tabela') ?></h4>
    
    <!-- Inicializa o config com valores padrão -->
    <div ng-init="field.config.columns = field.config.columns || []; field.config.minRows = field.config.minRows || 0; field.config.maxRows = field.config.maxRows || null;"></div>
    
    <!-- Lista de colunas configuradas -->
    <div ng-repeat="column in field.config.columns track by $index" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 4px; background-color: #f9f9f9;">
        <div style="margin-bottom: 10px;">
            <label style="display: block; margin-bottom: 5px;">
                <strong><?= i::__('Nome da Coluna') ?>:</strong>
                <input type="text" ng-model="column.name" ng-change="$parent.$parent.save()" ng-blur="$parent.$parent.save()" style="width: 100%; padding: 5px;" placeholder="<?= i::__('Ex: Nome, Quantidade, Valor...') ?>" required>
            </label>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label style="display: block; margin-bottom: 5px;">
                <strong><?= i::__('Tipo de Dado') ?>:</strong>
                <select ng-model="column.type" ng-change="$parent.$parent.save()" ng-init="column.type = column.type || 'text'" style="width: 100%; padding: 5px;">
                    <option value="text"><?= i::__('Texto') ?></option>
                    <option value="number"><?= i::__('Número') ?></option>
                    <option value="email"><?= i::__('E-mail') ?></option>
                    <option value="cpf"><?= i::__('CPF') ?></option>
                    <option value="date"><?= i::__('Data') ?></option>
                    <option value="select"><?= i::__('Lista de opções') ?></option>
                </select>
            </label>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label style="display: inline-block;">
                <input type="checkbox" ng-model="column.required" ng-change="$parent.$parent.save()" ng-init="column.required = column.required !== undefined ? column.required : false">
                <?= i::__('Campo Obrigatório') ?>
            </label>
        </div>
        
        <!-- Opções para tipo 'select' -->
        <div ng-if="column.type === 'select'" style="margin-bottom: 10px;">
            <label style="display: block; margin-bottom: 5px;">
                <strong><?= i::__('Opções (uma por linha)') ?>:</strong>
                <textarea ng-model="column.options" ng-change="$parent.$parent.save()" ng-blur="$parent.$parent.save()" ng-init="column.options = column.options || ''" style="width: 100%; padding: 5px; min-height: 80px;" placeholder="<?= i::__('Digite uma opção por linha') ?>"></textarea>
            </label>
        </div>
        
        <button type="button" ng-click="field.config.columns.splice($index, 1); $parent.$parent.save()" class="btn btn-danger" style="margin-top: 10px;">
            <?= i::__('Remover Coluna') ?>
        </button>
    </div>
    
    <button type="button" ng-click="field.config.columns.push({name:'', type:'text', required:false, options:''}); $parent.$parent.save()" class="btn btn-primary" style="margin-bottom: 20px;">
        <?= i::__('Adicionar Coluna') ?>
    </button>
    
    <hr style="margin: 20px 0;">
    
    <!-- Configuração de min/max linhas -->
    <div style="margin-bottom: 10px;">
        <label style="display: block; margin-bottom: 5px;">
            <strong><?= i::__('Mínimo de linhas') ?>:</strong>
            <input type="number" ng-model="field.config.minRows" ng-change="$parent.$parent.save()" min="0" style="width: 100px; padding: 5px;" placeholder="0" ng-init="field.config.minRows = field.config.minRows === undefined ? 0 : field.config.minRows">
            <small style="display: block; color: #666; margin-top: 3px;">
                <?= i::__('Deixe 0 para não ter mínimo') ?>
            </small>
        </label>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label style="display: block; margin-bottom: 5px;">
            <strong><?= i::__('Máximo de linhas') ?>:</strong>
            <input type="number" ng-model="field.config.maxRows" ng-change="$parent.$parent.save()" min="1" style="width: 100px; padding: 5px;" placeholder="<?= i::__('Ilimitado') ?>" ng-init="field.config.maxRows = field.config.maxRows || null">
            <small style="display: block; color: #666; margin-top: 3px;">
                <?= i::__('Deixe vazio para ilimitado') ?>
            </small>
        </label>
    </div>
</div>
