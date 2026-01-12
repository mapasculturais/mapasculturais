<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$taxonomies = $app->getRegisteredTaxonomies("MapasCulturais\Entities\Agent");
?>
<entity-field :entity="entity" hide-required  :editable="true" label="<?php i::esc_attr_e("Selecione o tipo do agente")?>" prop="type"></entity-field>
<entity-field :entity="entity" hide-required label="<?php i::esc_attr_e("Nome ou título")?>" prop="name"></entity-field>

<?php foreach($taxonomies as $taxonomy): ?>
    <?php if($taxonomy->required): ?>
        <entity-terms :entity="entity" hide-required :editable="true" :classes="areaClasses" taxonomy='<?php echo $taxonomy->slug; ?>' title="<?php echo $taxonomy->description; ?>"></entity-terms>
    <?php endif; ?>
<?php endforeach; ?>

<entity-field :entity="entity" hide-required prop="shortDescription" :max-length="400" label="<?php i::esc_attr_e("Adicione uma descrição curta para o agente")?>"></entity-field>
<entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>