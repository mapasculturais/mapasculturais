<?php
use MapasCulturais\i;
$this->import('
    confirm-button
');
?>

<mapas-card>
    <div class="evaluation-step grid-12">

        <entity-field :entity="entity" prop="evaluationFrom" classes="col-6 sm:col-12" :min="getMinDate(entity.__objectType, currentIndex)" :max="getMaxDate(entity.__objectType, currentIndex)"></entity-field>
        <entity-field :entity="entity" prop="evaluationTo" classes="col-6 sm:col-12" :min="getMinDate(entity.__objectType, currentIndex)" :max="getMaxDate(entity.__objectType, currentIndex)"></entity-field>
        <div class="evaluation-box col-12">
            <div class="evaluation-box__line">

            </div>
            <h2 class="evaluation-box__title"><?= i::__("Configuração da avaliação") ?></h2>
            <span class="evaluation-box__content"><?= i::__("A avaliação simplificada consiste num select box com os status possíveis para uma inscrição.") ?></span>
        </div>
        <div class="evaluation-simple col-12">
            <h3 class="evaluation-simple__title"><?= i::__("Comissão de avaliação simplificada") ?></h3>
            <span class="evaluation-simple__text"><?= i::__("Defina os agentes que serão avaliadores desta fase.") ?></span>
        </div>
        <div class="evaluation-open col-12">
            <button class="evaluation-open__button button--primary button"><mc-icon name="add"></mc-icon><label><?= i::__("Adicionar pessoa avaliadora") ?></label></button>
        </div>
        <div class="evaluation-view col-12">
            <h2 class="evaluation-view__title"><?= i::__("Configurar campos visíveis para os avaliadores") ?></h2>
            <span class="evaluation-view__text"><?= i::__("Defina quais campos serão habilitados para avaliação.") ?></span>
        </div>
        <div class="evaluation-step__btn col-12">
            <button class="evaluation-step__btn--secondary  button--secondarylight button"><?= i::__("Abrir lista de campos") ?></button>
        </div>
        <div class="evaluation-text col-12">
            <h3><?= i::__("Adicionar textos explicativos das avaliações") ?></h3>
        </div>
        <div class="evaluation-config col-12 field">
            <label> <?= i::__("Texto configuração geral") ?>
            </label>
            <textarea v-model="infos['general']" class="evaluation-config__area" rows="10"></textarea>
        </div>
        <div class="col-6 sm:col-12 field" v-for="category in categories">
            <label> {{ category }}
                <textarea v-model="infos[category]" style="width: 100%" rows="10"></textarea>
            </label>
        </div>
        <div class="config-phase__line-bottom col-12"></div>
        <div class="phase-delete col-6" v-if="!entity.isLastPhase && !entity.isFirstPhase">
            <confirm-button message="Confirma a execução da ação?" @confirm="deletePhase($event, entity, currentIndex)">
                <template #button="modal">
                    <a class="phase-delete__trash" @click="modal.open()">
                        <mc-icon name="trash"></mc-icon>
                        <label class="phase-delete__label">{{ text('excluir_fase_avaliacao') }}</label>
                    </a>
                </template>
            </confirm-button>
        </div>
        <div class="phase-delete col-6">
            <a @click="entity.save()" class="phase-delete__trash " href="#"><mc-icon name="upload"></mc-icon><label class="phase-delete__label"><?= i::__("Salvar") ?></label></a>
        </div>
    </div>
</mapas-card>
