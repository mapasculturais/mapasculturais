<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 use MapasCulturais\i;

 $this->import('
    entity-field
    v1-embed-tool
 ')
 ?>
<div class="registration-form">
    <?php $this->applyComponentHook("begin") ?>
    <v1-embed-tool v-if="isValid()" iframe-id="registration-form" route="registrationform" :id="registration.id"></v1-embed-tool>
    <div v-else>
        <entity-field v-if="hasCategory && !category" :entity="registration" prop="category"></entity-field><br>
        <entity-field v-if="hasProponentType && !proponentType" :entity="registration" prop="proponentType"></entity-field><br>
        <entity-field v-if="hasRange && !range" :entity="registration" prop="range"></entity-field><br>
    </div>
    <?php $this->applyComponentHook("end") ?>
</div>