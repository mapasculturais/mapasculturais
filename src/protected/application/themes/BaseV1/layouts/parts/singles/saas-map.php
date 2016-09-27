<?php $editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';?>
<div id="mapa" class="aba-content">
    <?php if($this->isEditable() || $entity->latitude): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"latitude") && $editEntity? 'required': '');?>">Latitude: </span>
            <span class="js-editable" data-edit="latitude" data-original-title="Latitude" data-emptytext="Ex.: 40.7143528"><?php echo $entity->latitude; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->longitude): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"logintude") && $editEntity? 'required': '');?>">Longitude: </span>
            <span class="js-editable" data-edit="longitude" data-original-title="longitude" data-emptytext="Ex.: 41 24.2028"><?php echo $entity->longitude; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable()): ?>
        <p class="tip">
            Para saber como obter coordenadas de latitude e longitude, visite: <a href="https://support.google.com/maps/answer/18539?hl=pt-BR" title="Página de suporte do Google Maps" target="_blank">Ajuda Google Maps.</a>
        </p>
    <?php endif;?>

    <?php if($this->isEditable() || $entity->zoom_default): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_default") && $editEntity? 'required': '');?>">Zoom Padrão: </span>
            <span class="js-editable" data-edit="zoom_default" data-original-title="Zoom Padrão" data-emptytext="Zoom padrão do mapa"><?php echo $entity->zoom_default;?></span>
        </p>
    <?php endif;?>

    <?php if($this->isEditable() || $entity->zoom_approximate): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_approximate") && $editEntity? 'required': '');?>">Zoom Aproximado: </span>
            <span class="js-editable" data-edit="zoom_approximate" data-original-title="Zoom Aproximado" data-emptytext="Zoom aproximado do mapa"><?php echo $entity->zoom_approximate;?></span>
        </p>
    <?php endif;?>

    <?php if($this->isEditable() || $entity->zoom_precise): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_preciso") && $editEntity? 'required': '');?>">Zoom Preciso: </span>
            <span class="js-editable" data-edit="zoom_precise" data-original-title="Zoom Preciso" data-emptytext="Zoom preciso do mapa"><?php echo $entity->zoom_precise;?></span>
        </p>
    <?php endif;?>

    <?php if($this->isEditable() || $entity->zoom_min): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_min") && $editEntity? 'required': '');?>">Zoom Mínimo: </span>
            <span class="js-editable" data-edit="zoom_min" data-original-title="Zoom Mínimo" data-emptytext="Zoom mínimo do mapa"><?php echo $entity->zoom_min;?></span>
        </p>
    <?php endif;?>

    <?php if($this->isEditable() || $entity->zoom_max): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"zoom_maximo") && $editEntity? 'required': '');?>">Zoom Máximo: </span>
            <span class="js-editable" data-edit="zoom_max" data-original-title="Zoom Máximo" data-emptytext="Zoom máximo do mapa"><?php echo $entity->zoom_max;?></span>
        </p>
    <?php endif;?>

    <?php $this->part('saas-filter', array('entity'=>$entity)); ?>
</div>
