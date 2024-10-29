<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>

<div class="opportunity-proponent-types">
    <h4 class="bold"><?= i::__("Tipos do proponente")?></h4>
    <h6><?= i::__("Selecione um ou mais tipos de proponente que poderá participar do edital")?></h6>
    <div>
        <div class="opportunity-proponent-types__fields">
            <div class="opportunity-proponent-types__field" v-for="optionValue in description.optionsOrder" :key="optionValue">
                <label>
                    <input 
                        :checked="value?.includes(optionValue)" 
                        type="checkbox" 
                        :value="optionValue" 
                        @change="modifyCheckbox($event)"
                    > 
                    {{ description.options[optionValue] }}
                </label>
                <div class="opportunity-proponent-types__field field__collective" v-if="showColetivoBinding && optionValue === 'Coletivo'">
                    <label>
                        <input 
                            type="checkbox" 
                            :checked="proponentAgentRelation['Coletivo']" 
                            @change="toggleAgentRelation($event, 'Coletivo')"
                        > 
                        <?= i::__("Habilitar a vinculação de agente coletivo")?>
                    </label>
                </div>

                <div class="opportunity-proponent-types__field field__legal" v-if="showJuridicaBinding && optionValue === 'Pessoa Jurídica'">
                    <label>
                        <input 
                            type="checkbox" 
                            :checked="proponentAgentRelation['Pessoa Jurídica']" 
                            @change="toggleAgentRelation($event, 'Pessoa Jurídica')"
                        > 
                        <?= i::__("Habilitar a vinculação de agente coletivo")?>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
