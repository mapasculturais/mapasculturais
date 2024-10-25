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
    registration-field-persons
 ')
 ?>
<div class="registration-form">
    <?php $this->applyComponentHook("begin") ?>
    <!-- @TODO: remover comentário quando a implementação estiver pronta -->
    <!-- <v1-embed-tool v-if="isValid()" iframe-id="registration-form" route="registrationform" :id="registration.id"></v1-embed-tool> -->
    <form v-if="isValid()" >
        <mc-card v-for="section in sections" class="registration-form__section">
            <template v-if="section.title" #title>{{section.title}}</template>
            <template #content>
                <p>{{section.description}}</p>
                <template v-for="field in section.fields" :key="field.fieldName || field.groupName">
                    <entity-field v-if="field.fieldName" 
                        :entity="registration" 
                        :prop="field.fieldName" 
                        :field-description="field.description" 
                        :max-length="field.maxSize" 
                        :autosave="60000"
                        :max-options="field?.config?.maxOptions !== undefined && field?.config?.maxOptions !== '' ? Number(field.config.maxOptions) : 0"></entity-field>

                    <entity-file v-if="field.groupName" :entity="registration" :groupName="field.groupName" titleModal="<?php i::_e('Adicionar anexo') ?>" :title="field.title" editable></entity-file>

                    <registration-field-persons v-if="field.fieldType == 'persons'" :registration="registration" :prop="field.fieldName"></registration-field-persons>
                </template>
            </template>
        </mc-card>

    </form>

    <div v-else>
        <entity-field v-if="hasCategory && !category" :entity="registration" prop="category"></entity-field><br>
        <entity-field v-if="hasProponentType && !proponentType" :entity="registration" prop="proponentType"></entity-field><br>
        <entity-field v-if="hasRange && !range" :entity="registration" prop="range"></entity-field><br>
    </div>
    <?php $this->applyComponentHook("end") ?>
</div>