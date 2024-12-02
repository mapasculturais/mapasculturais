<?php
use MapasCulturais\i;

$app = MapasCulturais\App::i();
$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$agent_fields = $app->modules['RegistrationFieldTypes']->getAgentFields();

$fields_options = [];
$fields_labels = [
    '@location' => " " . i::__('Campos de endereço'),
    '@terms:area' => " " . i::__('Área de atuação'),
    '@links' => " " . i::__(' Links'),
];

$taxonomies_fields = [];
if($registered_taxonomies = $app->getRegisteredTaxonomies()) {
    foreach($registered_taxonomies as $taxonomie) {
        if($taxonomie->restrictedTerms && in_array('MapasCulturais\Entities\Opportunity', $taxonomie->entities)) {
            $taxonomies_fields["@terms:{$taxonomie->slug}"] = $taxonomie->slug;
            $fields_labels["@terms:{$taxonomie->slug}"] = $taxonomie->description;
        }
    }
}

$app->applyHookBoundTo($this, "registrationFieldTypes--agent-collective-field-config-fields_labels", [&$fields_labels]);

$select_fields = [];
$taxonomie_options = [];
$area = $app->getRegisteredTaxonomyBySlug('area');
foreach ($agent_fields as $field) {

    if(in_array($field, array_keys($taxonomies_fields))) {
        $taxonomie_options[$field] = $taxonomies_fields[$field];
    }
    
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


$this->jsObject['registered_terms'] = array_keys($taxonomie_options);

?>
<div ng-if="field.fieldType === 'agent-collective-field'" >
    <?php i::_e('Campo do agente responsável:') ?>

    <select ng-model="field.config.entityField" ng-change="field.fieldOptions = null">
    <?php foreach ($fields_options as $key => $label) : ?>
        <option value="<?= $key ?>"><?= $label ?></option>
    <?php endforeach; ?>
    </select>
    
    <?php $this->applyTemplateHook('registrationFieldTypes--agent-collective-field-config','before', ['agent_fields' => $agent_fields]); ?>
    <div ng-if="field.config.entityField == '@location'">
        <label><input type="checkbox" ng-model="field.config.setLatLon" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Definir a latitude e longitude baseado no CEP?') ?></label><br>
        <label><input type="checkbox" ng-model="field.config.setPrivacy" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Fornecer opção para mudar a privacidade da localização?') ?></label>
    </div>

    <div ng-if="data.registered_terms.includes(field.config.entityField)">
        <?php foreach($taxonomie_options as $field_name => $slug):?>
            <div ng-if='field.config.entityField === "<?=$field_name?>"'>
                <?php i::_e("Informe os termos que estarão disponíveis para seleção.") ?>
                <textarea ng-model="field.fieldOptions" ng-init='field.fieldOptions = field.fieldOptions || data.taxonomies["<?=$slug?>"].terms.join("\n")' placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>"></textarea>
                <div>
                    <label><?php i::_e('Limite de Opções') ?><input type="number" ng-model="field.config.maxOptions"></label><br>
                    <small class="registration-help"><?php i::_e('Digite o limite de opções. Deixe em branco ou coloque 0 para selecionar ilimitadas.'); ?></small>
                </div>
                
            </div>
        <?php endforeach?>
    </div>
    <?php foreach($select_fields as $field_name => $options):?>
        <div ng-if='field.config.entityField === "<?=$field_name?>"'>
            <?php i::_e("Informe os termos que estarão disponíveis para seleção.") ?>
            <textarea ng-model="field.fieldOptions" ng-init="field.fieldOptions = field.fieldOptions || '<?=htmlentities($options)?>'" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>"></textarea>
            <div>
                <label><?php i::_e('Limite de Opções') ?><input type="number" ng-model="field.config.maxOptions"></label><br>
                <small class="registration-help"><?php i::_e('Digite o limite de opções. Deixe em branco ou coloque 0 para selecionar ilimitadas.'); ?></small>
            </div>
        </div>
    <?php endforeach?>

    <div ng-if="field.config.entityField == '@terms:area' || field.fieldOptions.length > 0">
        <label>
            <?php i::_e('Modo de visualização') ?><br>
            <select ng-model="field.config.viewMode">
                <option value="checkbox"><?php i::_e('Lista de checkboxes') ?></option>
                <option value="tag"><?php i::_e('Lista de tags') ?></option>
            </select>
        </label>
    </div>

    <div ng-if="field.config.entityField == 'longDescription' || field.config.entityField == 'shortDescription'">
        <input type="text" ng-model="field.maxSize" placeholder="<?php \MapasCulturais\i::esc_attr_e("Quantidade máxima de caracteres");?>"/>
    </div>
    
    <div ng-if="field.config.entityField == '@links'">
        <label><input type="checkbox" ng-model="field.config.title" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pedir de título') ?></label><br>
    </div>

    
    <?php $this->applyTemplateHook('registrationFieldTypes--agent-collective-field-config','after', ['agent_fields' => $agent_fields]); ?>
</div>