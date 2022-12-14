<?php
use MapasCulturais\i;

$app = MapasCulturais\App::i();
$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$agent_fields = $app->modules['RegistrationFieldTypes']->config['availableAgentFields'];

$fields_options = [];
$fields_labels = [
    '@location' => " " . i::__('Campos de endereço'),
    '@terms:area' => " " . i::__('Área de atuação'),
    '@links' => " " . i::__(' Links'),
];

$select_fields = [];
foreach ($agent_fields as $field) {

    if(in_array($definitions[$field]['type'] ?? [], ['select', 'multiselect','checkboxes'])){
        $options_list = $definitions[$field]['options'] ?? [];
        $options = [];
        foreach($options_list as $key => $value){
            if($key != $value && is_string($key)){
                $options[] = "{$key}:{$value}";
            }else{
                $options[] = $value;
            }
        }
        
        $select_fields[$field] = implode("\n", $options);
    }

    if (isset($definitions[$field])) {
        $def = $definitions[$field];
        $fields_options[$field] = $def['label'] ?: $field;
    } else if(isset($fields_labels[$field])){
        $fields_options[$field] = $fields_labels[$field];
    } else {
        $fields_options[$field] = $field;
    }
}
?>
<div ng-if="field.fieldType === 'agent-owner-field'" >
    <?php i::_e('Campo do agente responsável:') ?>

    <select ng-model="field.config.entityField" ng-change="field.fieldOptions = null">
    <?php foreach ($fields_options as $key => $label) : ?>
        <option value="<?= $key ?>"><?= $label ?></option>
    <?php endforeach; ?>
    </select>
    
    <div ng-if="field.config.entityField == '@location'">
        <label><input type="checkbox" ng-model="field.config.setLatLon" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Definir a latitude e longitude baseado no CEP?') ?></label><br>
        <label><input type="checkbox" ng-model="field.config.setPrivacy" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Fornecer opção para mudar a privacidade da localização?') ?></label>
    </div>
    <div ng-if="field.config.entityField == '@terms:area'">
        <?php i::_e("Informe os termos que estarão disponíveis para seleção. <br>Para fazer um mapeamento de valores utilize <strong>valor salvo:valor exibido</strong>. Exemplo: <strong>Dança:Artes da Dança</strong>") ?>
        
        <textarea ng-model="field.fieldOptions" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>"></textarea>
    </div>
    <?php foreach($select_fields as $field_name => $options):?>
        <div ng-if='field.config.entityField === "<?=$field_name?>"'>
            <?php i::_e("Informe os termos que estarão disponíveis para seleção.") ?>
            <textarea ng-model="field.fieldOptions" ng-init="field.fieldOptions = field.fieldOptions || '<?=htmlentities($options)?>'" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>"></textarea>
        </div>
    <?php endforeach?>
    <div ng-if="field.config.entityField == '@links'">
        <label><input type="checkbox" ng-model="field.config.title" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pedir de título') ?></label><br>
    </div>
    
</div>