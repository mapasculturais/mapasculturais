<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "Entity";

if(is_editable()){
    add_entity_types_to_js($entity);
    add_taxonoy_terms_to_js('area');

    add_taxonoy_terms_to_js('tag');

    add_entity_properties_metadata_to_js($entity);
}
add_map_assets();

add_agent_relations_to_js($entity);
add_angular_entity_assets($entity);

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<div class="barra-esquerda barra-lateral espaco">
    <div class="setinha"></div>
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<article class="main-content espaco">
    <header class="main-content-header">
        <div
            <?php if($header = $entity->getFile('header')): ?>
                 style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="imagem-do-header com-imagem js-imagem-do-header"
                 <?php else: ?>
                 class="imagem-do-header js-imagem-do-header"
            <?php endif; ?>
        >
            <?php if(is_editable()): ?>
                <a class="botao editar js-open-editbox" data-target="#editbox-change-header" href="#">editar</a>
                <div id="editbox-change-header" class="js-editbox mc-bottom" title="Editar Imagem da Capa">
                    <?php add_ajax_uploader ($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
                </div>
            <?php endif; ?>
        </div>
        <!--.imagem-do-header-->
        <div class="content-do-header">
            <?php if($avatar = $entity->avatar): ?>
                <div class="avatar com-imagem">
                    <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
                <?php else: ?>
                    <div class="avatar">
                        <img class="js-avatar-img" src="<?php echo $app->assetUrl ?>/img/avatar--space.png" />
            <?php endif; ?>
                <?php if(is_editable()): ?>
                    <a class="botao editar js-open-editbox" data-target="#editbox-change-avatar" href="#">editar</a>
                    <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
                        <?php add_ajax_uploader ($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
                    </div>
                <?php endif; ?>
            </div>
            <!--.avatar-->

            <?php if(is_editable() && $entity->canUser('modifyParent')): ?>
            <span  class="js-search js-include-editable"
                   data-field-name='parentId'
                   data-emptytext="Selecionar espaço pai"
                   data-search-box-width="400px"
                   data-search-box-placeholder="Selecionar espaço pai"
                   data-entity-controller="space"
                   data-search-result-template="#agent-search-result-template"
                   data-selection-template="#agent-response-template"
                   data-no-result-template="#agent-response-no-results-template"
                   data-selection-format="parentSpace"
                   data-allow-clear="1",
                   title="Selecionar espaço pai"
                   data-value="<?php if($entity->parent) echo $entity->parent->id; ?>"
                   data-value-name="<?php if($entity->parent) echo $entity->parent->name; ?>"
             ><?php if($entity->parent) echo $entity->parent->name; ?></span>

            <?php elseif($entity->parent): ?>
                <span><a href="<?php echo $entity->parent->singleUrl; ?>"><?php echo $entity->parent->name; ?></a></span>
            <?php endif; ?>

            <h2><span class="js-editable" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
            <div class="objeto-meta">
                <div>
                    <span class="label">Área de atuação: </span>
                    <?php if(is_editable()): ?>
                        <span id="term-area" class="js-editable-taxonomy" data-original-title="Área de Atuação" data-emptytext="Selecione pelo menos uma área" data-restrict="true" data-taxonomy="area"><?php echo implode('; ', $entity->terms['area'])?></span>
                    <?php else: ?>
                        <?php foreach($entity->terms['area'] as $i => $term): if($i) echo '; '; ?>
                            <a href="<?php echo $app->createUrl('site', 'search')?>#taxonomies[area][]=<?php echo $term ?>"><?php echo $term ?></a>
                        <?php endforeach; ?>
                    <?php endif;?>
                </div>
                <div>
                    <span class="label">Tipo: </span>
                    <a href="#" class='js-editable-type' data-original-title="Tipo" data-emptytext="Selecione um tipo" data-entity='space' data-value='<?php echo $entity->type ?>'><?php echo $entity->type? $entity->type->name : ''; ?></a>
                </div>
                <div>
                    <?php if(is_editable() || !empty($entity->terms['tag'])): ?>
                        <span class="label">Tags: </span>
                        <?php if(is_editable()): ?>
                            <span class="js-editable-taxonomy" data-original-title="Tags" data-emptytext="Insira tags" data-taxonomy="tag"><?php echo implode('; ', $entity->terms['tag'])?></span>
                        <?php else: ?>
                            <?php foreach($entity->terms['tag'] as $i => $term): if($i) echo '; ';
                                ?><a href="<?php echo $app->createUrl('site', 'search')?>#taxonomies[tags][]=<?php echo $term ?>"><?php echo $term ?></a><?php
                                endforeach; ?>
                        <?php endif;?>
                    <?php endif;?>
                </div>
            </div>
            <!--.objeto-meta-->
        </div>
    </header>
    <ul class="abas clearfix">
        <li class="active"><a href="#sobre">Sobre</a></li>
        <li><a href="#agenda">Agenda</a></li>
    </ul>
    <div id="sobre" class="aba-content">
        <div class="ficha-spcultura">
            <p>
                <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-tpl='<textarea maxlength="700"></textarea>'><?php echo $entity->shortDescription; ?></span>
            </p>
            <div class="servico">
                <?php if(is_editable()): ?>
                    <p style="display:none" class="privado"><span class="icone icon_lock"></span>Virtual ou Físico? (se for virtual a localização não é obrigatória)</p>
                <?php endif; ?>

                <?php if(is_editable() || $entity->acessibilidade): ?>
                <p><span class="label">Acessibilidade: </span><span class="js-editable" data-edit="acessibilidade" data-original-title="Acessibilidade"><?php echo $entity->acessibilidade; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable() || $entity->capacidade): ?>
                <p><span class="label">Capacidade: </span><span class="js-editable" data-edit="capacidade" data-original-title="Capacidade" data-emptytext="Especifique a capacidade do espaço"><?php echo $entity->capacidade; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable() || $entity->horario): ?>
                <p><span class="label">Horário de funcionamento: </span><span class="js-editable" data-edit="horario" data-original-title="Horário de Funcionamento" data-emptytext="Insira o horário de abertura e fechamento"><?php echo $entity->horario; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable() || $entity->site): ?>
                    <p><span class="label">Site:</span>
                    <?php if(is_editable()): ?>
                        <span class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span></p>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(is_editable() || $entity->emailPublico): ?>
                <p><span class="label">Email Público:</span> <span class="js-editable" data-edit="emailPublico" data-original-title="Email Público" data-emptytext="Insira um email que será exibido publicamente"><?php echo $entity->emailPublico; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable()):?>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">Email Privado:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="Email Privado" data-emptytext="Insira um email que não será exibido publicamente"><?php echo $entity->emailPrivado; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable() || $entity->telefonePublico): ?>
                <p><span class="label">Telefone Público:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Telefone Público" data-emptytext="Insira um telefone que será exibido publicamente"><?php echo $entity->telefonePublico; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable()):?>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">Telefone Privado 1:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone1; ?></span></p>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">Telefone Privado 2:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone2; ?></span></p>
                <?php endif; ?>
            </div>

            <?php $lat = $entity->location->latitude; $lng = $entity->location->longitude; ?>
            <?php if ( is_editable() || ($lat && $lng) ): ?>
                <div class="servico clearfix">
                    <div class="infos">
                        <p><span class="label">Endereço:</span> <span class="js-editable" data-edit="endereco" data-original-title="Endereço" data-emptytext="Insira o endereço, se optar pela localização aproximada, informe apenas o CEP" data-showButtons="bottom"><?php echo $entity->endereco ?></span></p>
                        <p><span class="label">Distrito:</span> <span class="js-sp_distrito"><?php echo $entity->sp_distrito; ?></span></p>
                        <p><span class="label">Subprefeitura:</span> <span class="js-sp_subprefeitura"><?php echo $entity->sp_subprefeitura; ?></span></p>
                        <p><span class="label">Zona:</span> <span class="js-sp_regiao"><?php echo $entity->sp_regiao; ?></p>
                    </div>
                    <!--.infos-->
                    <div class="mapa">
                        <?php if(is_editable()): ?>
                            <button id="buttonLocateMe" class="btn btn-small btn-success" >Localize-me</button>
                        <?php endif; ?>
                        <div id="map" class="js-map" data-lat="<?php echo $lat?>" data-lng="<?php echo $lng?>">
                        </div>
                        <button id="buttonSubprefs" class="btn btn-small btn-success" ><i class="icon-map-marker"></i>Mostrar Subprefeituras</button>
                        <button id="buttonSubprefs_off" class="btn btn-small btn-danger" ><i class="icon-map-marker"></i>Esconder Subprefeituras</button>
                        <script>
                            $('input[name="map-precisionOption"][value="<?php echo $entity->precisao; ?>"]').attr('checked', true);
                        </script>
                        <input type="hidden" id="map-target" data-name="location" class="js-editable" data-edit="location" data-value="[0,0]"/>
                    </div>
                    <!--.mapa-->
                </div>
            <?php endif; ?>
        </div>

        <?php if ( is_editable() || $entity->longDescription ): ?>
            <h3>Descrição</h3>
            <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Espaço" data-emptytext="Insira uma descrição do espaço" ><?php echo $entity->longDescription; ?></span>
        <?php endif; ?>

        <?php if ( is_editable() || $entity->criterios ): ?>
            <h3>Critérios de uso do espaço</h3>
            <div class="descricao js-editable" data-edit="criterios" data-original-title="Critérios de uso do espaço" data-emptytext="Insira os critérios de uso do espaço" data-placeholder="Insira os critérios de uso do espaço" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->criterios; ?></div>
        <?php endif; ?>

        <!-- Video Gallery BEGIN -->
        <?php $app->view->part('parts/video-gallery.php', array('entity'=>$entity)); ?>
        <!-- Video Gallery END -->

        <!-- Image Gallery BEGIN -->
        <?php $app->view->part('parts/gallery.php', array('entity'=>$entity)); ?>
        <!-- Image Gallery END -->
    </div>
    <!-- #sobre -->
    <div id="agenda" class="aba-content lista">
        <?php $this->part('parts/agenda', array('entity' => $entity)); ?>
    </div>
    <!-- #agenda -->

    <?php $this->part('parts/owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<div class="barra-lateral espaco barra-direita">
    <div class="setinha"></div>
    <!-- Related Agents BEGIN -->
    <?php $app->view->part('parts/related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->
    <div class="bloco">
        <?php if($entity->children): ?>
        <h3 class="subtitulo">Sub-espaços</h3>
        <ul class="js-slimScroll">
            <?php foreach($entity->children as $space): ?>
            <li><a href="<?php echo $space->singleUrl; ?>"><?php echo $space->name; ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if($entity->id && $entity->canUser('createChield')): ?>
        <a class="botao adicionar" href="<?php echo $app->createUrl('space','create', array('parentId' => $entity->id)) ?>">adicionar sub-espaço</a>
        <?php endif; ?>
    </div>

    <!-- Downloads BEGIN -->
    <?php $app->view->part('parts/downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
    <?php $app->view->part('parts/link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>
