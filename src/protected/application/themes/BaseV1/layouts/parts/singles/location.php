<?php
$lat = $entity->location->latitude; $lng = $entity->location->longitude;
$has_private_location = isset($has_private_location) && $has_private_location
?>
<?php if ( $this->isEditable() || (($has_private_location && $entity->publicLocation && $lat && $lng) || (!$has_private_location && $lat && $lng)) ): ?>
    <?php $this->applyTemplateHook('location','before'); ?>
    <div class="servico clearfix">
        <div class="mapa js-map-container">
            <?php if($this->isEditable()): ?>
                <div class="clearfix js-leaflet-control" data-leaflet-target=".leaflet-top.leaflet-left">
                    <a id ="button-locate-me" class="control-infobox-open hltip botoes-do-mapa" title="<?php \MapasCulturais\i::esc_attr_e("Encontrar minha localização");?>"></a>
                </div>
            <?php endif; ?>
            <div id="single-map-container" class="js-map" data-lat="<?php echo $lat?>" data-lng="<?php echo $lng?>"></div>
            <input type="hidden" id="map-target" data-name="location" class="js-editable" data-edit="location" data-value="<?php echo '[' . $lng . ',' . $lat . ']'; ?>"/>
        </div>
        <!--.mapa-->
        <div class="infos">

            <?php $this->applyTemplateHook('location-info','before'); ?>
            <?php $this->applyTemplateHook('location-info','after'); ?>
            <?php $html = ''; $geoMeta = false; foreach($app->getRegisteredGeoDivisions() as $k => $geo_division): if (!$geo_division->display) continue; $metakey = $geo_division->metakey;?>
                    <?php
                        $html .= ($entity->$metakey) ? '<p>' : '<p style="display:none">';
                        $html .= '<span class="label">' . $geo_division->name . ':</span> <span class="js-geo-division-address" data-metakey="' . $metakey .'">' . $entity->$metakey . '</span></p>';

                        $geoMeta = ($entity->$metakey && !$geoMeta) ? true : $geoMeta;
                    ?>
            <?php endforeach;

                if($geoMeta){ ?>
                    <div class="sobre-info-geo-bt hltip icon icon-arrow-up">
                        <a href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Informações Geográficas");?></a>
                    </div>
                    <div class="sobre-info-geo" style="display:none;">
                        <?php echo $html; ?>
                    </div><!--.sobre-info-geo-->
                <?php }
            ?>
        </div>
        <!--.infos-->
    </div>
    <!--.servico-->
    <?php $this->applyTemplateHook('location','after'); ?>
<?php endif; ?>
