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
            <div v-if="infoReviewer.status == -5" class="grid-12">
                <mc-alert type="warning" class="col-9">
                    <div class="col-9">
                        <strong>{{infoReviewer.agent.name}}</strong> <?= i::__('ainda não aceitou o convite para avaliar esta oportunidade') ?>
                    </div>
                </mc-alert>
                <mc-confirm-button @confirm="delReviewer(infoReviewer.agent)" no="<?= i::esc_attr__('Não') ?>" yes="<?= i::esc_attr__('Sim') ?>">
                    <template #button="{open}">
                        <button class="button button--delete button--icon button--sm col-3" @click="open()">
                            <mc-icon name="trash"></mc-icon> <?= i::__('Cancelar convite') ?>
                        </button>
                    </template> 
                    <template #message="message">
                        <p>
                            <?= i::__('Você tem certeza que cancelar o convite para <strong>{{infoReviewer.agent.name}}</strong> avaliar esta oportunidade?') ?>
                        </p>
                    </template> 
                </mc-confirm-button>
            </div>
            <div v-else class="opportunity-evaluation-committee__card-header">
                <div class="opportunity-evaluation-committee__card-header-info">
                    <mc-avatar :entity="infoReviewer.agent" size="xsmall"></mc-avatar>
                    
                   <div class="evaluator-data">
                        <span class="bold">{{infoReviewer.agent.name}}</span>
                        <span>
                            <small>
                                <strong>E-mail:</strong> {{infoReviewer.agent.user.email}} | <strong>ID Agente:</strong> #{{infoReviewer.agent.id}} | <strong>ID Usuário:</strong> #{{infoReviewer.agent.user.id}}
                            </small>
                        </span>
                   </div>
                </div>

                <div class="opportunity-evaluation-committee__card-header-actions">
                    <mc-confirm-button v-if="infoReviewer.metadata?.summary.sent > 0" @confirm="reopenEvaluations(infoReviewer.agentUserId)">
                        <template #button="{open}">
                            <button class="button button--primary" :class="{'disabled' : infoReviewer.metadata.summary.sent <= 0}" @click="open()">
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
    
                    <mc-confirm-button @confirm="delReviewer(infoReviewer.agent)" no="<?= i::esc_attr__('Cancelar') ?>" yes="<?= i::esc_attr__('Excluir') ?>">
                        <template #button="{open}">
                            <button class="button button--delete button--icon button--sm" @click="open()">
                                <mc-icon name="trash"></mc-icon> <?= i::__('Excluir') ?>
                            </button>
                        </template> 
                        <template #message="message">
                            <p>
                                <?= i::__('Você tem certeza que deseja excluir <strong>{{infoReviewer.agent.name}}</strong> da função de avaliador(a)?') ?>
                            </p>
                            <br><br>
                            <p>
                                <mc-alert type="warning">
                                    <strong><?= i::__('ATENÇÃO') ?>: </strong> <?= i::__('TODAS as avaliações realizadas por <strong>{{infoReviewer.agent.name}}</strong> serão <strong>excluídas permanentemente</strong>.') ?>
                                </mc-alert>
                            </p>
                        </template> 
                    </mc-confirm-button>
                </div>
            </div>
           
            <div v-if="showSummary(infoReviewer.metadata?.summary)" class="opportunity-evaluation-committee__summary">
                <span class="opportunity-evaluation-committee__summary__pending">
                    <mc-icon name="clock"></mc-icon> <?= i::_e('Avaliações pendentes') ?>: {{infoReviewer.metadata.summary.pending}}
                </span>
                <span class="opportunity-evaluation-committee__summary__started">
                    <mc-icon name="clock"></mc-icon> <?= i::_e('Avaliações iniciadas') ?>: {{infoReviewer.metadata.summary.started}}
                </span>
                <span class="opportunity-evaluation-committee__summary__completed">
                    <mc-icon name="check"></mc-icon> <?= i::_e('Avaliações concluídas') ?>: {{infoReviewer.metadata.summary.completed}}
                </span>
                <span class="opportunity-evaluation-committee__summary__sent">
                    <mc-icon name="send"></mc-icon> <?= i::_e('Avaliações enviadas') ?>: {{infoReviewer.metadata.summary.sent}}
                </span>
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
                        <input type="text" placeholder="00-99" maxlength="5" @change="sendDefinition('addDistribution', infoReviewer.agentUserId, $event, 'fetch')" v-model="entity.fetch[infoReviewer.agentUserId]"/>
                    </div>
                    <div class="opportunity-evaluation-committee__all-settings" v-if="registrationCategories.length > 0 || registrationRanges.length > 0 || registrationProponentTypes.length > 0">
                        <div v-if="registrationCategories.length > 0" class="field">
                            <label><?php i::_e('Categorias a serem avaliadas') ?></label>
                            <div class="opportunity-evaluation-committee__settings">
                                <mc-multiselect :model="entity.fetchCategories[infoReviewer.agentUserId]" :items="registrationCategories" #default="{popover, setFilter}" @selected="sendDefinition('addCategory', infoReviewer.agentUserId, $event, 'categories')" @removed="sendDefinition('removeCategory', infoReviewer.agentUserId, $event, 'categories')">
                                    <button class="button button--rounded button--sm button--icon button--primary" @click="popover.toggle()" >
                                        <?php i::_e("Adicionar categoria") ?>
                                        <mc-icon name="add"></mc-icon>
                                    </button>
                                </mc-multiselect>
                                <mc-tag-list :tags="entity.fetchCategories[infoReviewer.agentUserId]" classes="opportunity__background" @remove="sendDefinition('removeCategory', infoReviewer.agentUserId, $event, 'categories')" editable></mc-tag-list>
                            </div>
                        </div>

                        <div v-if="registrationRanges.length > 0" class="field">
                            <label><?php i::_e('Faixas a serem avaliadas') ?></label>
                            <div class="opportunity-evaluation-committee__settings">
                                <mc-multiselect :model="entity.fetchRanges[infoReviewer.agentUserId]" :items="registrationRanges" #default="{popover, setFilter}" @selected="sendDefinition('addRange', infoReviewer.agentUserId, $event, 'ranges')" @removed="sendDefinitionRanges('removeRange', infoReviewer.agentUserId, $event, 'ranges')">
                                    <button class="button button--rounded button--sm button--icon button--primary" @click="popover.toggle()" >
                                        <?php i::_e("Adicionar faixa") ?>
                                        <mc-icon name="add"></mc-icon>
                                    </button>
                                </mc-multiselect>
                                <mc-tag-list :tags="entity.fetchRanges[infoReviewer.agentUserId]" classes="opportunity__background" @remove="sendDefinition('removeRange', infoReviewer.agentUserId, $event, 'ranges')" editable></mc-tag-list>
                            </div>
                        </div>

                        <div v-if="registrationProponentTypes.length > 0" class="field">
                            <label><?php i::_e('Proponentes a serem avaliados') ?></label>
                            <div class="opportunity-evaluation-committee__settings">
                                <mc-multiselect :model="entity.fetchProponentTypes[infoReviewer.agentUserId]" :items="registrationProponentTypes" #default="{popover, setFilter}" @selected="sendDefinition('addProponentType', infoReviewer.agentUserId, $event, 'proponentTypes')" @removed="sendDefinitionRanges('removeProponentType', infoReviewer.agentUserId, $event, 'proponentTypes')">
                                    <button class="button button--rounded button--sm button--icon button--primary" @click="popover.toggle()" >
                                        <?php i::_e("Adicionar proponente") ?>
                                        <mc-icon name="add"></mc-icon>
                                    </button>
                                </mc-multiselect>
                                <mc-tag-list :tags="entity.fetchProponentTypes[infoReviewer.agentUserId]" classes="opportunity__background" @remove="sendDefinition('removeProponentType', infoReviewer.agentUserId, $event, 'proponentTypes')" editable></mc-tag-list>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="opportunity-evaluation-committee__add-new-evaluator">
        <select-entity type="agent" :select="queryString" :query="query" @select="selectAgent($event)" openside="down-right" permissions="">
            <template #button="{ toggle }">
                <button class="button button--icon button--primary" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Adicionador pessoa avaliadora') ?>
                </button>
            </template>

            <template #entityInfo="{entity}">
                <span class="icon">
                    <mc-avatar :entity="entity" size="xsmall"></mc-avatar>
                </span>
                <span class="label"> #{{entity.id}} - {{entity.name}}</span>
            </template>
        </select-entity>
    </div>
</div>