<?php
use MapasCulturais\i;

$this->import('
    select-entity
    confirm-button
    mapas-card
');
?>

<?php $this->applyTemplateHook('entity-related-agents', 'before'); ?>
<div :class="classes" class="entity-related-agents" v-if="editable || group.length > 0">
    <?php $this->applyTemplateHook('entity-related-agents', 'begin'); ?>
    <h3 v-if="group"><?php i::_e("Administrado por") ?></h3>
    <div class="entity-related-agents__group">
        <div class="entity-related-agents__group--agents">
            <div v-for="agent in group" class="agent"> 

                <popover openside="down-right" classes="agent-popover" title="<?php i::esc_attr_e('Editar link')?>">
                    <template #button="popover">
                        <a class="agent__img" @click="$event.preventDefault(); popover.toggle()"> <!--  :href="agent.singleUrl" -->
                            <img v-if="agent.files.avatar" :src="agent.files.avatar?.transformations?.avatarMedium?.url" class="agent__img--img" />
                            <mc-icon v-if="!agent.files.avatar" name="agent"></mc-icon>
                        </a>
                    </template>
                    <template #default="{close}">
                        <mapas-card class="view-card" noTitle>
                            <div class="view-card__close" @click="close()">
                                <mc-icon name="close"></mc-icon>
                            </div>

                            <div class="view-card__header">
                                <div class="image">
                                    <img v-if="agent.files.avatar" :src="agent.files.avatar?.transformations?.avatarMedium?.url" class="agent__img--img" />
                                    <mc-icon v-if="!agent.files.avatar" name="image"></mc-icon>
                                </div>
                                <div class="name">
                                    {{agent.name}}
                                </div>
                            </div>

                            <div class="view-card__content">
                                <div class="type">
                                    <span> <?= i::__('Este agente atua de forma') ?>  <span :class="['actualType', entity.__objectType+'__color']">{{entity.type.name}}</span> </span>
                                </div>
                                <div class="tags">
                                    <div class="tags__label">
                                        <?= i::__("Áreas de atuação") ?> ({{entity.terms.area.lenght}})
                                    </div>
                                    <div class="tags__tagsList">
                                        {{entity.terms.area.join(', ')}}
                                    </div>
                                </div>
                            </div>

                            <div class="view-card__status">

                            </div>
                        </mapas-card>
                    </template>
                </popover>

                <div v-if="editable" class="agent__delete">
                    <!-- remover agente -->
                    <confirm-button @confirm="removeAgent(agent)">
                        <template #button="modal">
                            <mc-icon @click="modal.open()" name="delete"></mc-icon>
                        </template>
                        <template #message="message">
                            <?php i::_e('Remover agente relacionado?') ?>
                        </template>
                    </confirm-button>
                </div>
            </div>
        </div>
        <div class="entity-related-agents__group--actions">
            <select-entity v-if="editable" type="agent" permissions="" @select="addAgent($event)" :query="query" openside="down-right">
                <template #button="{ toggle }">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()">
                        <?php i::_e('Adicionar administrador') ?>
                        <mc-icon name="add"></mc-icon>
                    </button>
                </template>
            </select-entity>
        </div>
    </div>
    <?php $this->applyTemplateHook('entity-related-agents', 'end'); ?>
</div>
<?php $this->applyTemplateHook('entity-related-agents', 'after'); ?>