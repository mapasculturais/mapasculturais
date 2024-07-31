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

<fieldset class="colors-customizer">
    <legend class="colors-customizer__legend">
        <h3> <?= i::__('Cores das entidades') ?> </h3>
    </legend>

    <div class="colors-customizer__content">    
        <div class="colors-customizer__previews">
            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item primary__background" :style="{ backgroundColor: subsite.color_primary }"> <mc-icon name="home"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Primária') ?> </span>
                <entity-field :entity="subsite" prop="color_primary" hideLabel :autosave="300"></entity-field>
            </label>
    
            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item secondary__background" :style="{ backgroundColor: subsite.color_secondary}"> <mc-icon name="home"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Secundária') ?> </span>
                <entity-field :entity="subsite" prop="color_secondary" hideLabel :autosave="300"></entity-field>
            </label>
    
            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item seal__background" :style="{ backgroundColor: subsite.color_seals}"> <mc-icon name="seal"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Selos') ?> </span>
                <entity-field :entity="subsite" prop="color_seals" hideLabel :autosave="300"></entity-field>
            </label>

            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item agent__background" :style="{ backgroundColor: subsite.color_agents}"> <mc-icon name="agent-2"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Agentes') ?> </span>
                <entity-field :entity="subsite" prop="color_agents" hideLabel :autosave="300"></entity-field>
            </label>
    
            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item event__background" :style="{ backgroundColor: subsite.color_events}"> <mc-icon name="event"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Eventos') ?> </span>
                <entity-field :entity="subsite" prop="color_events" hideLabel :autosave="300"></entity-field>
            </label>
            
            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item opportunity__background" :style="{ backgroundColor: subsite.color_opportunities}"> <mc-icon name="opportunity"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Oportunidades') ?> </span>
                <entity-field :entity="subsite" prop="color_opportunities" hideLabel :autosave="300"></entity-field>
            </label>
    
            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item project__background" :style="{ backgroundColor: subsite.color_projects}"> <mc-icon name="project"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Projetos') ?> </span>
                <entity-field :entity="subsite" prop="color_projects" hideLabel :autosave="300"></entity-field>
            </label>
            
            <label href="http://localhost/" class="colors-customizer__preview">
                <span class="colors-customizer__preview-item space__background" :style="{ backgroundColor: subsite.color_spaces}"> <mc-icon name="space"></mc-icon> </span>
                <span class="colors-customizer__preview-label"> <?= i::__('Espaços') ?> </span>
                <entity-field :entity="subsite" prop="color_spaces" hideLabel :autosave="300"></entity-field>
            </label>
        </div>
    </div>
</fieldset>