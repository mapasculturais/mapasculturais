<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>
<entity-field :entity="entity" hide-required label="<?php i::esc_attr_e("Nome ou título")?>" prop="name"></entity-field>
<entity-field :entity="entity" hide-required  :editable="true" label="<?php i::esc_attr_e("Selecione o tipo do espaço")?>" prop="type"></entity-field>
<entity-terms :entity="entity" hide-required :editable="true" :classes="areaClasses" taxonomy='area' title="<?php i::esc_attr_e("Área de Atuação") ?>"></entity-terms>
<entity-field :entity="entity" hide-required prop="shortDescription" :max-length="400" label="<?php i::esc_attr_e("Adicione uma descrição curta para o espaço")?>"></entity-field>
