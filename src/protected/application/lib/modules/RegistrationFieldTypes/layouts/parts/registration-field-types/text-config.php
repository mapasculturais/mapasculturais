<div ng-if="field.fieldType === 'text'">
    <input type="text" ng-model="field.maxSize" placeholder="<?php \MapasCulturais\i::esc_attr_e("Quantidade máxima de caracteres");?>"/>
    <input type="text" ng-model="field.mask" placeholder="<?php \MapasCulturais\i::esc_attr_e("Máscara");?>"/>
    <input type="text" ng-model="field.maskOptions" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de Máscara");?>"/>
</div>