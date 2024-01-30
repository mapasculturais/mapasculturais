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

    <div class="opportunity-evaluation-committee__header">
        <h2><?php i::_e('Configuração da Avaliação') ?></h2>
        <p><?php i::_e('A avaliação técnica consiste em um valor quantitativo, por exemplo, uma nota de 0 a 10.') ?></p>
    </div>

    <div class="opportunity-evaluation-committee__definitions">
        <div class="opportunity-evaluation-committee__header-definitions">
            <p class="bold"><?php i::_e('Comissão de avaliação técnica') ?></p>
            <p><?php i::_e('Defina os agentes que serão avaliadores desta fase.') ?></p>
        </div>

        <div class="opportunity-evaluation-committee__new-evaluator">
            <select-entity type="agent" :select="queryString" @select="selectAgent($event)" openside="down-right">
                <template #button="{ toggle }">
                    <button class="button button--sm button--icon button--primary" @click="toggle()">
                        <?php i::_e('Adicionador pessoa avaliadora') ?>
                    </button>
                </template>
            </select-entity>
        </div>

        <div v-if="showReviewers" class="opportunity-evaluation-committee__card-grouping">

            <div class="opportunity-evaluation-committee__card" v-for="infoReviewer in infosReviewers" :key="infoReviewer.id">
                <div class="opportunity-evaluation-committee__card-header">
                    <div class="opportunity-evaluation-committee__card-header-info">
                        <mc-avatar :entity="infoReviewer.agent" size="xsmall"></mc-avatar>
                        <span class="bold">{{infoReviewer.agent.name}}</span>

                        <div class="opportunity-evaluation-committee__pending-evaluations">
                            <mc-icon name="info"></mc-icon> <?= i::__('Avaliações pendentes') ?>
                        </div>
                    </div>

                    <div class="opportunity-evaluation-committee__card-header-actions">
                        <mc-confirm-button @confirm="reopenEvaluations(infoReviewer.agentUserId)">
                            <template #button="{open}">
                                <button class="button button--primary" @click="open()">
                                    <?php i::_e('Reabrir avaliações') ?>
                                </button>
                            </template>         
                            <template #message="message">
                                <?php i::_e('Você tem certeza que deseja reabrir as avaliações para este avaliador?') ?>
                            </template> 
                        </mc-confirm-button>
                        
                        <button class="button button--disable button--icon button--sm" @click="disableOrEnableReviewer(infoReviewer)">
                            <mc-icon name="close"></mc-icon> {{buttonText(infoReviewer.status)}}
                        </button>
        
                        <mc-confirm-button @confirm="delReviewer(infoReviewer.agent)">
                            <template #button="{open}">
                                <button class="button button--delete button--icon button--sm" @click="open()">
                                    <mc-icon name="trash"></mc-icon> <?= i::__('Excluir') ?>
                                </button>
                            </template> 
                            <template #message="message">
                                <?php i::_e('Você tem certeza que deseja remover este avaliador?') ?>
                            </template> 
                        </mc-confirm-button>
                    </div>
                </div>

                <div class="opportunity-evaluation-committee__card-content">
                    <div class="field opportunity-evaluation-committee__distribution">
                        <label><?php i::_e('Distribuição') ?></label>
                        <input type="text" @change="sendDefinition('distribution')" v-model="entity.fetch[infoReviewer.agentUserId]"/>
                    </div>

                    <div v-if="entity.opportunity.registrationCategories.length > 0" class="field">
                        <label><?php i::_e('Categorias a serem avaliadas') ?></label>
                        <div class="opportunity-evaluation-committee__categories">
                            <mc-tag-list :tags="entity.fetchCategories[infoReviewer.agentUserId]" classes="opportunity__background" @remove="sendDefinition('categoryRemove')" editable></mc-tag-list>
                            <mc-multiselect :model="entity.fetchCategories[infoReviewer.agentUserId]" :items="entity.opportunity.registrationCategories" #default="{popover, setFilter}" @selected="sendDefinition('category')" @removed="sendDefinition('categoryRemove')">
                                <button class="button button--rounded button--sm button--icon button--primary" @click="popover.toggle()" >
                                    <?php i::_e("Adicionar nova") ?>
                                    <mc-icon name="add"></mc-icon>
                                </button>
                            </mc-multiselect>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>