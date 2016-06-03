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
                    <a id ="button-locate-me" class="control-infobox-open hltip botoes-do-mapa" title="Encontrar mi localización"></a>
                </div>
            <?php endif; ?>
            <div id="single-map-container" class="js-map" data-lat="<?php echo $lat?>" data-lng="<?php echo $lng?>"></div>
            <input type="hidden" id="map-target" data-name="location" class="js-editable" data-edit="location" data-value="<?php echo '[' . $lng . ',' . $lat . ']'; ?>"/>
        </div>
        <!--.mapa-->
        <div class="infos">
            <input type="hidden" class="js-editable" id="endereco" data-edit="endereco" data-original-title="Dirección" data-emptytext="Agregue la dirección" data-showButtons="bottom" value="<?php echo $entity->endereco ?>" data-value="<?php echo $entity->endereco ?>">
            <p class="endereco"><span class="label"><strong>DIRECCIÓN</strong></span> <span class="js-endereco"><?php echo $entity->endereco ?></span></p>
            <p><span class="label">Código Postal:</span> <span class="js-editable js-mask-cep" id="En_CEP" data-edit="En_CEP" data-original-title="Cód. Postal" data-emptytext="Agregue el Código Postal" data-showButtons="bottom"><?php echo $entity->En_CEP ?></span> <a href="http://www.correo.com.uy/index.asp?codPag=codPost&switchMapa=codPost" target="_blank"> <input type="button" name="boton" value="Ver C.P" style="width:70px; height:25px" /> </a> </p>
            <p><span class="label">Calle:</span> <span class="js-editable" id="En_Nome_Logradouro" data-edit="En_Nome_Logradouro" data-original-title="Calle" data-emptytext="Agregue la calle" data-showButtons="bottom"><?php echo $entity->En_Nome_Logradouro ?></span></p>
            <p><span class="label">Número:</span> <span class="js-editable" id="En_Num" data-edit="En_Num" data-original-title="Número" data-emptytext="Agregue el Número" data-showButtons="bottom"><?php echo $entity->En_Num ?></span></p>
            <p><span class="label">Ciudad:</span> <span class="js-editable" id="En_Municipio" data-edit="En_Municipio" data-original-title="Ciudad" data-emptytext="Agregue la Ciudad" data-showButtons="bottom"><?php echo $entity->En_Municipio ?></span></p>
            <p><span class="label">Departamento:</span> <span class="js-editable" id="En_Estado" data-edit="En_Estado" data-original-title="Departamento" data-emptytext="Agregue el Departamento" data-showButtons="bottom"><?php echo $entity->En_Estado ?></span></p>
            <p style="visibility:hidden"><span class="label">Complemento:</span> <span class="js-editable" id="En_Complemento" data-edit="En_Complemento" data-original-title="Complemento" data-emptytext="Agregue un complemento" data-showButtons="bottom"><?php echo $entity->En_Complemento ?></span></p> 
            <p style="visibility:hidden"><span class="label">Barrio:</span> <span class="js-editable" id="En_Bairro" data-edit="En_Bairro" data-original-title="Barrio" data-emptytext="Agregue el Barrio" data-showButtons="bottom"><?php echo $entity->En_Bairro ?></span></p>
            <?php if($has_private_location && $this->isEditable()): ?>
                <p class="privado">
                    <span class="icon icon-private-info"></span><span class="label">Localización:</span>
                    <span class="js-editable clear" data-edit="publicLocation" data-type="select" data-showbuttons="false"
                        data-value="<?php echo $entity->publicLocation ? '1' : '0';?>"
                        data-source="[{value: 1, text: 'Pública'},{value: 0, text:'Privada'}]">
                    </span>
                </p>
            <?php endif; ?>

            <?php foreach($app->getRegisteredGeoDivisions() as $geo_division): $metakey = $geo_division->metakey; ?>
                <p <?php if(!$entity->$metakey) { echo 'style="display:none"'; }?>>
                    <span class="label"><?php echo $geo_division->name ?>:</span> <span class="js-geo-division-address" data-metakey="<?php echo $metakey ?>"><?php echo $entity->$metakey; ?></span>
                </p>
            <?php endforeach; ?>
        </div>
        <!--.infos-->
    </div>
    <!--.servico-->
    <?php $this->applyTemplateHook('location','after'); ?>
<?php endif; ?>
