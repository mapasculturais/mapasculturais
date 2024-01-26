<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-icon
    mc-confirm-button
    mc-tag-list
    mc-multiselect
    select-entity
');
?>
<div class="opportunity-evaluation-committee">
    <h2><?php i::_e('Configuração da Avaliação') ?></h2>
    <p><?php i::_e('A avaliação técnica consiste em um valor quantitativo, por exemplo, uma nota de 0 a 10.') ?></p>
    
    <p class="bold"><?php i::_e('Comissão de avaliação técnica') ?></p>
    <p><?php i::_e('Defina os agentes que serão avaliadores desta fase.') ?></p>

    <select-entity type="agent" :select="queryString" @select="selectAgent($event)" openside="down-right">
        <template #button="{ toggle }">
            <button class="button button--sm button--icon button--primary" @click="toggle()">
                <?php i::_e('Adicionador pessoa avaliadora') ?>
            </button>
        </template>
    </select-entity>
    <div v-if="showReviewers">
        <div v-for="infoReviewer in infosReviewers" :key="infoReviewer.id">
            <div>
                <mc-avatar :entity="infoReviewer.agent" size="xsmall"></mc-avatar>
                <span class="bold">{{infoReviewer.agent.name}}</span>

                <mc-confirm-button @confirm="reopenEvaluations(infoReviewer.agentUserId)">
                    <template #button="{open}">
                        <button @click="open()">
                            <?php i::_e('Reabrir avaliações') ?>
                        </button>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Você tem certeza que deseja reabrir as avaliações para este avaliador?') ?>
                    </template> 
                </mc-confirm-button>
                
                <button @click="disableOrEnableReviewer(infoReviewer)">
                    {{buttonText(infoReviewer.status)}}
                </button>


                <mc-confirm-button @confirm="delReviewer(infoReviewer.agent)">
                    <template #button="{open}">
                        <mc-icon name="trash" @click="open()"></mc-icon>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Você tem certeza que deseja remover este avaliador?') ?>
                    </template> 
                </mc-confirm-button>
            </div>

            <div>
                <div>
                    <span class="bold"><?php i::_e('Distribuição') ?></span>
                    <input type="text" @change="sendDefinition" v-model="entity.fetch[infoReviewer.agentUserId]"/>
                </div>

                <div>
                    <span class="bold"><?php i::_e('Categorias a serem avaliadas') ?></span>
                    <mc-tag-list :tags="entity.fetchCategories[infoReviewer.agentUserId]" @remove="sendDefinition" editable></mc-tag-list>
                    <mc-multiselect :model="entity.fetchCategories[infoReviewer.agentUserId]" :items="entity.opportunity.registrationCategories" #default="{popover, setFilter}" @selected="sendDefinition" @removed="sendDefinition">
                        <input @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os itens: ') ?>">
                    </mc-multiselect>
                </div>
            </div>
        </div>
    </div>
</div>