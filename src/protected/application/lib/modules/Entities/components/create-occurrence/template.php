<?php 
use MapasCulturais\i;
$this->import('modal entity-field'); 
?>

<modal title="Inserir ocorrência no evento" classes="create-occurrence" button-label="" >
    <template #default>

        <div class="grid-12">
            <div :class="['col-12', 'create-occurrence__section', {'active' : step==0}]">
                <label class="create-occurrence__section--title"> <?= i::_e('Vincular um espaço para o evento') ?> </label>

                <div class="create-occurrence__section--link-space">
                    <!-- Seletor de entidades - espaços -->
                    <button class="button button--icon button--text-outline"> <mc-icon name="add"></mc-icon> <?= i::_e('Adicionar') ?> </button>

                    <?= i::_e('ou') ?>

                    <!-- create space -->
                    <button class="button button--icon button--primary-outline"> <mc-icon name="add"></mc-icon> <?= i::_e('Crie um novo espaço') ?> </button>
                </div>
            </div>

            <div :class="['col-12', 'create-occurrence__section', {'active' : step==1}]">
                <span class="create-occurrence__section--title"> <?= i::_e('Quando o evento ocorrerá?') ?> </span>

                <div class="grid-12">
                    <div class="col-6 sm:col-12">
                        <!-- <entity-field :entity="entity" label=<?php i::esc_attr_e("Data inicial:")?> ></entity-field> -->
                        <div class="create-occurrence__section--field">
                            <span class="label"><?= i::_e('Data inicial:') ?></span>
                            <input v-model="startsOn" type="date" />
                        </div>
                    </div>
                    <div class="col-6 sm:col-12">
                        <div class="create-occurrence__section--field">
                            <span class="label"><?= i::_e('Data final:') ?></span>
                            <input v-model="until" type="date" />
                        </div>
                    </div>
                </div>     
            </div>

            <div :class="['col-6', 'sm:col-12', 'create-occurrence__section', {'active' : step==2}]">
                <span class="create-occurrence__section--title"> <?= i::_e('Qual o horário do evento?') ?> </span>

                <div class="grid-12">
                    <div class="col-6 sm:col-12">
                        <div class="create-occurrence__section--field">
                            <span class="label"><?= i::_e('Horário inicial:') ?></span>
                            <input v-model="startsAt" type="time" />
                        </div>
                    </div>
                    <div class="col-6 sm:col-12">
                        <div class="create-occurrence__section--field">
                            <span class="label"><?= i::_e('Horário final:') ?></span>
                            <input v-model="endsAt" type="time" />
                        </div>
                    </div>
                </div>  
            </div>

            <div :class="['col-6', 'sm:col-12', 'create-occurrence__section', {'active' : step==3}]">
                <span class="create-occurrence__section--title"> <?= i::_e('Qual a frequência do evento?') ?> </span>

                <div class="create-occurrence__section--fields">
                    <label class="create-occurrence__section--fields-field"> <input v-model="frequency" type="radio" name="frequency" value="once" /> <?= i::_e('uma vez') ?> </label>
                    <label class="create-occurrence__section--fields-field"> <input v-model="frequency" type="radio" name="frequency" value="weekly" /> <?= i::_e('semanal') ?> </label>
                    <label class="create-occurrence__section--fields-field"> <input v-model="frequency" type="radio" name="frequency" value="daily" /> <?= i::_e('todos os dias') ?> </label>
                </div>
            </div>

            <div v-if="frequency=='weekly'" :class="['col-12', 'create-occurrence__section', {'active' : step==3}]">
                <span class="create-occurrence__section--title"> <?= i::_e('Que dias da semana o evento se repete?') ?> </span>

                <div class="create-occurrence__section--fields">
                    <label class="create-occurrence__section--fields-field"><input v-model="day[0]" type="checkbox" name="day[0]"> <?= i::_e('Domingo') ?> </label>
                    <label class="create-occurrence__section--fields-field"><input v-model="day[1]" type="checkbox" name="day[1]"> <?= i::_e('Segunda') ?> </label>
                    <label class="create-occurrence__section--fields-field"><input v-model="day[2]" type="checkbox" name="day[2]"> <?= i::_e('Terça') ?> </label>
                    <label class="create-occurrence__section--fields-field"><input v-model="day[3]" type="checkbox" name="day[3]"> <?= i::_e('Quarta') ?> </label>
                    <label class="create-occurrence__section--fields-field"><input v-model="day[4]" type="checkbox" name="day[4]"> <?= i::_e('Quinta') ?> </label>
                    <label class="create-occurrence__section--fields-field"><input v-model="day[5]" type="checkbox" name="day[5]"> <?= i::_e('Sexta') ?> </label>
                    <label class="create-occurrence__section--fields-field"><input v-model="day[6]" type="checkbox" name="day[6]"> <?= i::_e('Sabado') ?> </label>
                </div>
            </div>

            <div :class="['col-12', 'create-occurrence__section', {'active' : step==4}]">
                <span class="create-occurrence__section--title"> <?= i::_e('Como será a entrada?') ?> </span>
                
                <div class="create-occurrence__section--fields">
                    <div class="grid-12">
                        <div class="col-12">
                            <div class="create-occurrence__section--fields">
                                <span class="label"> <?= i::_e('O evento será gratuito?') ?> </span>
                                <label class="create-occurrence__section--fields-field"> <input v-model="free" type="radio" name="free" :value="true" /> <?= i::_e('Sim') ?> </label>
                                <label class="create-occurrence__section--fields-field"> <input v-model="free" type="radio" name="free" :value="false" /> <?= i::_e('Não') ?> </label>
                            </div>
                        </div>
                        <div class="col-6 sm:col-12" v-if="!free">
                            <div class="create-occurrence__section--field">
                                <span class="label"><?= i::_e('Valor da entrada:') ?></span>
                                <input v-model="price" type="number" />
                            </div>
                        </div>
                        <div class="col-6 sm:col-12">
                            <div class="create-occurrence__section--field">
                                <span class="label"><?= i::_e('Informações adicionais sobre a entrada:') ?></span>
                                <input type="text" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div :class="['col-12', 'create-occurrence__section', {'active' : step==5}]">
                <span class="create-occurrence__section--title"> <?= i::_e('Resumo das informações: ') ?> </span>

                <div class="create-occurrence__section--fields">
                    <div class="grid-12">
                        <div class="col-12">
                            <div class="create-occurrence__section--field">
                                <span class="label"><?= i::_e('Descrição legível de data e horário') ?></span>
                                <div class="auto-description">
                                    <span>{{autoDescription}}</span>
                                    <button class="button button--icon button--sm"> <mc-icon name="copy"></mc-icon> copiar </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="create-occurrence__section--field">
                                <span class="label"><small><?= i::_e('Você pode usar a descrição gerada pelo sistema OU criar uma descrição customizada prenchendo o campo abaixo') ?></small></span>
                                <input v-model="description" type="text" name="description" placeholder="<?= i::_e('Preencha aqui o resumo customizado') ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </template>

    <template #button="modal">
        <button class="button button--primary" @click="modal.open"><?php i::_e('Inserir nova ocorrência') ?></button>
    </template>

    <template #actions="modal">

        <div class="mobile">
            <div class="button-group">
                <button v-if="step==0" class="button button--text" @click="cancel(modal)"><?php i::_e('Cancelar')?></button>
                <button v-if="step>0" class="button button--text" @click="prev()"><?php i::_e('Voltar')?></button>
                <button v-if="step<5" class="button button--primary button--icon" @click="next()"><?php i::_e('Próximo')?><mc-icon name="next"></mc-icon></button>
                <button v-if="step==5" class="button button--primary" @click="create()"><?php i::_e('Concluir')?></button>
            </div>

            <div class="pagination">
                <div :class="['pagination-item', {active:step==0}]"></div>
                <div :class="['pagination-item', {active:step==1}]"></div>
                <div :class="['pagination-item', {active:step==2}]"></div>
                <div :class="['pagination-item', {active:step==3}]"></div>
                <div :class="['pagination-item', {active:step==4}]"></div>
                <div :class="['pagination-item', {active:step==5}]"></div>
            </div>
        </div>

        <div class="desktop">
            <div class="button-group">
                <button class="button button--text" @click="cancel(modal)"><?php i::_e('Cancelar')?></button>
                <button class="button button--primary" @click="create()"><?php i::_e('Inserir ocorrência')?></button>
            </div>
        </div>

    </template>
</modal>