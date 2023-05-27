<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-entities
    registration-card
    tabs
');
?>
<tabs :class="{'hasDrafts': this.totalDrafts>0}" @changed="changed($event)">
    <tab label="<?= i::_e('Não enviadas') ?>" slug="notSent" name="tem">
        <mc-entities name="registrationsList" type="registration" endpoint="find" :query="query" :order="query['@order']" select="number,category,createTimestamp,sentTimestamp,owner.{name,files.avatar},opportunity.{name,files.avatar,isOpportunityPhase,parent.{name,files.avatar}}">
            <template #header="{entities}">
                <div class="registrations__filter">
                    <form class="form" @submit="entities.refresh(); $event.preventDefault();">
                        <div class="search">
                            <input type="text" v-model="entities.query['@keyword']" class="input" @input="entities.refresh();" />
                            <button class="button button--icon">
                                <mc-icon name="search"></mc-icon>
                            </button>
                        </div>
                        <select class="order primary__border-solid" v-model="query['@order']" @change="entities.refresh();">
                            <option value="owner.name ASC"><?= i::__('ordem alfabética') ?></option>
                            <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                            <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
                        </select>
                    </form>
                </div>
            </template>
            <template #default="{entities}">
                <div class="registrations__list">
                    <registration-card v-for="registration in entities" :entity="registration" pictureCard>
                        <template #button="{entity}">
                            <a class="button button--md button--primary button--icon" :href="entity.singleUrl"> <?= i::__("Continuar") ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
                        </template>
                    </registration-card>
                </div>
            </template>
        </mc-entities>
    </tab>
    <tab label="<?= i::_e('Enviadas') ?>" class="tabs_sent" slug="sent">
        <mc-entities name="registrationsList" type="registration" endpoint="find" :query="query" :order="query['@order']" select="name,number,category,createTimestamp,sentTimestamp,owner.{name,files.avatar},opportunity.{name,files.avatar,isOpportunityPhase,parent.{name,files.avatar}}">            
            <template #header="{entities}">
                <div class="registrations__filter">
                    <form class="form" @submit="entities.refresh(); $event.preventDefault();">
                        <div class="search">
                            <input type="text" v-model="entities.query['@keyword']" class="input" @input="entities.refresh();" />
                            <button class="button button--icon" type="submit">
                                <mc-icon name="search"></mc-icon>
                            </button>
                        </div>
                        <select class="order primary__border-solid" v-model="query['@order']" @change="entities.refresh();">
                            <option value="owner.name ASC"><?= i::__('ordem alfabética') ?></option>
                            <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                            <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
                        </select>
                    </form>
                    <mc-alert type="warning" :state="showAlert" closeButton>
                        <?= i::__('Você tem inscrições não finalizadas. Acesse a aba')?> <strong><?= i::__('Não Enviadas') ?></strong> <?= i::__('para visualizar.') ?>
                    </mc-alert>
                </div>
            </template>
            <template #default="{entities}">
                <div class="registrations__list">
                    <registration-card v-for="registration in entities" :entity="registration" picture-card></registration-card>
                </div>
            </template>
        </mc-entities>
    </tab>
</tabs>