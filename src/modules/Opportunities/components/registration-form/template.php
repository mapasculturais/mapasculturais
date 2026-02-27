<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 use MapasCulturais\i;

 $this->import('
    entity-field
    entity-file
    mc-card
    v1-embed-tool 
    registration-field-address
    registration-field-persons
 ')
 ?>
<div class="registration-form">
    <?php $this->applyComponentHook("begin") ?>
    <div v-if="preview || !isValid">
        <entity-field v-if="hasCategory && (preview || !registration.category)" :entity="registration" prop="category"></entity-field><br>
        <entity-field v-if="hasProponentType && (preview || !registration.proponentType)" :entity="registration" prop="proponentType"></entity-field><br>
        <entity-field v-if="hasRange && (preview || !registration.range)" :entity="registration" prop="range"></entity-field><br>
    </div>
    <form v-if="preview || isValid" >
        <mc-card v-for="section in sections" class="registration-form__section">
            <template v-if="section.title" #title>
                {{section.title}}
                <p v-if="section.description">{{section.description}}</p>
            </template>
            <template #content>
                <template v-for="field in section.fields" :key="field.fieldName || field.groupName">
                    <registration-field-address v-if="showField(field, 'addresses')" 
                        :registration="registration"
                        :disabled="isDisabled(field)"
                        :prop="field.fieldName"></registration-field-address>

                    <registration-field-persons v-if="showField(field, 'persons')" 
                        :registration="registration"
                        :disabled="isDisabled(field)"
                        :prop="field.fieldName"></registration-field-persons>
                        
                    <entity-field v-else-if="field.fieldName && field.fieldType != 'addresses' && field.fieldType != 'persons'"
                        @change="clearFields()" 
                        :entity="registration" 
                        :disabled="isDisabled(field)"
                        :prop="field.fieldName" 
                        :field-description="field.description" 
                        :max-length="field.maxSize || undefined" 
                        :autosave="60000"
                        description-first
                        :max-options="field?.config?.maxOptions !== undefined && field?.config?.maxOptions !== '' ? Number(field.config.maxOptions) : 0"
                        :registration-field-configuration="field"
                        preserve-order></entity-field>

                    <entity-file v-else-if="field.groupName" 
                        :entity="registration"
                        :disabled="isDisabled(field)"
                        :groupName="field.groupName" 
                        titleModal="<?php i::_e('Adicionar anexo') ?>" 
                        :title="field.title" 
                        :description="field.description"
                        editable
                        :required="field.required"
                        :default-file="field?.template"></entity-file>

                </template>
            </template>
        </mc-card>
    </form>
    <?php $this->applyComponentHook("end") ?>
</div>