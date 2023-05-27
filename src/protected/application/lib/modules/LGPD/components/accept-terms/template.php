<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-loading
    mc-tab
    mc-tabs
    user-accepted-terms
');
?>
<?php $this->applyTemplateHook('accepted-terms', 'before'); ?>
<div class="mapas-terms">

    <header class="mapas-terms__header">
        <div class="mapas-terms__header-title">
            <div class="title">
                <div class="title__title"> <?= i::_e('Termos e condições') ?> </div>
            </div>
        </div>
    </header>

    <mc-card>
        <mc-loading :condition="loading"><?= i::__('Salvando aceite dos termos...') ?></mc-loading>
    </mc-card>

    <mc-tabs v-if="!loading" class="tabs mapas-terms__content" :defaultTab="step" iconPosition="right">
        <mc-tab v-for="(term, slug) in terms" :label="term.title" :slug="slug" :icon="showIconAccepted(term.md5)">
            <template #default>
                <mc-card>
                    <template #content>
                        <div class="term">
                            <div v-html="term.text" class="term__content">
                            </div>
                        </div>
                    </template>
                </mc-card>
                <div v-if="showButton(term.md5)"  class="btn">
                    <button class="button button--text back" @click="cancel()">Voltar</button>
                    <button class="button button--primary button--md accept" @click="acceptTerm(slug,term.md5)">{{term.buttonText}}</button>
                </div>

                <mc-card v-if="user && !showButton(term.md5)">
                    <div>
                        <user-accepted-terms :user="user" :onlyTerm="slug"></user-accepted-terms>
                    </div>
                </mc-card>

            </template>
        </mc-tab>
    </mc-tabs>
</div>
<?php $this->applyTemplateHook('accepted-terms', 'after'); ?>