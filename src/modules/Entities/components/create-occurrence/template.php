<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    create-space
    mc-link
    mc-modal
    select-entity
    mc-datepicker
');
?>
<mc-modal title="<?= i::esc_attr__('Inserir ocorrência no evento')?>" classes="create-occurrence">
    <template #default>
        <div class="grid-12">
            <div :class="['col-12', 'create-occurrence__section', {'active' : step==0}]">
                <template v-if="occurrenceType === 'in-person'">
                    <label v-if="!space" class="create-occurrence__section__title"> <?= i::_e('Vincular um espaço para o evento') ?> </label>
                    <label v-if="space" class="create-occurrence__section__title"> <?= i::_e('Espaço vinculado:') ?> </label>

                    <div v-if="!space" class="create-occurrence__section__link-space">
                        <select-entity type="space" openside="down-right" permissions="" select="name,files.avatar,endereco,location" @select="selectSpace($event)">
                            <template #button="{ toggle }">
                                <button class="button button--icon button--text-outline" @click="toggle()"> <mc-icon name="add"></mc-icon> <?= i::_e('Adicionar') ?> </button>
                            </template>
                        </select-entity>

                        <?= i::_e('ou') ?>

                        <create-space #default="{modal}" @create="selectSpace">
                            <button @click="modal.open()" editable class="button button--icon button--primary-outline"> 
                                <mc-icon name="add"></mc-icon> <?= i::_e('Crie um novo espaço') ?> 
                            </button>
                        </create-space>
                    </div>

                    <div v-if="space" class="create-occurrence__section__link-space space-info">
                        <div class="space-info__space">
                            <div class="space-info__space__title">
                                <mc-icon name="pin"></mc-icon> {{space.name}}
                                <a class="remove" @click="removeSpace()"> 
                                    <mc-icon name="trash"></mc-icon>
                                </a>
                            </div>
                            <div v-if="space.endereco" class="space-info__space__address"> {{space.endereco}} </div>
                            <div v-if="!space.endereco" class="space-info__space__address"> <?= i::_e('Sem endereço') ?> </div>
                        </div>

                        <div class="space-info__new">
                            <select-entity type="space" openside="down-right" @select="selectSpace($event)">
                                <template #button="{ toggle }">
                                    <button class="button button--icon button--primary-outline" @click="toggle()"> <mc-icon name="add"></mc-icon> <?= i::_e('Alterar espaço selecionado') ?> </button>
                                </template>
                            </select-entity>  
                        </div>
                    </div>
                </template>

                <template v-if="occurrenceType === 'virtual'">
                    <label class="create-occurrence__section__title"> <?= i::_e('Links do evento online') ?> </label>
                    
                    <div class="create-occurrence__links">
                        <div v-for="(link, index) in links" :key="index" class="create-occurrence__link-item">
                            <input 
                                type="url" 
                                v-model="links[index]" 
                                placeholder="https://..."
                                class="create-occurrence__link-input"
                            />
                            <button class="button button--icon button--text-danger" @click="removeLink(index)">
                                <mc-icon name="trash"></mc-icon>
                            </button>
                        </div>
                        <button class="button button--icon button--primary-outline create-occurrence__add-link" @click="addLink()">
                            <mc-icon name="add"></mc-icon> <?= i::_e('Adicionar link') ?>
                        </button>
                    </div>
                </template>

                <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['space']">        
                    {{newOccurrence.__validationErrors['space'].join('; ')}}
                </small>
            </div>

            <div :class="['col-6', 'sm:col-12', 'create-occurrence__section', {'active' : step==1}]">
                <span class="create-occurrence__section__title"> <?= i::_e('Qual a frequência do evento?') ?> </span>

                <div class="create-occurrence__section__fields">
                    <label class="create-occurrence__section__fields-field"> <input v-model="frequency" type="radio" name="frequency" value="once" /> <?= i::_e('uma vez') ?> </label>
                    <label class="create-occurrence__section__fields-field"> <input v-model="frequency" type="radio" name="frequency" value="weekly" /> <?= i::_e('semanal') ?> </label>
                    <label class="create-occurrence__section__fields-field"> <input v-model="frequency" type="radio" name="frequency" value="daily" /> <?= i::_e('todos os dias') ?> </label>
                </div>
            </div>

            <div v-if="frequency=='weekly'" :class="['col-12', 'create-occurrence__section', {'active' : step==1}]">
                <span class="create-occurrence__section__title"> <?= i::_e('Que dias da semana o evento se repete?') ?> </span>

                <div class="create-occurrence__section__fields">
                    <label class="create-occurrence__section__fields-field"><input v-model="days[0]" type="checkbox" true-value="on" :false-value="undefined"> <?= i::_e('Domingo') ?> </label>
                    <label class="create-occurrence__section__fields-field"><input v-model="days[1]" type="checkbox" true-value="on" :false-value="undefined"> <?= i::_e('Segunda') ?> </label>
                    <label class="create-occurrence__section__fields-field"><input v-model="days[2]" type="checkbox" true-value="on" :false-value="undefined"> <?= i::_e('Terça') ?> </label>
                    <label class="create-occurrence__section__fields-field"><input v-model="days[3]" type="checkbox" true-value="on" :false-value="undefined"> <?= i::_e('Quarta') ?> </label>
                    <label class="create-occurrence__section__fields-field"><input v-model="days[4]" type="checkbox" true-value="on" :false-value="undefined"> <?= i::_e('Quinta') ?> </label>
                    <label class="create-occurrence__section__fields-field"><input v-model="days[5]" type="checkbox" true-value="on" :false-value="undefined"> <?= i::_e('Sexta') ?> </label>
                    <label class="create-occurrence__section__fields-field"><input v-model="days[6]" type="checkbox" true-value="on" :false-value="undefined"> <?= i::_e('Sabado') ?> </label>
                </div>
                <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['frequency']">        
                    {{newOccurrence.__validationErrors['frequency'].join('; ')}}
                </small>
            </div>

            <div :class="['col-12', 'create-occurrence__section', {'active' : step==2}]">
                <span class="create-occurrence__section__title"> <?= i::_e('Quando o evento ocorrerá?') ?> </span>

                <div :class="['create_occurrence__datepicker', {'grid-12': frequency=='once'}]">
                    <div v-if="frequency=='once'" class="col-6 sm:col-12">
                        <div class="create-occurrence__section__field field">
                            <span class="label"><?= i::_e('Data inicial:') ?></span>   

                            <mc-datepicker 
                                v-model:modelValue="dateRange.start"
                                fieldType="date"
                                locale="locale">
                            </mc-datepicker>
                        </div>

                        <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['startsOn']">        
                            {{newOccurrence.__validationErrors['startsOn'].join('; ')}}
                        </small>
                    </div>
                    
                    <div v-if="frequency!=='once'" class="grid-12">
                        <div class="col-6 sm:col-12">
                            <div class="create-occurrence__section__field field">
                                <span class="label"><?= i::_e('Data inicial:') ?></span> 

                                <mc-datepicker 
                                    v-model:modelValue="dateRange.start"
                                    fieldType="date"
                                    locale="locale"                                    
                                    >    
                                </mc-datepicker>

                                <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['until']">        
                                    {{newOccurrence.__validationErrors['until'].join('; ')}}
                                </small>
                            </div>
                        </div>
                        <div class="col-6 sm:col-12">
                            <div class="create-occurrence__section__field field">
                                <span class="label"><?= i::_e('Data final:') ?></span>

                                <mc-datepicker 
                                    v-model:modelValue="dateRange.end"
                                    fieldType="date"
                                    locale="locale"
                                    >
                                </mc-datepicker>

                                <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['endsOn']">        
                                    {{newOccurrence.__validationErrors['endsOn'].join('; ')}}
                                </small>
                            </div>
                        </div>
                        
                    </div>
                </div>     
            </div>

            <div :class="['col-12', 'create-occurrence__section', {'active' : step==3}]">
                <span class="create-occurrence__section__title"> <?= i::_e('Qual o horário do evento?') ?> </span>

                <div class="grid-12">
                    <div class="col-6 sm:col-12">                        
                        <div class="create-occurrence__section__field field">
                            <span class="label"><?= i::_e('Horário inicial:') ?></span>
                            
                            <mc-datepicker 
                                v-model:modelValue="startsAt"
                                fieldType="time"
                                locale="locale"                               
                                >
                            </mc-datepicker>

                            <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['startsAt']">        
                                {{newOccurrence.__validationErrors['startsAt'].join('; ')}}
                            </small>
                        </div>
                    </div>

                    <div class="col-6 sm:col-12">
                        <div class="create-occurrence__section__field field">
                            <span class="label"><?= i::_e('Horário final:') ?></span>
                            
                            <mc-datepicker 
                                v-model:modelValue="endsAt"
                                fieldType="time"
                                locale="locale"
                                >
                            </mc-datepicker>

                            <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['endsAt']">        
                                {{newOccurrence.__validationErrors['endsAt'].join('; ')}}
                            </small>                            
                        </div>
                    </div>

                </div>  
            </div>

            <div :class="['col-12', 'create-occurrence__section', {'active' : step==4}]">
                <span class="create-occurrence__section__title"> <?= i::_e('Como será a entrada?') ?> </span>
                
                <div class="create-occurrence__section__fields">
                    <div class="grid-12">
                        <div class="col-12">
                            <div class="create-occurrence__section__fields">
                                <span class="label"> <?= i::_e('O evento será gratuito?') ?> </span>
                                <label class="create-occurrence__section__fields-field"> <input v-model="free" type="radio" name="free" :value="true" /> <?= i::_e('Sim') ?> </label>
                                <label class="create-occurrence__section__fields-field"> <input v-model="free" type="radio" name="free" :value="false" /> <?= i::_e('Não') ?> </label>
                            </div>
                        </div>

                        <div class="col-6 sm:col-12" v-if="!free">
                            <div class="create-occurrence__section__field">
                                <span class="label"><?= i::_e('Valor da entrada:') ?></span>
                                <input type="text" @input="priceMask" v-model="price" />
                                <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['price']">        
                                    {{newOccurrence.__validationErrors['price'].join('; ')}}
                                </small>  
                            </div>
                        </div>

                        <div class="col-6 sm:col-12 create-occurrence__price-info">
                            <div class="create-occurrence__section__field">
                                <span class="label"><?= i::_e('Informações adicionais sobre a entrada:') ?></span>
                                <textarea v-model="priceInfo" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div :class="['col-12', 'create-occurrence__section', {'active' : step==5}]">
                <span class="create-occurrence__section__title"> <?= i::_e('Resumo das informações: ') ?> </span>

                <div class="create-occurrence__section__fields">
                    <div class="grid-12">
                        <div class="col-12">
                            <div class="create-occurrence__section__field">
                                <input v-model="description" type="text" name="description" placeholder="<?= i::_e('Preencha aqui o resumo customizado') ?>" />
                                <small class="field__error" v-if="newOccurrence && newOccurrence.__validationErrors && newOccurrence.__validationErrors['description']">        
                                    {{newOccurrence.__validationErrors['description'].join('; ')}}
                                </small>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </template>

    <template #button="modal">
        <button class="button button--primary" @click="modal.open">{{buttonLabel}}</button>
    </template>

    <template #actions="modal">
        <div class="create-occurrence__action-mobile">
            <div class="button-group">
                <button v-if="step==0" class="button button--text" @click="cancel(modal)"><?php i::_e('Cancelar')?></button>
                <button v-if="step>0" class="button button--text button--icon button--icon-left" @click="prev()"><?php i::_e('Voltar')?><mc-icon name="previous"></mc-icon></button>
                <button v-if="step<5" class="button button--primary button--icon" @click="next()"><?php i::_e('Próximo')?><mc-icon name="next"></mc-icon></button>
                <button v-if="step==5" class="button button--primary" @click="create(modal)"><?php i::_e('Concluir')?></button>
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

        <div class="create-occurrence__action-desktop">
            <div class="button-group">
                <button class="button button--text" @click="cancel(modal)"><?php i::_e('Cancelar')?></button>
                <button class="button button--primary" @click="create(modal)"><?php i::_e('Inserir ocorrência')?></button>
            </div>
        </div>
    </template>
</mc-modal>
