<?php 
use MapasCulturais\i; 
$app = MapasCulturais\App::i();
?>
<input ng-required="requiredField(field)" ng-model="entity[fieldName]" ng-blur="saveField(field, entity[fieldName])" type="url" class="form-control" placeholder="<?php echo sprintf(i::__('Ex: %s'), $app->baseUrl); ?>">