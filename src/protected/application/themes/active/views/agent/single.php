<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

if(is_editable()){
    add_entity_types_to_js($entity);
    add_taxonoy_terms_to_js('area');

    add_taxonoy_terms_to_js('tag');

    add_entity_properties_metadata_to_js($entity);
}
add_map_assets();

?>
<script> $(function(){ MapasCulturais.Map.initialize({mapSelector:'.js-map',locateMeControl:true}); }); </script>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<div class="barra-esquerda barra-lateral agente">
    <div class="setinha"></div>
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<article class="main-content agente">
	<header class="main-content-header">

        <div
            <?php if($header = $entity->getFile('header')): ?>
            style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="imagem-do-header com-imagem js-imagem-do-header"
            <?php elseif(is_editable()): ?>
                class="imagem-do-header js-imagem-do-header"
            <?php endif; ?>
        >
		<?php if(is_editable()): ?>
				<a class="botao editar js-open-dialog" data-dialog="#dialog-change-header" href="#">editar</a>
				<div id="dialog-change-header" class="js-dialog" title="Editar Imagem da Capa">
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
						<img class="js-avatar-img" src="<?php echo $app->assetUrl ?>/img/avatar-padrao.png" />
			<?php endif; ?>
				<?php if(is_editable()): ?>
					<a class="botao editar js-open-dialog" data-dialog="#dialog-change-avatar" href="#">editar</a>
					<div id="dialog-change-avatar" class="js-dialog" title="Editar avatar">
						<?php add_ajax_uploader ($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
					</div>
				<?php endif; ?>
			</div>
			<!--.avatar-->
			<h2><span class="js-editable" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
			<div class="objeto-meta">
				<div>
					<span class="label">Área de atuação: </span>
					<?php if(is_editable()): ?>
						<span id="term-area" class="js-editable-taxonomy" data-original-title="Área de Atuação" data-emptytext="Selecione pelo menos uma área" data-restrict="true" data-taxonomy="area"><?php echo implode(', ', $entity->terms['area'])?></span>
					<?php else: ?>
						<?php foreach($entity->terms['area'] as $i => $term): if($i) echo ', ';
                            ?> <a href="<?php echo $app->createUrl('site', 'search')?>#taxonomies[area][]=<?php echo $term ?>"><?php echo $term ?></a><?php
                        endforeach; ?>
					<?php endif;?>
				</div>
				<div>
					<span class="label">Tipo: </span>
					<a href="#" class='js-editable-type' data-original-title="Tipo" data-emptytext="Selecione um tipo" data-entity='agent' data-value='<?php echo $entity->type ?>'><?php echo $entity->type->name; ?></a>
				</div>
				<div>
					<?php if(is_editable() || !empty($entity->terms['tag'])): ?>
                        <span class="label">Tags: </span>
                        <?php if(is_editable()): ?>
                            <span class="js-editable-taxonomy" data-original-title="Tags" data-emptytext="Insira tags" data-taxonomy="tag"><?php echo implode(', ', $entity->terms['tag'])?></span>
                        <?php else: ?>
                            <?php foreach($entity->terms['tag'] as $i => $term): if($i) echo ', ';
                                ?> <a href="<?php echo $app->createUrl('site', 'search')?>#taxonomies[tags][]=<?php echo $term ?>"><?php echo $term ?></a><?php
                            endforeach; ?>
                        <?php endif;?>
                    <?php endif; ?>
				</div>
			</div>
			<!--.objeto-meta-->
		</div>
	</header>
	<ul class="abas clearfix clear">
		<li class="active"><a href="#sobre">Sobre</a></li>
		<li><a href="#agenda">Agenda</a></li>
		<li class="staging-hidden"><a href="#contas">Contas</a></li>
	</ul>
	<div id="sobre" class="aba-content">
		<div class="ficha-spcultura">
			<p>
                <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-showButtons="bottom" data-tpl='<textarea maxlength="700"></textarea>'><?php echo $entity->shortDescription; ?></span>
			</p>
            <div class="servico">

                <?php if(is_editable() || $entity->site): ?>
                    <p><span class="label">Site:</span>
                    <?php if(is_editable()): ?>
                        <span class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span></p>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if(is_editable()): ?>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">Nome:</span> <span class="js-editable" data-edit="nomeCompleto" data-original-title="Nome Completo ou Razão Social" data-emptytext="Insira seu nome completo ou razão social"><?php echo $entity->nomeCompleto; ?></span></p>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">CPF/CNPJ:</span> <span class="js-editable" data-edit="documento" data-original-title="CPF/CNPJ" data-emptytext="Insira o CPF ou CNPJ com pontos, hífens e barras"><?php echo $entity->documento; ?></span></p>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">Idade/Tempo:</span> <span class="js-editable" data-edit="idade" data-original-title="Idade/Tempo" data-emptytext="Insira sua idade ou tempo de existência"><?php echo $entity->idade; ?></span></p>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">Gênero:</span> <span class="js-editable" data-edit="genero" data-original-title="Gênero" data-emptytext="Selecione o gênero se for pessoa física"><?php echo $entity->genero; ?></span></p>
                    <p class="privado"><span class="icone icon_lock"></span><span class="label">Email:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="Email Privado" data-emptytext="Insira um email que não será exibido publicamente"><?php echo $entity->emailPrivado; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable() || $entity->telefonePublico): ?>
                <p><span class="label">Telefone Público:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Telefone Público" data-emptytext="Insira um telefone que será exibido publicamente"><?php echo $entity->telefonePublico; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable()): ?>
                <p class="privado"><span class="icone icon_lock"></span><span class="label">Telefone 1:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone1; ?></span></p>
                <p class="privado"><span class="icone icon_lock"></span><span class="label">Telefone 2:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone2; ?></span></p>
                <?php endif; ?>
            </div>


            <?php $lat = $entity->location->latitude; $lng = $entity->location->longitude; ?>
            <?php if ( is_editable() || ($entity->precisao && $lat && $lng) ): ?>
                <!--.servico-->
                <div class="servico clearfix">
                    <div class="infos">

                        <?php if(is_editable()): ?>
                            <p class="privado">
                                <span class="icone icon_lock"></span><span class="label">Localização:</span>
                                <span class="js-editable" data-edit="precisao" id="map-precisionOption" data-onchange="precisionChange" data-truevalue="Precisa"><?php echo $entity->precisao; ?></span>
                            </p>
                        <?php else: ?>
                            <span style="display:none" id="map-precisionOption" data-truevalue="Precisa"><?php echo $entity->precisao; ?></span>
                        <?php endif; ?>

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
                        <?php if(is_editable()): ?>
                        <script>
                            $('input[name="map-precisionOption"][value="<?php echo $entity->precisao; ?>"]').attr('checked', true);
                        </script>
                    <?php endif; ?>
                        <input type="hidden" id="map-target" data-name="location" class="js-editable" data-edit="location" data-value="[0,0]"/>
                    </div>
                    <!--.mapa-->
                </div>
                <!--.servico-->
            <?php endif; ?>

		</div>
		<!--.ficha-spcultura-->

        <?php if ( is_editable() || $entity->longDescription ): ?>
            <h3>Descrição</h3>
            <div class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição" data-emptytext="Insira uma descrição" data-placeholder="Informe seus dados" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->longDescription; ?></div>
        <?php endif; ?>
		<!--.descricao-->
        <!-- Video Gallery BEGIN -->
            <?php $app->view->part('parts/video-gallery.php', array('entity'=>$entity)); ?>
        <!-- Video Gallery END -->
        <!-- Image Gallery BEGIN -->
            <?php $app->view->part('parts/gallery.php', array('entity'=>$entity)); ?>
        <!-- Image Gallery END -->
	</div>
	<!-- #sobre -->

	<div id="agenda" class="aba-content lista">
            <?php
            $date_from = new DateTime();
            $date_to = new DateTime('+180 days');
            $events = !$entity->id ? array() : $app->repo('Event')->findByAgent($entity, $date_from, $date_to);
            ?>
            <!--a class="botao adicionar" href="<?php echo $app->createUrl('event', 'create')?>">
            adicionar evento</a-->
            <?php foreach($events as $event): ?>

                <article class="objeto evento clearfix">
                    <h1><a href="<?php echo $app->createUrl('event', 'single', array('id'=>$event->id))?>">
                        <?php echo $event->name ?></a>
                    </h1>
                    <div class="objeto-content clearfix">
                        <div class="objeto-thumb"><img src="<?php echo $event->avatar ? $event->avatar->url : ''; ?>"/></div>
                        <p class="objeto-resumo">
                            <?php echo $event->shortDescription ?>
                        </p>
                        <div class="objeto-meta">
                            <div><span class="label">Linguagem:</span> <?php echo implode(', ', $event->terms['linguagem'])?></div>
                            Ocorrências:
                            <div style="padding:10px">
                                <?php
                                $occurrences = $event->findOccurrences($date_from, $date_to);
                                foreach($occurrences as $occ):
                                    ?>
                                <div>dia <?php echo $occ->startsOn->format('d \d\e M'); ?> das <?php echo $occ->startsAt->format('H:i'); ?> às <?php echo $occ->endsAt->format('H:i'); ?> </div>

                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </article>
                <!--.objeto-->
            <?php endforeach; ?>
	</div>
	<!-- #agenda -->
	<div id="contas" class="aba-content staging-hidden">
		<h3>Relatórios</h3>
		<p><a href="#">Nome do Relatório</a></p>
		<h3>Contratos</h3>
		<table>
			<thead>
			<tr>
				<th>Data</th>
				<th>Objeto</th>
				<th>Contratante</th>
				<th>Contratado</th>
				<th>Intermediário</th>
				<th>Evento relacionado</th>
				<th>R$</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>24/05/2013</td>
				<td>Cachê</td>
				<td>Mussum</td>
				<td>Fulanis de Tal</td>
				<td>Siclanis de Tal</td>
				<td>Eventis</td>
				<td>0.000.000,00</td>
			</tr>
			<tr>
				<td>24/05/2013</td>
				<td>Cachê</td>
				<td>Mussum</td>
				<td>Fulanis de Tal</td>
				<td>Siclanis de Tal</td>
				<td>Eventis</td>
				<td>0.000.000,00</td>
			</tr>
			<tr>
				<td>24/05/2013</td>
				<td>Cachê</td>
				<td>Mussum</td>
				<td>Fulanis de Tal</td>
				<td>Siclanis de Tal</td>
				<td>Eventis</td>
				<td>0.000.000,00</td>
			</tr>
			<tr>
				<td>24/05/2013</td>
				<td>Cachê</td>
				<td>Mussum</td>
				<td>Fulanis de Tal</td>
				<td>Siclanis de Tal</td>
				<td>Eventis</td>
				<td>0.000.000,00</td>
			</tr>
			<tr>
				<td>24/05/2013</td>
				<td>Cachê</td>
				<td>Mussum</td>
				<td>Fulanis de Tal</td>
				<td>Siclanis de Tal</td>
				<td>Eventis</td>
				<td>0.000.000,00</td>
			</tr>
			<tr>
				<td>24/05/2013</td>
				<td>Cachê</td>
				<td>Mussum</td>
				<td>Fulanis de Tal</td>
				<td>Siclanis de Tal</td>
				<td>Eventis</td>
				<td>0.000.000,00</td>
			</tr>
			</tbody>
		</table>
	</div>
	<!--#contas-->
	<?php $this->part('parts/owner', array('entity' => $entity, 'owner' => $entity->owner)); ?>
</article>
<div class="barra-lateral agente barra-direita">
	<div class="setinha"></div>
    <!-- Related Agents BEGIN -->
        <?php $this->part('parts/related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->

    <div class="bloco">
        <h3 class="subtitulo">Espaços do agente</h3>
        <ul class="js-slimScroll">
            <?php foreach($entity->spaces as $space): ?>
            <li><a href="<?php echo $app->createUrl('space', 'single', array('id' => $space->id)) ?>"><?php echo $space->name; ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <!--
    <div class="bloco">
        <h3 class="subtitulo">Projetos do agente</h3>
        <ul>
            <li><a href="#">Projeto 1</a></li>
            <li><a href="#">Projeto 2</a></li>
            <li><a href="#">Projeto 3</a></li>
        </ul>
    </div>
    -->

    <!-- Downloads BEGIN -->
        <?php $this->part('parts/downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('parts/link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>
