<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>
<div class="registration-evaluation-tab grid-12">
    <div class="registration-evaluation-tab__distribution field col-12">
        <h3><?= i::__('Lista dos avaliadores distribuídos para esta inscrição') ?></h3>
        
        <div v-for="(group, groupName) in groupedValuers" :key="groupName" class="evaluation-group field">
            <h4 :class="['semibold', { tiebreaker: group.isTiebreaker }]">
                {{ group.isTiebreaker ? '<?= i::__('Voto de minerva') ?>' : group.name }}
            </h4>
            
            <ul class="valuers-list field">
                <li v-for="valuer in group.valuers" :key="valuer.id" class="valuer-item">
                    <div class="valuer-content">
                        <span class="valuer-name">{{ valuer.name }}</span>
                        <span class="valuer-status">
                            <span>
                                <span class="status-bullet" :class="getStatusClass(evaluations[valuer.userId].status)"></span>
                                <span class="semibold" :class="getStatusClass(evaluations[valuer.userId].status)">
                                    {{ evaluations[valuer.userId].status  }}
                                </span>
                            </span>
                            <span v-if="evaluations[valuer.userId].resultString" class="result-string semibold">
                                {{ evaluations[valuer.userId].resultString }}
                            </span>
                        </span>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="registration-evaluation-tab__all-valuers field col-12">
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
                        <span class="valuer-info">
                            <span>{{ valuer.name }}</span>
                            <span class="valuer-committees" v-if="getValuerCommittees(valuer)"> ({{ getValuerCommittees(valuer) }})</span>
                        </span>
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
                        <span class="valuer-info">
                            <span>{{ valuer.name }}</span>
                            <span class="valuer-committees" v-if="getValuerCommittees(valuer)"> ({{ getValuerCommittees(valuer) }})</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>