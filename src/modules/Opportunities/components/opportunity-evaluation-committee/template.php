<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-avatar
    mc-confirm-button
    mc-icon
    mc-multiselect
    mc-tag-list
    select-entity
');
?>
<div class="opportunity-evaluation-committee">        
    <div class="opportunity-evaluation-committee__header">
        <p><?php i::_e('Defina os agentes que serão avaliadores desta fase.') ?></p>
    </div>

    <div v-if="showReviewers" class="opportunity-evaluation-committee__card-grouping">
        <div class="opportunity-evaluation-committee__card" v-for="infoReviewer in infosReviewers" :key="infoReviewer.id">
            <div class="opportunity-evaluation-committee__card-header">
                <div class="opportunity-evaluation-committee__card-header-info">
                    <mc-avatar :entity="infoReviewer.agent" size="xsmall"></mc-avatar>

                    <span class="bold">{{infoReviewer.agent.name}}</span>
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
                <div v-if="infoReviewer.default" class="opportunity-evaluation-committee__change-distributions">
                    <mc-alert type="warning"><?= i::__('Essa pessoa irá avaliar todas as inscrições')?></mc-alert>
                    <button class="button button--primary button--md" @click="infoReviewer.default = !infoReviewer.default" >
                        <?php i::_e("Alterar distribuição das avaliações") ?>
                    </button>
                </div>

                <div v-if="!infoReviewer.default" class="opportunity-evaluation-committee__distributions">
                    <div class="field opportunity-evaluation-committee__distribution">
                        <label><?php i::_e('Distribuição') ?></label>
                        <input type="text" placeholder="0-9" maxlength="3" @change="sendDefinition('addDistribution', infoReviewer.agentUserId)" v-model="entity.fetch[infoReviewer.agentUserId]"/>
                    </div>
                    <div v-if="registrationCategories.length > 0" class="field">
                        <label><?php i::_e('Categorias a serem avaliadas') ?></label>
                        <div class="opportunity-evaluation-committee__categories">
                            <mc-multiselect :model="entity.fetchCategories[infoReviewer.agentUserId]" :items="registrationCategories" #default="{popover, setFilter}" @selected="sendDefinition('addCategory', infoReviewer.agentUserId, $event)" @removed="sendDefinition('removeCategory'), infoReviewer.agentUserId">
                                <button class="button button--rounded button--sm button--icon button--primary" @click="popover.toggle()" >
                                    <?php i::_e("Adicionar categoria") ?>
                                    <mc-icon name="add"></mc-icon>
                                </button>
                            </mc-multiselect>
                            <mc-tag-list :tags="entity.fetchCategories[infoReviewer.agentUserId]" classes="opportunity__background" @remove="sendDefinition('removeCategory', infoReviewer.agentUserId)" editable></mc-tag-list>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="opportunity-evaluation-committee__add-new-evaluator">
        <select-entity type="agent" :select="queryString" @select="selectAgent($event)" openside="down-right">
            <template #button="{ toggle }">
                <button class="button button--icon button--primary" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Adicionador pessoa avaliadora') ?>
                </button>
            </template>
        </select-entity>
    </div>
</div>