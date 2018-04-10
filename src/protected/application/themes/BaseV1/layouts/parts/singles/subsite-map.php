<?php
use MapasCulturais\i;

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";


?>
<div id="mapa" class="aba-content">
    <p class="alert info"><?php i::_e('Nesta seção você indica a posição inicial do mapa, ou seja, quando o usuário abrir o mapa ele visualizará as informações nessa posição já estabelecida podendo fazer um zoom mínimo e máximo. Informe o endereço/nome município/nome estado no campo “Digite um endereço”.'); ?></p>
    <div>
        <input placeholder="<?php i::_e('Busque pelo nome da cidade ou estado') ?>" class="js-subsite-map-search--input" style="width:300px"> 
        <buttom class="btn btn-primary js-subsite-map-search--button"><?php i::_e('buscar') ?></button>
    </div>
    
    <div id="subsite-map" style="width:100%; height:500px">
    </div>

    <p>
        <span class="label <?php echo ($entity->isPropertyRequired($entity,"latitude") && $editEntity? 'required': '');?>"><?php i::_e('Latitude:') ?> </span>
        <span id="latitude" class="js-editable" data-disabled="true" data-edit="latitude" data-original-title="<?php i::esc_attr_e('Latitude') ?>" data-emptytext="<?php i::esc_attr_e('Ex.: 40.7143528'); ?>"><?php echo $entity->latitude; ?></span>

        <span class="label <?php echo ($entity->isPropertyRequired($entity,"logintude") && $editEntity? 'required': '');?>"><?php i::_e('Longitude:') ?> </span>
        <span id="longitude" class="js-editable" data-disabled="true" data-edit="longitude" data-original-title="<?php i::esc_attr_e('longitude')?>" data-emptytext="<?php i::esc_attr_e('Ex.: 24.2028') ?>"><?php echo $entity->longitude; ?></span>

        <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_default") && $editEntity? 'required': '');?>"><?php i::_e('Zoom Padrão:') ?> </span>
        <span id="zoom_default" class="js-editable" data-disabled="true" data-edit="zoom_default" data-original-title="<?php i::esc_attr_e('Zoom Padrão');?>" data-emptytext="<?php i::esc_attr_e('Zoom padrão do mapa') ?>"><?php echo $entity->zoom_default;?></span>
    </p>


    <p>
        <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_min") && $editEntity? 'required': '');?>"><?php i::_e('Zoom Mínimo:')?> </span>
        <span class="js-editable" data-edit="zoom_min" data-original-title="<?php i::esc_attr_e('Zoom Mínimo') ?>" data-emptytext="<?php i::esc_attr_e('Zoom mínimo do mapa') ?>"><?php echo $entity->zoom_min;?></span>
    </p>
    <p>
        <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_maximo") && $editEntity? 'required': '');?>"><?php i::_e('Zoom Máximo:') ?> </span>
        <span class="js-editable" data-edit="zoom_max" data-original-title="<?php i::esc_attr_e('Zoom Máximo') ?>" data-emptytext="<?php i::esc_attr_e('Zoom máximo do mapa') ?>"><?php echo $entity->zoom_max;?></span>
    </p>

</div>
