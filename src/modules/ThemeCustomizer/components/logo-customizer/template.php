<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    theme-logo
    entity-field
');

?>

<fieldset class="logo-customizer">
    <legend class="logo-customizer__legend">
        <h3> <?= i::__('Customização da logo') ?> </h3>
    </legend>

    <div class="logo-customizer__content">        
        <div class="logo-customizer__settings grid-12">
            <entity-field class="col-12" :entity="subsite" prop="logo_title" :autosave="300"></entity-field>
            <entity-field class="col-12" :entity="subsite" prop="logo_subtitle" :autosave="300"></entity-field>
            <entity-field class="col-12" :entity="subsite" prop="custom_colors" :autosave="300"></entity-field>
            
            <div v-if="subsite.custom_colors" class="logo-customizer__color-inputs col-12">
                <entity-field :entity="subsite" prop="logo_color1" :autosave="300"></entity-field>
                <entity-field :entity="subsite" prop="logo_color2" :autosave="300"></entity-field>
                <entity-field :entity="subsite" prop="logo_color3" :autosave="300"></entity-field>
                <entity-field :entity="subsite" prop="logo_color4" :autosave="300"></entity-field>
            </div>
        </div>

        <div class="logo-customizer__preview">
            <theme-logo :bg1="colors.first" :bg2="colors.second" :bg3="colors.third" :bg4="colors.fourth" :title="title" :subtitle="subtitle"></theme-logo>
        </div>
    </div>
</fieldset>