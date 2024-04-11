<?php

use MapasCulturais\i; ?>
<p ng-if="data.entity.object.registrationRanges">
    <small><?php i::_e("Selecione em quais Faixas este campo é utilizado"); ?>:</small><br>
    <label class="checkbox-label">
        <input type="checkbox" onclick="if (!this.checked) return false" ng-click="data.newFieldConfiguration.registrationRanges = []" ng-checked="allRanges(data.newFieldConfiguration)"> <?php i::_e("Todas"); ?>
    </label>
    <label class="checkbox-label" ng-repeat="range in data.entity.object.registrationRanges">
        <input type="checkbox" checklist-model="data.newFieldConfiguration.registrationRanges" checklist-value="range.label"> {{range.label}}
    </label>
</p>

<p ng-if="data.entity.object.registrationProponentTypes">
    <small><?php i::_e("Selecione em quais Tipos do preponente este campo é utilizado"); ?>:</small><br>
    <label class="checkbox-label">
        <input type="checkbox" onclick="if (!this.checked) return false" ng-click="data.newFieldConfiguration.proponentTypes = []" ng-checked="allProponentTypes(data.newFieldConfiguration)"> <?php i::_e("Todas"); ?>
    </label>
    <label class="checkbox-label" ng-repeat="proponent in data.entity.object.registrationProponentTypes">
        <input type="checkbox" checklist-model="data.newFieldConfiguration.proponentTypes" checklist-value="proponent"> {{proponent}}
    </label>
</p>