<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>
<entity-field :entity="entity" hide-required label=<?php i::esc_attr_e("Nome ou título") ?> prop="name"></entity-field>
<entity-terms :entity="entity" hide-required :editable="true" :classes="linguagemClasses" taxonomy='linguagem' title="<?php i::esc_attr_e("Linguagem cultural") ?>"></entity-terms>
<entity-field :entity="entity" hide-required prop="shortDescription" :max-length="400" label="<?php i::esc_attr_e("Adicione uma descrição curta para o evento") ?>"></entity-field>
<entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>