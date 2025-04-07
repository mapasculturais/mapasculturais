<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>
<div class="registration-evaluation-tab grid-12">
    <div class="registration-evaluation-tab__include-list field col-12">
        <div class="registration-evaluation-tab__include-list__header field">
            <h3><?= i::__('Lista de inclusão')?></h3>
            <p class="registration-evaluation-tab__text"><?= i::__('Os agentes selecionados serão incluídos como avaliadores desta inscrição')?></p>
        </div>

        <div class="registration-evaluation-tab__list field">
            <div v-for="valuer in allValuers" :key="valuer.id" class="registration-evaluation-tab__list-item field">
                <label>
                    <input 
                        type="checkbox" 
                        :checked="isIncluded(valuer.userId)"
                        @change="toggleValuer(valuer, $event.target.checked, 'include')"
                    >
                    {{ valuer.name }}
                </label>
            </div>
        </div>
    </div>

    <div class="registration-evaluation-tab__exclude-list field col-12">
        <div class="registration-evaluation-tab__exclude-list__header field">
            <h3><?= i::__('Lista de exclusão')?></h3>
            <p class="registration-evaluation-tab__text"><?= i::__('Os avaliadores selecionados NÃO serão incluídos como avaliadores desta inscrição')?></p>
        </div>

        <div class="registration-evaluation-tab__list field">
            <div v-for="valuer in allValuers" :key="valuer.id" class="registration-evaluation-tab__list-item field">
                <label>
                    <input 
                        type="checkbox" 
                        :checked="isExcluded(valuer.userId)"
                        @change="toggleValuer(valuer, $event.target.checked, 'exclude')"
                    >
                    {{ valuer.name }}
                </label>
            </div>
        </div>
    </div>
</div>