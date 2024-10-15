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
    opportunity-registration-filter-configuration
');
?>
<div class="opportunity-evaluation-committee">        
    <div class="opportunity-evaluation-committee__header">
        <select-entity type="agent" :select="queryString" @select="selectAgent($event)" openside="down-right" permissions="">
            <template #button="{ toggle }">
                <button class="button button--icon button--primary button--md" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Adicionar pessoa avaliadora') ?>
                </button>
            </template>
        </select-entity>

        <div class="opportunity-evaluation-committee__expand-button">
            <button class="button button--primary" @click="expandAllToggles">
                <?php i::_e('Expandir todos os avaliadores') ?>
            </button>
        </div>
    </div>

    <div v-if="showReviewers" class="opportunity-evaluation-committee__content">
        <div class="opportunity-evaluation-committee__card" v-for="infoReviewer in infosReviewers" :key="infoReviewer.id">
            <div :class="['opportunity-evaluation-committee__card-header', {'open-toggle': infoReviewer.isContentVisible}]">
                <div class="opportunity-evaluation-committee__card-toggle" @click.stop="toggleContent(infoReviewer.id)">
                    <mc-icon :name="infoReviewer.isContentVisible ? 'up' : 'down'"></mc-icon>
                </div>
                
                <div class="opportunity-evaluation-committee__card-header-content">
                    <div class="opportunity-evaluation-committee__card-entity">
                        <div class="opportunity-evaluation-committee__card-header-info">
                            <mc-avatar v-if="infoReviewer.status !== -5" :entity="infoReviewer.agent" size="xsmall"></mc-avatar>
                            <mc-avatar v-if="infoReviewer.status == -5" :entity="infoReviewer.agent" type="warning" size="xsmall" square></mc-avatar>
                            <div class="opportunity-evaluation-committee__card-header-info-name">
                                <span class="bold">{{infoReviewer.agent.name}}</span>
                                <small class="semibold">ID: #{{infoReviewer.agent.id}}</small>
                            </div>
                        </div>
                    </div>
                    <div class="opportunity-evaluation-committee__card-status">
                        <div class="opportunity-evaluation-committee__card-status-wrapper field">
                            <label class="status-label"><?= i::_e('Status das avaliações:') ?></label>
                            <div class="opportunity-evaluation-committee__summary">
                                <span class="opportunity-evaluation-committee__summary--pending semibold">
                                    <mc-icon name="clock"></mc-icon> <?= i::_e('Pendentes') ?>: {{infoReviewer.metadata.summary.pending}}
                                </span>
                                <span class="opportunity-evaluation-committee__summary--started semibold">
                                    <mc-icon name="clock"></mc-icon> <?= i::_e('Iniciadas') ?>: {{infoReviewer.metadata.summary.started}}
                                </span>
                                <span class="opportunity-evaluation-committee__summary--completed semibold">
                                    <mc-icon name="check"></mc-icon> <?= i::_e('Concluídas') ?>: {{infoReviewer.metadata.summary.completed}}
                                </span>
                                <span class="opportunity-evaluation-committee__summary--sent semibold">
                                    <mc-icon name="send"></mc-icon> <?= i::_e('Enviadas') ?>: {{infoReviewer.metadata.summary.sent}}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="opportunity-evaluation-committee__card-content" v-if="infoReviewer.isContentVisible">
                <opportunity-registration-filter-configuration
                    :entity="entity"
                    v-model:default-value="fetchConfigs[infoReviewer.agent.id]"
                    :excludeFields="excludeFields"
                    @updateExcludeFields="$emit('updateExcludeFields', $event)"
                    :info-reviewer="infoReviewer"
                />
                <div class="opportunity-evaluation-committee__card-distribution">
                    <div v-if="infoReviewer.default" class="opportunity-evaluation-committee__change-distributions">
                        <mc-alert type="warning"><?= i::__('Essa pessoa irá avaliar todas as inscrições')?></mc-alert>
                        <button class="button button--primary button--md" @click="infoReviewer.default = !infoReviewer.default" >
                            <?php i::_e("Alterar distribuição das avaliações") ?>
                        </button>
                    </div>

                    <div v-if="!infoReviewer.default" class="opportunity-evaluation-committee__distributions">
                        <div class="field opportunity-evaluation-committee__distribution">
                            <label><?php i::_e('Distribuição') ?></label>
                            <input type="text" placeholder="00-99" maxlength="5" @input.stop="sendDefinition('addDistribution', infoReviewer.agentUserId, $event, 'distribution')" v-model="entity.fetch[infoReviewer.agentUserId]"/>
                        </div>
                        <div class="opportunity-evaluation-committee__all-settings" v-if="registrationCategories.length > 0 || registrationRanges.length > 0 || registrationProponentTypes.length > 0">
                            <div v-if="registrationCategories.length > 0" class="field">
                                <label><?php i::_e('Categorias a serem avaliadas') ?></label>
                                <div class="opportunity-evaluation-committee__settings">
                                    <mc-multiselect :model="entity.fetchCategories[infoReviewer.agentUserId]" :items="registrationCategories" #default="{popover, setFilter}" @selected="sendDefinition('addCategory', infoReviewer.agentUserId, $event, 'categories')" @removed="sendDefinition('removeCategory', infoReviewer.agentUserId, $event, 'categories')">
                                        <button class="button button--rounded button--sm button--icon button--primary" @click.stop="popover.toggle()" >
                                            <?php i::_e("Adicionar") ?>
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
                                        <button class="button button--rounded button--sm button--icon button--primary" @click.stop="popover.toggle()" >
                                            <?php i::_e("Adicionar") ?>
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
                                        <button class="button button--rounded button--sm button--icon button--primary" @click.stop="popover.toggle()" >
                                            <?php i::_e("Adicionar") ?>
                                            <mc-icon name="add"></mc-icon>
                                        </button>
                                    </mc-multiselect>
                                    <mc-tag-list :tags="entity.fetchProponentTypes[infoReviewer.agentUserId]" classes="opportunity__background" @remove="sendDefinition('removeProponentType', infoReviewer.agentUserId, $event, 'proponentTypes')" editable></mc-tag-list>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>

                <div class="opportunity-evaluation-committee__card-footer"> 
                <!-- v-if="infoReviewer.status !== -5" -->
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
                    
                    <div class="opportunity-evaluation-committee__card-footer-alert">
                        <div v-if="infoReviewer.status == -5" class="grid-12">
                            <mc-alert type="warning" class="col-9">
                                <div class="col-9">
                                    <strong>{{infoReviewer.agent.name}}</strong> <?= i::__('ainda não aceitou o convite para avaliar esta oportunidade') ?>
                                </div>
                            </mc-alert>
                            <mc-confirm-button @confirm="delReviewer(infoReviewer.agent)" no="<?= i::esc_attr__('Não') ?>" yes="<?= i::esc_attr__('Sim') ?>">
                                <template #button="{open}">
                                    <button class="opportunity-evaluation-committee__card-footer-button button button--delete button--icon button--sm col-3" @click="open()">
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
                    </div>

                    <div class="opportunity-evaluation-committee__card-footer-actions" v-if="infoReviewer.status !== -5">
                        <button class="opportunity-evaluation-committee__card-footer-button button button--disable button--icon button--sm" @click="disableOrEnableReviewer(infoReviewer)">
                            <mc-icon name="close"></mc-icon> {{buttonText(infoReviewer.status)}}
                        </button>

                        <mc-confirm-button @confirm="delReviewer(infoReviewer.agent)" no="<?= i::esc_attr__('Cancelar') ?>" yes="<?= i::esc_attr__('Excluir') ?>">
                            <template #button="{open}">
                                <button class="opportunity-evaluation-committee__card-footer-button button button--delete button--icon button--sm" @click="open()">
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
            </div>
        </div>   
    </div>

    <div class="opportunity-evaluation-committee__footer" v-if="infosReviewers.length > 0">
    <!-- {{console.log(infosReviewers)}} -->
        <select-entity type="agent" :select="queryString" @select="selectAgent($event)" openside="down-right" permissions="">
            <template #button="{ toggle }">
                <button class="button button--icon button--primary button--md" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Adicionar pessoa avaliadora') ?>
                </button>
            </template>
        </select-entity>
    </div>
</div>