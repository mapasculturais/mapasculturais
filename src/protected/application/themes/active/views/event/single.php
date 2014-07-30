<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "Entity";

if (is_editable()) {
    add_entity_types_to_js($entity);
    add_taxonoy_terms_to_js('tag');
    add_taxonoy_terms_to_js('linguagem');

    add_entity_properties_metadata_to_js($entity);

    $app->enqueueScript('app', 'events', '/js/events.js', array('mapasculturais'));
    $app->enqueueScript('vendor', 'jquery-ui-datepicker', '/vendor/jquery-ui.datepicker.js', array('jquery'));
    $app->enqueueScript('vendor', 'jquery-ui-datepicker-pt-BR', '/vendor/jquery-ui.datepicker-pt-BR.min.js', array('jquery'));
}

$app->enqueueScript('app', 'events', '/js/events.js', array('mapasculturais'));

add_agent_relations_to_js($entity);
add_angular_entity_assets($entity);

$app->enqueueScript('vendor', 'momentjs', '/vendor/moment.js');
$app->enqueueScript('vendor', 'momentjs-pt-br', '/vendor/moment.pt-br.js',array('momentjs'));

add_map_assets();

add_occurrence_frequencies_to_js();
?>
<?php ob_start(); /* Event Occurrence Item Template - Mustache */ ?>
    <div id="event-occurrence-{{id}}" class="regra clearfix" data-item-id="{{id}}">
        <header class="clearfix">
            <h3 class="alignleft"><a href="{{space.singleUrl}}">{{space.name}}</a></h3>
            <a class="toggle-mapa" href="#"><span class="ver-mapa">ver mapa</span><span class="ocultar-mapa">ocultar mapa</span> <span class="icone icon_pin"></span></a>
        </header>
        <div class="infos">
            <p><span class="label">Descrição Legível: </span>{{#rule.description}}{{rule.description}}{{/rule.description}}{{^rule.description}}Não Informado.{{/rule.description}}</p>
            <p><span class="label">Preço:</span> {{#rule.price}}{{rule.price}}{{/rule.price}}{{^rule.price}}Não Informado.{{/rule.price}}</p>
            <p><span class="label">Horário inicial:</span> {{rule.startsAt}}</p>
            {{#rule.duration}}
                <p><span class="label">Duração:</span> {{rule.duration}}</p>
            {{/rule.duration}}
            <?php if(is_editable()): ?>
                <p class="privado"><span class="icone icon_lock"></span><span class="label">Frequência:</span> {{rule.screen_frequency}}</p>
            <?php endif; ?>
            <p><span class="label">Data inicial:</span> {{rule.screen_startsOn}}</p>
            {{#rule.screen_until}}
                <p><span class="label">Data final:</span> {{rule.screen_until}}</p>
            {{/rule.screen_until}}
        </div>
        <!-- .infos -->
        <div id="occurrence-map-{{id}}" class="mapa js-map" data-lat="{{space.location.latitude}}" data-lng="{{space.location.longitude}}"></div>
        <!-- .mapa -->
        <?php if(is_editable()): ?>
            <div class="clear">
                <a class="editar botao js-open-dialog hltip"
                   data-dialog="#dialog-event-occurrence"
                   data-dialog-callback="MapasCulturais.eventOccurrenceUpdateDialog"
                   data-dialog-title="Modificar Ocorrência"
                   data-form-action="edit"
                   data-item="{{serialized}}"
                   href="#" title='Editar Ocorrência'>editar</a>
               <a class='excluir botao js-event-occurrence-item-delete js-remove-item hltip' style="vertical-align:middle" data-href="{{deleteUrl}}" data-target="#event-occurrence-{{id}}" data-confirm-message="Excluir esta Ocorrência?" title='Excluir Ocorrência'>excluir</a>
            </div>
        <?php endif; ?>
    </div>
<?php $eventOccurrenceItemTemplate = ob_get_clean(); ?>
<?php ob_start(); /* Event Occurrence Item Template VIEW - Mustache */ ?>
    <div class="regra clearfix">
        <header class="clearfix">
            <h3 class="alignleft"><a href="{{space.singleUrl}}">{{space.name}}</a></h3>
            <a class="toggle-mapa" href="#"><span class="ver-mapa">ver mapa</span><span class="ocultar-mapa">ocultar mapa</span> <span class="icone icon_pin"></span></a>
        </header>
        <div class="infos">
            <p class="descricao-legivel">{{occurrencesDescription}}</p>
            {{#occurrencesPrice}}
                <p><span class="label">Preço:</span> {{occurrencesPrice}}</p>
            {{/occurrencesPrice}}
            <p><span class="label">Endereço:</span> {{space.endereco}}</p>
        </div>
        <!-- .infos -->
        <div id="occurrence-map-{{space.id}}" class="mapa js-map" data-lat="{{location.latitude}}" data-lng="{{location.longitude}}"></div>
        <!-- .mapa -->
    </div>
<?php $eventOccurrenceItemTemplate_VIEW = ob_get_clean(); ?>

<?php $this->part('editable-entity', array('entity' => $entity, 'action' => $action));  ?>
<div class="barra-esquerda barra-lateral evento">
    <div class="setinha"></div>
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
        <?php if(is_editable()): ?>
        <div class="bloco">
            <h3 class="subtitulo">Projeto</h3>
            <a class="js-search js-include-editable"
                data-field-name='projectId'
                data-emptytext="Selecione um projeto"
                data-search-box-width="400px"
                data-search-box-placeholder="Selecione um projeto"
                data-entity-controller="project"
                data-search-result-template="#agent-search-result-template"
                data-selection-template="#agent-response-template"
                data-no-result-template="#agent-response-no-results-template"
                data-selection-format="chooseProject"
                data-allow-clear="1"
                data-auto-open="true"
                data-value="<?php echo $entity->project ? $entity->project->id : ''; ?>"
                data-value-name="<?php echo $entity->project ? $entity->project->name : ''; ?>"
                title="Selecionar um Projeto">
                <?php echo $entity->project ? $entity->project->name : ''; ?>
            </a>
        </div>
        <?php elseif($entity->project): ?>
        <div class="bloco">
            <h3 class="subtitulo">Projeto</h3>
            <span><a href="<?php echo $entity->project->singleUrl; ?>"><?php echo $entity->project->name; ?></a></span>
        </div>
        <?php endif; ?>
</div>
<article class="col-60 main-content evento">
    <header class="main-content-header">
        <div
        <?php if ($header = $entity->getFile('header')): ?>
                style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="imagem-do-header com-imagem js-imagem-do-header"
            <?php else: ?>
                class="imagem-do-header js-imagem-do-header"
            <?php endif; ?>
            >
                <?php if (is_editable()): ?>
                <a class="botao editar js-open-editbox" data-target="#editbox-change-header" href="#">editar</a>
                <div id="editbox-change-header" class="js-editbox mc-bottom" title="Editar Imagem da Capa">
                    <?php add_ajax_uploader($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
                </div>
            <?php endif; ?>
        </div>
        <!--.imagem-do-header-->
        <div class="content-do-header">
            <?php if ($avatar = $entity->avatar): ?>
                <div class="avatar com-imagem">
                    <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
                <?php else: ?>
                <div class="avatar">
                    <img class="js-avatar-img" src="<?php echo $app->assetUrl ?>/img/avatar-padrao.png" />
                <?php endif; ?>
                <?php if (is_editable()): ?>
                    <a class="botao editar js-open-editbox" data-target="#editbox-change-avatar" href="#">editar</a>
                    <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
                        <?php add_ajax_uploader($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
                    </div>
                <?php endif; ?>
                </div>
                <!--.avatar-->


                <h2><span class="js-editable" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
                <div class="objeto-meta">
                    <div>
                        <p>
                            <?php if (is_editable() || $entity->subTitle): ?>
                                <span class="js-editable" data-edit="subTitle" data-original-title="Sub-Título" data-emptytext="Insira um sub-título para o evento" data-tpl='<input tyle="text" maxlength="140"></textarea>'><?php echo $entity->subTitle; ?></span>
                            <?php endif; ?>
                        </p>
                        <span class="label">Linguagens: </span>
                        <?php if (is_editable()): ?>
                            <span id="term-linguagem" class="js-editable-taxonomy" data-original-title="Linguagens" data-emptytext="Selecione pelo menos uma linguagem" data-restrict="true" data-taxonomy="linguagem"><?php echo implode('; ', $entity->terms['linguagem']) ?></span>
                        <?php else: ?>
                            <?php foreach ($entity->terms['linguagem'] as $i => $term): if ($i) echo ': '; ?>
                                <a href="<?php echo $app->createUrl('site', 'search') ?>#taxonomies[linguagem][]=<?php echo $term ?>"><?php echo $term ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if (is_editable() || !empty($entity->terms['tag'])): ?>
                            <span class="label">Tags: </span>
                            <?php if (is_editable()): ?>
                                <span class="js-editable-taxonomy" data-original-title="Tags" data-emptytext="Insira tags" data-taxonomy="tag"><?php echo implode('; ', $entity->terms['tag']) ?></span>
                            <?php else: ?>
                                <?php foreach ($entity->terms['tag'] as $i => $term): if ($i) echo '; '; ?>
                                    <a href="<?php echo $app->createUrl('site', 'search') ?>#taxonomies[tags][]=<?php echo $term ?>"><?php echo $term ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <!--.objeto-meta-->
            </div>
    </header>
    <!--.main-content-header-->
    <div id="sobre" class="aba-content">
        <div class="ficha-spcultura">
            <p>
                <?php if (is_editable() || $entity->shortDescription): ?>
                    <span class="label">Descrição Curta:</span><br>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta para o evento" data-tpl='<textarea maxlength="700"></textarea>'><?php echo $entity->shortDescription; ?></span>
                <?php endif; ?>
            </p>
            <div class="servico">

                <?php if (is_editable() || $entity->registrationInfo): ?>
                    <p><span class="label">Inscrições:</span><span class="js-editable" data-edit="registrationInfo" data-original-title="Inscrições" data-emptytext="Informações sobre as inscrições"><?php echo $entity->registrationInfo; ?></span></p>
                <?php endif; ?>

                <?php if (is_editable() || $entity->classificacaoEtaria): ?>
                    <?php
                    /*Agente padrão da Giovanna editando atrações da Virada*/
                    if(!$entity->classificacaoEtaria && $entity->project && $entity->project->id == 4 && $entity->owner->id == 428){
                        $entity->classificacaoEtaria = 'Livre';
                    }
                    ?>
                    <p><span class="label">Classificação Etária: </span><span class="js-editable" data-edit="classificacaoEtaria" data-original-title="Classificação Etária" data-emptytext="Informe a classificação etária do evento"><?php echo $entity->classificacaoEtaria; ?></span></p>
                <?php endif; ?>

                <?php if (is_editable() || $entity->site): ?>
                    <p><span class="label">Site:</span>
                        <?php if (is_editable()): ?>
                            <span class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Informe o endereço do site do evento"><?php echo $entity->site; ?></span></p>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(is_editable() || $entity->telefonePublico): ?>
                    <p><span class="label">Mais Informações:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Mais Informações" data-emptytext="(000) 0000-0000"><?php echo $entity->telefonePublico; ?></span></p>
                <?php endif; ?>

                <?php if(is_editable() || $entity->traducaoLibras || $entity->traducaoLibras || $entity->descricaoSonora): ?>
                    <br>
                    <p>
                        <span>Acessibilidade:</span>

                        <?php if(is_editable() || $entity->traducaoLibras): ?>
                            <p><span class="label">Tradução para LIBRAS: </span><span class="js-editable" data-edit="traducaoLibras" data-original-title="Tradução para LIBRAS"><?php echo $entity->traducaoLibras; ?></span></p>
                        <?php endif; ?>

                        <?php if(is_editable() || $entity->descricaoSonora): ?>
                            <p><span class="label">Áudio Descrição: </span><span class="js-editable" data-edit="descricaoSonora" data-original-title="Descrição Sonora"><?php echo $entity->descricaoSonora; ?></span></p>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <!--.servico-->
            <div class="servico ocorrencia clearfix">
                <h6>Este evento ocorre em:</h6>

                <?php

                //Event->getOccurrencesGroupedBySpace()
                function getOccurrencesBySpace($occurrences){
                    $spaces = array();
                    usort($occurrences, function($a, $b) {
                        return $a->space->id - $b->space->id;
                    });
                    foreach ($occurrences as $occurrence) {
                        if (!array_key_exists($occurrence->space->id, $spaces)){
                            $spaces[$occurrence->space->id] = array(
                                'space' => $occurrence->space,
                                'location' => $occurrence->space->location,
                                'occurrences' => array()
                            );
                        }
                        $spaces[$occurrence->space->id]['occurrences'][] = $occurrence;
                    }
                    return $spaces;
                }

                function compareArrayElements($array){
                    $compareBase = null;
                    foreach($array as $element){
                        if($compareBase === null) $compareBase = $element;
                        if($compareBase !== $element)
                            return false;
                    }
                    return true;
                }

                $occurrences = $entity->occurrences ? $entity->occurrences->toArray() :  array();
                ?>
                <?php if (is_editable() || $occurrences): ?>
                    <div class="js-event-occurrence">

                        <?php

                        $screenFrequencies = getOccurrenceFrequencies();
                        $mustache = new Mustache_Engine();

                        if ($occurrences) {

                            if (is_editable()) {

                                foreach ($occurrences as $occurrence) {
                                    $templateData = json_decode(json_encode($occurrence));
                                    if(!is_object($templateData->rule))
                                        $templateData->rule = new stdclass;
                                    $templateData->rule->screen_startsOn = $occurrence->rule->startsOn ? (new DateTime($occurrence->rule->startsOn))->format('d/m/Y') : '';
                                    $templateData->rule->screen_until = $occurrence->rule->until ? (new DateTime($occurrence->rule->until))->format('d/m/Y') : '';
                                    $templateData->rule->screen_frequency = $occurrence->rule->frequency ? $screenFrequencies[$templateData->rule->frequency] : '';

                                    $templateData->rule->screen_spaceAddress = $occurrence->space->endereco;

                                    $templateData->serialized = json_encode($templateData);
                                    $templateData->formAction = $occurrence->editUrl;
                                    echo $mustache->render($eventOccurrenceItemTemplate, $templateData);
                                }

                            }else{

                                $spaces = getOccurrencesBySpace($occurrences);

                                $templateData = array();
                                $templatesData = array();
                                foreach($spaces as $space){
                                    $templateData = json_decode(json_encode($space));
                                    $templateData->occurrencesDescription = '';
                                    $templateData->occurrencesPrice = '';
                                    $prices = array();
                                    foreach ($space['occurrences'] as $occurrence) {
                                        $prices[] = strtolower(trim($occurrence->rule->price));
                                    }
                                    $arePricesTheSame = compareArrayElements($prices);
                                    if($arePricesTheSame){
                                        $templateData->occurrencesPrice = $space['occurrences'][0]->rule->price;
                                    }
                                    foreach ($space['occurrences'] as $occurrence) {
                                        $templateData->occurrencesDescription .= trim($occurrence->rule->description);
                                        if(!$arePricesTheSame)
                                            $templateData->occurrencesDescription .= '. '.$occurrence->rule->price;
                                        $templateData->occurrencesDescription .= '; ';
                                    }
                                    $templateData->occurrencesDescription = substr($templateData->occurrencesDescription,0,-2);
                                    $templatesData[] = $templateData;
                                }

                                foreach($templatesData as $templateData){
                                    echo $mustache->render($eventOccurrenceItemTemplate_VIEW, $templateData);
                                }

                            }

                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <!--.servico.ocorrencia-->
            <?php if(is_editable()): ?>
                <div class="textright">
                    <a class="botao adicionar js-open-dialog hltip" data-dialog="#dialog-event-occurrence" href="#"
                       data-dialog-callback="MapasCulturais.eventOccurrenceUpdateDialog"
                       data-dialog-title="Adicionar Ocorrência"
                       data-form-action='insert'
                       title="Clique para adicionar ocorrências">
                        Adicionar Ocorrência
                    </a>
                </div>
            <?php endif; ?>
            <div id="dialog-event-occurrence" class="js-dialog">
                <?php if($this->controller->action == 'create'): ?>
                    <span class="js-dialog-disabled" data-message="Primeiro Salve"></span>
                <?php else: ?>
                    <div class="js-dialog-content"></div>
                <?php endif; ?>
            </div>
        </div>
        <!--.ficha-spcultura-->

        <?php if ( is_editable() || $entity->longDescription ): ?>
            <h3>Descrição</h3>
            <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Evento" data-emptytext="Insira uma descrição do evento" ><?php echo $entity->longDescription; ?></span>
        <?php endif; ?>


        <!-- Video Gallery BEGIN -->
        <?php $app->view->part('parts/video-gallery.php', array('entity' => $entity)); ?>
        <!-- Video Gallery END -->

        <!-- Image Gallery BEGIN -->
        <?php $app->view->part('parts/gallery.php', array('entity' => $entity)); ?>
        <!-- Image Gallery END -->
    </div>
    <!-- #sobre.aba-content -->
    <?php $this->part('parts/owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<!--.main-content-->
<div class="barra-lateral evento barra-direita">
    <div class="setinha"></div>
    <!-- Related Agents BEGIN -->
    <?php $app->view->part('parts/related-agents.php', array('entity' => $entity)); ?>
    <!-- Related Agents END -->


    <!-- Downloads BEGIN -->
    <?php $app->view->part('parts/downloads.php', array('entity' => $entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
    <?php $app->view->part('parts/link-list.php', array('entity' => $entity)); ?>
    <!-- Link List END -->
</div>
<?php if (is_editable()): ?>
<script id="event-occurrence-form" type="text/html" class="js-mustache-template">
    <form action="{{formAction}}" method="POST">
        <div class="mensagem erro escondido"></div>
        <input type="hidden" name="eventId" value="<?php echo $entity->id; ?>"/>
        <input id="espaco-do-evento" type="hidden" name="spaceId" value="{{space.id}}">

        <div class="clearfix js-space">
            <label>Espaço:</label><br>
            <span class="js-search-occurrence-space"
                data-field-name='spaceId'
                data-emptytext="Selecione um espaço"
                data-search-box-width="400px"
                data-search-box-placeholder="Selecione um espaço"
                data-entity-controller="space"
                data-search-result-template="#agent-search-result-template"
                data-selection-template="#agent-response-template"
                data-no-result-template="#agent-response-no-results-template"
                data-selection-format="chooseSpace"
                data-auto-open="true"
                data-value="{{space.id}}"
                title="Selecione um espaço"
                >{{space.name}}</span>
        </div>

        <!--mostrar se não encontrar o espaço cadastrado
        <div class="mensagem alerta">
            Aparentemente o espaço procurado ainda não se encontra registrado em nosso sistema. Tente uma nova busca ou antes de continuar, adicione um novo espaço clicando no botão abaixo.
        </div>
        <a class="botao adicionar" href="#">adicionar espaço</a>-->
        <div class="clearfix">
            <div class="grupo-de-campos">
                <label for="horario-de-inicio">Horário inicial:</label><br>
                <input id="horario-de-inicio" class="horario-da-ocorrencia js-event-time" type="text" name="startsAt" placeholder="00:00" value="{{rule.startsAt}}">
            </div>
            <div class="grupo-de-campos">
                <label for="duracao">Duração:</label><br>
                <input id="duracao" class="horario-da-ocorrencia js-event-duration" type="text" name="duration" placeholder="00h00"  value="{{rule.duration}}">
            </div>
            <div class="grupo-de-campos">
                <span class="label">Frequência:</span><br>
                    <select name="frequency" class="js-select-frequency">
                        <option value="once" {{#rule.freq_once}}selected="selected"{{/rule.freq_once}}>uma vez</option>
                        <option value="daily" {{#rule.freq_daily}}selected="selected"{{/rule.freq_daily}}>todos os dias</option>
                        <option value="weekly" {{#rule.freq_weekly}}selected="selected"{{/rule.freq_weekly}}>semanal</option>
                        <!-- for now we will not support monthly recurrences.
                        <option value="monthly" {{#rule.freq_monthly}}selected="selected"{{/rule.freq_monthly}}>mensal</option>
                        -->
                    </select>
                </div>
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos">
                <label for="data-de-inicio">Data inicial:</label><br>
                <input id="starts-on-{{id}}-visible" type="text" class="js-event-dates js-start-date data-da-ocorrencia" readonly="readonly" placeholder="00/00/0000" value="{{rule.screen_startsOn}}">
                <input id="starts-on-{{id}}" name="startsOn" type="hidden" data-alt-field="#starts-on-{{id}}-visible" value="{{rule.startsOn}}"/>
            </div>
            <div class="grupo-de-campos js-freq-hide js-daily js-weekly js-monthly">
                <label for="data-de-fim">Data final:</label><br>
                <input id="until-{{id}}-visible" type="text" class="js-event-dates js-end-date data-da-ocorrencia" readonly="readonly" placeholder="00/00/0000" value="{{rule.screen_until}}">
                <input id="until-{{id}}" name="until" type="hidden" value="{{rule.until}}"/>
                <!--(Se repetir mostra o campo de data final)-->
            </div>
            <div class="alignleft js-freq-hide js-weekly">
                <span class="label">Repete:</span><br>
                <div>
                    <label><input type="checkbox" name="day[0]" {{#rule.day.0}}checked="checked"{{/rule.day.0}}/> D </label>
                    <label><input type="checkbox" name="day[1]" {{#rule.day.1}}checked="checked"{{/rule.day.1}}/> S </label>
                    <label><input type="checkbox" name="day[2]" {{#rule.day.2}}checked="checked"{{/rule.day.2}}/> T </label>
                    <label><input type="checkbox" name="day[3]" {{#rule.day.3}}checked="checked"{{/rule.day.3}}/> Q </label>
                    <label><input type="checkbox" name="day[4]" {{#rule.day.4}}checked="checked"{{/rule.day.4}}/> Q </label>
                    <label><input type="checkbox" name="day[5]" {{#rule.day.5}}checked="checked"{{/rule.day.5}}/> S </label>
                    <label><input type="checkbox" name="day[6]" {{#rule.day.6}}checked="checked"{{/rule.day.6}}/> S </label>
                </div>
                <!-- for now we will not support monthly recurrences.
                <div>
                    <label style="display:inline;"><input type="radio" name="monthly" value="month" {{#rule.monthly_month}}checked="checked"{{/rule.monthly_month}}/> dia do mês </label>
                    <label style="display:inline;"><input type="radio" name="monthly" value="week" {{#rule.monthly_week}}checked="checked"{{/rule.monthly_week}}/> dia da semana </label>
                </div>
                -->
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos descricao-horario-legivel">
                <label for="description">Descrição legível do horário:</label>
                <p class="form-help">Você pode inserir uma descrição própria ou inserir a descrição gerada automaticamente clicando no botão ao lado.</p>
                <div class="grupo-descricao-automatica clearfix">
                    <p id="descricao-automatica" class="mensagem automatica">Descrição gerada pelo sistema automaticamente.</p>
                    <a class="botao simples inserir"></a>
                </div>
                <input type="text" name="description" value="{{rule.description}}">
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos" >
                <label for="price">Preço:</label><br>
                <input type="text" name="price" value="{{rule.price}}">
            </div>
        </div>
        <footer class="clearfix">
            <input type="submit" value="enviar">
        </footer>
    </form>
</script>
<?php endif; ?>
<script type="text/html" id="event-occurrence-item" class="js-mustache-template">
    <?php echo $eventOccurrenceItemTemplate; ?>
</script>
