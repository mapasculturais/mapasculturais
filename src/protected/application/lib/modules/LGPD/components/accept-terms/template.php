<?php

use MapasCulturais\i;
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

    <tabs class="tabs mapas-terms__content">
        <tab v-for="(term, slug) in terms" :label="term.title" :slug="slug">
            <template #default>
                <mapas-card>
                    <template #content>
                        <div class="term">
                            <div v-html="term.text" class="term__content">
                            </div>
                        </div>
                    </template>

                </mapas-card>
                <div class="btn">
                    <button class="button button--text back" @click="cancel()">Voltar</button>
                    <button class="button button--primary button--md accept" @click="acceptTerm(slug)">{{term.buttonText}}</button>
                 </div>
            </template>
        </tab>
    </tabs>
</div>
<?php $this->applyTemplateHook('accepted-terms', 'after'); ?>