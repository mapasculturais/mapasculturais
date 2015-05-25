<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "Entity";

$this->addEntityToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('area');

    $this->addTaxonoyTermsToJs('tag');
}

$this->includeMapAssets();

$this->includeAngularEntityAssets($entity);

$child_entity_request = isset($child_entity_request) ? $child_entity_request : null;

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content space">
    <header class="main-content-header">
        <div
            <?php if ($header = $entity->getFile('header')): ?>
                style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="header-image js-imagem-do-header"
            <?php elseif($this->isEditable()): ?>
                class="header-image js-imagem-do-header"
            <?php endif; ?>
            >
            <?php if ($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-header" href="#">Editar</a>
                <div id="editbox-change-header" class="js-editbox mc-bottom" title="Editar Imagem da Capa">
                    <?php $this->ajaxUploader($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
                </div>
            <?php endif; ?>
        </div>
        <!--.header-image-->
        <div class="header-content">
            <?php if($avatar = $entity->avatar): ?>
                <div class="avatar com-imagem">
                    <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
                <?php else: ?>
                    <div class="avatar">
                        <img class="js-avatar-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
            <?php endif; ?>
                <?php if($this->isEditable()): ?>
                    <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-avatar" href="#">Editar</a>
                    <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
                        <?php $this->ajaxUploader($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
                    </div>
                <?php endif; ?>
                <!-- pro responsivo!!! -->
                <?php if($entity->isVerified): ?>
                    <a class="verified-seal hltip active" title="Este <?php echo $entity->entityType ?> é verificado." href="#"></a>
                <?php endif; ?>
            </div>
            <!--.avatar-->
            <div class="entity-type space-type">
                <div class="icon icon-space"></div>
                <a href="#" class='js-editable-type' data-original-title="Tipo" data-emptytext="Selecione um tipo" data-entity='space' data-value='<?php echo $entity->type ?>'><?php echo $entity->type? $entity->type->name : ''; ?></a>
            </div>
            <?php $this->part('entity-parent', ['entity' => $entity, 'child_entity_request' => $child_entity_request]) ?>

            <h2><span class="js-editable" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
        </div>
    </header>
    <ul class="abas clearfix">
        <li class="active"><a href="#sobre">Sobre</a></li>
        <li><a href="#agenda">Agenda</a></li>
    </ul>
    <div id="sobre" class="aba-content">
        <div class="ficha-spcultura">
            <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                <div class="alert warning">O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui <?php echo strlen($entity->shortDescription) ?> caracteres. Você deve alterar seu texto ou este será cortado ao salvar.</div>
            <?php endif; ?>

            <p>
                <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
            </p>
            <div class="servico">
                <?php if($this->isEditable()): ?>
                    <p style="display:none" class="privado"><span class="icon icon-private-info"></span>Virtual ou Físico? (se for virtual a localização não é obrigatória)</p>
                <?php endif; ?>

                <?php if($this->isEditable() || $entity->acessibilidade): ?>
                <p><span class="label">Acessibilidade: </span><span class="js-editable" data-edit="acessibilidade" data-original-title="Acessibilidade"><?php echo $entity->acessibilidade; ?></span></p>
                <?php endif; ?>

                <?php if($this->isEditable() || $entity->capacidade): ?>
                <p><span class="label">Capacidade: </span><span class="js-editable" data-edit="capacidade" data-original-title="Capacidade" data-emptytext="Especifique a capacidade do espaço"><?php echo $entity->capacidade; ?></span></p>
                <?php endif; ?>

                <?php if($this->isEditable() || $entity->horario): ?>
                <p><span class="label">Horário de funcionamento: </span><span class="js-editable" data-edit="horario" data-original-title="Horário de Funcionamento" data-emptytext="Insira o horário de abertura e fechamento"><?php echo $entity->horario; ?></span></p>
                <?php endif; ?>

                <?php if($this->isEditable() || $entity->site): ?>
                    <p><span class="label">Site:</span>
                    <?php if($this->isEditable()): ?>
                        <span class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span></p>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if($this->isEditable() || $entity->emailPublico): ?>
                <p><span class="label">Email Público:</span> <span class="js-editable" data-edit="emailPublico" data-original-title="Email Público" data-emptytext="Insira um email que será exibido publicamente"><?php echo $entity->emailPublico; ?></span></p>
                <?php endif; ?>

                <?php if($this->isEditable()):?>
                    <p class="privado"><span class="icon icon-private-info"></span><span class="label">Email Privado:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="Email Privado" data-emptytext="Insira um email que não será exibido publicamente"><?php echo $entity->emailPrivado; ?></span></p>
                <?php endif; ?>

                <?php if($this->isEditable() || $entity->telefonePublico): ?>
                <p><span class="label">Telefone Público:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Telefone Público" data-emptytext="Insira um telefone que será exibido publicamente"><?php echo $entity->telefonePublico; ?></span></p>
                <?php endif; ?>

                <?php if($this->isEditable()):?>
                    <p class="privado"><span class="icon icon-private-info"></span><span class="label">Telefone Privado 1:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone1; ?></span></p>
                    <p class="privado"><span class="icon icon-private-info"></span><span class="label">Telefone Privado 2:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone2; ?></span></p>
                <?php endif; ?>
            </div>

            <?php $lat = $entity->location->latitude; $lng = $entity->location->longitude; ?>
            <?php if ( $this->isEditable() || ($lat && $lng) ): ?>
                <div class="servico clearfix">
                    <div class="mapa">
                        <?php if( $this->isEditable()): ?>
                            <div class="clearfix js-leaflet-control" data-leaflet-target=".leaflet-top.leaflet-left">
                                <a id ="locate-me" class="control-infobox-open hltip btn-map" title="Encontrar minha localização"></a>
                            </div>
                        <?php endif; ?>
                        <div id="single-map-container" class="js-map" data-lat="<?php echo $lat?>" data-lng="<?php echo $lng?>"></div>
                        <input type="hidden" id="map-target" data-name="location" class="js-editable" data-edit="location" data-value="<?php echo $lat && $lng ? "[$lng,$lat]" : '[0,0]'; ?>"/>
                    </div>
                    <!--.mapa-->
                    <div class="infos">
                        <p><span class="label">Endereço:</span> <span class="js-editable" data-edit="endereco" data-original-title="Endereço" data-emptytext="Insira o endereço, se optar pela localização aproximada, informe apenas o CEP" data-showButtons="bottom"><?php echo $entity->endereco ?></span></p>
                        <?php foreach($app->getRegisteredGeoDivisions() as $geo_division): $metakey = $geo_division->metakey; ?>
                            <p <?php if(!$entity->$metakey) { echo 'style="display:none"'; }?>>
                                <span class="label"><?php echo $geo_division->name ?>:</span> <span class="js-geo-division-address" data-metakey="<?php echo $metakey ?>"><?php echo $entity->$metakey; ?></span>
                            </p>
                        <?php endforeach; ?>
                    </div>
                    <!--.infos-->
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $this->isEditable() || $entity->longDescription ): ?>
            <h3>Descrição</h3>
            <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Espaço" data-emptytext="Insira uma descrição do espaço" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
        <?php endif; ?>

        <?php if ( $this->isEditable() || $entity->criterios ): ?>
            <h3>Critérios de uso do espaço</h3>
            <div class="descricao js-editable" data-edit="criterios" data-original-title="Critérios de uso do espaço" data-emptytext="Insira os critérios de uso do espaço" data-placeholder="Insira os critérios de uso do espaço" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->criterios; ?></div>
        <?php endif; ?>

        <!-- Video Gallery BEGIN -->
        <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
        <!-- Video Gallery END -->

        <!-- Image Gallery BEGIN -->
        <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
        <!-- Image Gallery END -->
    </div>
    <!-- #sobre -->
    <div id="agenda" class="aba-content">
        <?php $this->part('agenda', array('entity' => $entity)); ?>
    </div>
    <!-- #agenda -->

    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<div class="sidebar-left sidebar space">
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <div class="widget">
        <h3>Status</h3>
        <?php if($this->isEditable()): ?>
            <div id="editable-space-status" class="js-editable" data-edit="public" data-type="select" data-value="<?php echo $entity->public ? '1' : '0' ?>"  data-source="[{value: 0, text: 'Publicação restrita - requer autorização para criar eventos'},{value: 1, text:'Publicação livre - qualquer pessoa pode criar eventos'}]">
                <?php if ($entity->public) : ?>
                    <div class="venue-status"><div class="icon icon-publication-status-open"></div>Publicação livre</div>
                    <p class="venue-status-definition">Qualquer pessoa pode criar eventos.</p>
                <?php else: ?>
                    <div class="venue-status"><div class="icon icon-publication-status-locked"></div>Publicação restrita</div>
                    <p class="venue-status-definition">Requer autorização para criar eventos.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($entity->public) : ?>
                <div class="venue-status"><div class="icon icon-publication-status-open"></div>Publicação livre</div>
                <p class="venue-status-definition">Qualquer pessoa pode criar eventos.</p>
            <?php else: ?>
                <div class="venue-status"><div class="icon icon-publication-status-locked"></div>Publicação restrita</div>
                <p class="venue-status-definition">Requer autorização para criar eventos.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar space sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info">Para adicionar arquivos para download ou links, primeiro é preciso salvar o espaço.<span class="close"></span></p>
        </div>
    <?php endif; ?>
    <!-- Related Agents BEGIN -->
    <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->
    <?php if($this->controller->action !== 'create'): ?>
        <div class="widget">
            <?php if($entity->children && $entity->children->count()): ?>
            <h3>Sub-espaços</h3>
            <ul class="js-slimScroll widget-list">
                <?php foreach($entity->children as $space): ?>
                <li class="widget-list-item"><a href="<?php echo $space->singleUrl; ?>"><?php echo $space->name; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if($entity->id && $entity->canUser('createChild')): ?>
            <a class="btn btn-default add" href="<?php echo $app->createUrl('space','create', array('parentId' => $entity->id)) ?>">Adicionar sub-espaço</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <!-- Downloads BEGIN -->
    <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
    <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>
