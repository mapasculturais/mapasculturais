<?php $lat = $entity->location->latitude; $lng = $entity->location->longitude; ?>
<?php if ( $this->isEditable() || ($entity->publicLocation && $lat && $lng) ): ?>
    <div class="servico clearfix">
        <div class="mapa js-map-container">
            <?php if($this->isEditable()): ?>
                <div class="clearfix js-leaflet-control" data-leaflet-target=".leaflet-top.leaflet-left">
                    <a id ="button-locate-me" class="control-infobox-open hltip botoes-do-mapa" title="Encontrar minha localização"></a>
                </div>
            <?php endif; ?>
            <div id="single-map-container" class="js-map" data-lat="<?php echo $lat?>" data-lng="<?php echo $lng?>"></div>
            <input type="hidden" id="map-target" data-name="location" class="js-editable" data-edit="location" data-value="<?php echo '[' . $lng . ',' . $lat . ']'; ?>"/>
        </div>
        <!--.mapa-->
        <div class="infos">
            <input type="hidden" class="js-editable" id="endereco" data-edit="endereco" data-original-title="Endereço" data-emptytext="Insira o endereço" data-showButtons="bottom" value="<?php echo $entity->endereco ?>" data-value="<?php echo $entity->endereco ?>">
            <p class="endereco"><span class="label">Endereço:</span> <span class="js-endereco"><?php echo $entity->endereco ?></span></p>
            <p><span class="label">CEP:</span> <span class="js-editable" id="En_CEP" data-edit="En_CEP" data-original-title="CEP" data-emptytext="Insira o CEP" data-showButtons="bottom"><?php echo $entity->En_CEP ?></span></p>
            <p><span class="label">Logradouro:</span> <span class="js-editable" id="En_Nome_Logradouro" data-edit="En_Nome_Logradouro" data-original-title="Logradouro" data-emptytext="Insira o logradouro" data-showButtons="bottom"><?php echo $entity->En_Nome_Logradouro ?></span></p>
            <p><span class="label">Número:</span> <span class="js-editable" id="En_Num" data-edit="En_Num" data-original-title="Número" data-emptytext="Insira o Número" data-showButtons="bottom"><?php echo $entity->En_Num ?></span></p>
            <p><span class="label">Complemento:</span> <span class="js-editable" id="En_Complemento" data-edit="En_Complemento" data-original-title="Complemento" data-emptytext="Insira um complemento" data-showButtons="bottom"><?php echo $entity->En_Complemento ?></span></p>
            <p><span class="label">Bairro:</span> <span class="js-editable" id="En_Bairro" data-edit="En_Bairro" data-original-title="Bairro" data-emptytext="Insira o Bairro" data-showButtons="bottom"><?php echo $entity->En_Bairro ?></span></p>
            <p><span class="label">Município:</span> <span class="js-editable" id="En_Municipio" data-edit="En_Municipio" data-original-title="Município" data-emptytext="Insira o Município" data-showButtons="bottom"><?php echo $entity->En_Municipio ?></span></p>
            <p><span class="label">Estado:</span> <span class="js-editable" id="En_Estado" data-edit="En_Estado" data-original-title="Estado" data-emptytext="Insira o Estado" data-showButtons="bottom"><?php echo $entity->En_Estado ?></span></p>
            <?php if(isset($has_private_location) && $has_private_location && $this->isEditable()): ?>
                <p class="privado">
                    <span class="icon icon-private-info"></span><span class="label">Localização:</span>
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
<?php endif; ?>