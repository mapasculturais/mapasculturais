<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$request_project = null;

$this->addEntityToJs($entity);
if ($this->isEditable()) {
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('tag');
    $this->addTaxonoyTermsToJs('linguagem');

    $this->addOccurrenceFrequenciesToJs();

    if(!$entity->isNew()){
        $request_project = $app->repo('RequestEventProject')->findOneBy(['originType' => $entity->getClassName(), 'originId' => $entity->id]);
    }
}

$this->enqueueScript('app', 'events', '/js/events.js', array('mapasculturais'));

$this->includeAngularEntityAssets($entity);

$this->includeMapAssets();


?>
<?php ob_start(); /* Event Occurrence Item Template - Mustache */ ?>
    <div id="event-occurrence-{{id}}" class="regra clearfix" data-item-id="{{id}}">
        <header class="clearfix">
            <h3 class="alignleft"><a href="{{space.singleUrl}}">{{space.name}}</a></h3>
            <a class="toggle-mapa" href="#"><span class="ver-mapa">ver mapa</span><span class="ocultar-mapa">ocultar mapa</span> <span class="icon icon-show-map"></span></a>
        </header>
        {{#pending}}<div class="alert warning pending">Esperando confirmación</div>{{/pending}}
        <div id="occurrence-map-{{id}}" class="mapa js-map" data-lat="{{space.location.latitude}}" data-lng="{{space.location.longitude}}"></div>
        <!-- .mapa -->
        <div class="infos">
            <p><span class="label">Descripción legible: </span>{{#rule.description}}{{rule.description}}{{/rule.description}}{{^rule.description}}No Informado.{{/rule.description}}</p>
            <p><span class="label">Precio:</span> {{#rule.price}}{{rule.price}}{{/rule.price}}{{^rule.price}}No Informado.{{/rule.price}}</p>
            <p><span class="label">Horario inicial(formato HH:MM):</span> {{rule.startsAt}}</p>
            {{#rule.duration}}
                <p><span class="label">Duración (en minutos):</span> {{rule.duration}} min</p>
            {{/rule.duration}}
            <p><span class="label">Horario final(formato HH:MM):</span> {{rule.endsAt }}</p>
            <?php if($this->isEditable()): ?>
                <p class="privado"><span class="icon icon-private-info"></span><span class="label">Frecuencia:</span> {{rule.screen_frequency}}</p>
            <?php endif; ?>
            <p><span class="label">Fecha inicial:</span> {{rule.screen_startsOn}}</p>
            {{#rule.screen_until}}
                <p><span class="label">Fecha final:</span> {{rule.screen_until}}</p>
            {{/rule.screen_until}}
        </div>
        <!-- .infos -->
        <?php if($this->isEditable()): ?>
            <div class="clear">
                <a class="btn btn-default edit js-open-dialog hltip"
                   data-dialog="#dialog-event-occurrence"
                   data-dialog-callback="MapasCulturais.eventOccurrenceUpdateDialog"
                   data-dialog-title="Modificar Fecha"
                   data-form-action="edit"
                   data-item="{{serialized}}"
                   href="#" title='Editar Fecha'>Editar</a>
               <a class='btn btn-default delete js-event-occurrence-item-delete js-remove-item hltip' style="vertical-align:middle" data-href="{{deleteUrl}}" data-target="#event-occurrence-{{id}}" data-confirm-message="Eliminar esta Fecha?" title='Eliminar Fecha'>eliminar</a>
            </div>
        <?php endif; ?>
    </div>
<?php $eventOccurrenceItemTemplate = ob_get_clean(); ?>
<?php ob_start(); /* Event Occurrence Item Template VIEW - Mustache */ ?>
    <div class="regra clearfix">
        <header class="clearfix">
            <h3 class="alignleft"><a href="{{space.singleUrl}}">{{space.name}}</a></h3>
            <a class="toggle-mapa" href="#"><span class="ver-mapa">ver mapa</span><span class="ocultar-mapa">ocultar mapa</span> <span class="icon icon-show-map"></span></a>
        </header>
        <div id="occurrence-map-{{space.id}}" class="mapa js-map" data-lat="{{location.latitude}}" data-lng="{{location.longitude}}"></div>
        <!-- .mapa -->
        <div class="infos">
            <p class="descricao-legivel">{{occurrencesDescription}}</p>
            {{#occurrencesPrice}}
                <p><span class="label">Precio:</span> {{occurrencesPrice}}</p>
            {{/occurrencesPrice}}
            <p><span class="label">Dirección:</span> {{space.endereco}}</p>
        </div>
        <!-- .infos -->
    </div>
<?php $eventOccurrenceItemTemplate_VIEW = ob_get_clean(); ?>

<?php $this->part('editable-entity', array('entity' => $entity, 'action' => $action));  ?>
<article class="main-content event">
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
        
        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>
        
        <?php $this->applyTemplateHook('header.status','after'); ?>
        
        <!--.header-image-->
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--event.png']); ?>
            <!--.avatar-->
            <div class="entity-type event-type">
                <div class="icon icon-event"></div>
                <a href="#">Evento</a>
            </div>
            <!--.entity-type-->
            
            <?php $this->part('singles/name', ['entity' => $entity]) ?>
            
            <?php if ($this->isEditable() || $entity->subTitle): ?>
                <?php $this->applyTemplateHook('subtitle','before'); ?>
                <h4 class="event-subtitle">
                    <span class="js-editable" data-edit="subTitle" data-original-title="Subtítulo" data-emptytext="Ingrese un subtítulo para el evento" data-tpl='<input tyle="text" maxlength="140"></textarea>'><?php echo $entity->subTitle; ?></span>
                </h4>
                <?php $this->applyTemplateHook('subtitle','after'); ?>
            <?php endif; ?>
            
            <?php $this->applyTemplateHook('header-content','end'); ?>
        </div>
        <!--.header-content-->
        <?php $this->applyTemplateHook('header-content','after'); ?>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>
    
    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        
        <div id="sobre" class="aba-content">
            <div class="ficha-spcultura">
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning">El límite de caracteres de la descripción corta se disminuyó a 400, pero su texto actual posee  <?php echo strlen($entity->shortDescription) ?> caracteres. Debe cambiar su texto o este será recortado al salvar.</div>
                <?php endif; ?>
                <p>
                    <?php if ($this->isEditable() || $entity->shortDescription): ?>
                        <span class="label">Descripción Corta:</span><br>
                        <span class="js-editable" data-edit="shortDescription" data-original-title="Descripción Corta" data-emptytext="Agregue una Descripción Corta para el evento" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                    <?php endif; ?>
                </p>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>
                    <?php if ($this->isEditable() || $entity->registrationInfo): ?>
                        <p><span class="label">Inscripciones:</span><span class="js-editable" data-edit="registrationInfo" data-original-title="Inscripciones" data-emptytext="Informes sobre las inscripciones"><?php echo $entity->registrationInfo; ?></span></p>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->classificacaoEtaria): ?>
                        <?php
                        /*Agente padrão da Giovanna editando atrações da Virada*/
                        if(!$entity->classificacaoEtaria && $entity->project && $entity->project->id == 4 && $entity->owner->id == 428){
                            $entity->classificacaoEtaria = 'Libre';
                        }
                        ?>
                        <p><span class="label">Clasificación Etaria: </span><span class="js-editable" data-edit="classificacaoEtaria" data-original-title="Clasificación Etaria" data-emptytext="Informe la Clasificación Etaria del evento"><?php echo $entity->classificacaoEtaria; ?></span></p>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->site): ?>
                        <p><span class="label">Sitio web:</span>
                            <?php if ($this->isEditable()): ?>
                                <span class="js-editable" data-edit="site" data-original-title="Sitio" data-emptytext="Informe la dirección del sitio web del evento"><?php echo $entity->site; ?></span></p>
                        <?php else: ?>
                            <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->telefonePublico): ?>
                        <p><span class="label">Más información (tel.):</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Más información" data-emptytext="000 00 00 00 00"><?php echo $entity->telefonePublico; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->traducaoLibras || $entity->traducaoLibras || $entity->descricaoSonora): ?>
                        <br>
                        <p>
                            <span>Accesibilidad:</span>

                            <?php if($this->isEditable() || $entity->traducaoLibras): ?>
                                <p><span class="label">Traducción para Lenguaje de Señas: </span><span class="js-editable" data-edit="traducaoLibras" data-original-title="Traducción para Lenguaje de Señas"><?php echo $entity->traducaoLibras; ?></span></p>
                            <?php endif; ?>

                            <?php if($this->isEditable() || $entity->descricaoSonora): ?>
                                <p><span class="label">Audio Descripción: </span><span class="js-editable" data-edit="descricaoSonora" data-original-title="Descripción Sonora"><?php echo $entity->descricaoSonora; ?></span></p>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>
                <!--.servico-->
                <div class="servico ocorrencia clearfix">
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
                    <?php if ($this->isEditable() || $occurrences): ?>
                        <div class="js-event-occurrence">

                            <?php

                            $screenFrequencies = $this->getOccurrenceFrequencies();
                            $mustache = new Mustache_Engine();

                            if ($occurrences) {

                                if ($this->isEditable()) {

                                    foreach ($occurrences as $occurrence) {
                                        $templateData = json_decode(json_encode($occurrence));
                                        $templateData->pending = $occurrence->status === MapasCulturais\Entities\EventOccurrence::STATUS_PENDING;
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
                                    $occurrences = array_filter($occurrences, function($e){
                                        return $e->status > 0;
                                    });

                                    $spaces = getOccurrencesBySpace($occurrences);

                                    $templateData = array();
                                    $templatesData = array();
                                    foreach($spaces as $space){
                                        $templateData = json_decode(json_encode($space));
                                        $templateData->occurrencesDescription = '';
                                        $templateData->occurrencesPrice = '';
                                        $prices = array();
                                        foreach ($space['occurrences'] as $occurrence) {
                                            $prices[] = !empty($occurrence->rule->price) ? strtolower(trim($occurrence->rule->price)) : '';
                                        }
                                        $arePricesTheSame = compareArrayElements($prices);
                                        if($arePricesTheSame && !empty($space['occurrences'][0]->rule->price)){
                                            $templateData->occurrencesPrice = $space['occurrences'][0]->rule->price;
                                        }
                                        foreach ($space['occurrences'] as $occurrence) {
                                            if(!empty($occurrence->rule->description))
                                                $templateData->occurrencesDescription .= trim($occurrence->rule->description);
                                            if(!$arePricesTheSame)
                                                $templateData->occurrencesDescription .= '. '.$occurrence->rule->price;
                                            if(!empty($occurrence->rule->description))
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
                <?php if($this->isEditable()): ?>
                    <div class="textright">
                        <a class="btn btn-default add js-open-dialog hltip" data-dialog="#dialog-event-occurrence" href="#"
                           data-dialog-callback="MapasCulturais.eventOccurrenceUpdateDialog"
                           data-dialog-title="Agregar Fecha"
                           data-form-action='insert'
                           title="Cliquee para agregar Fechas">
                            Agregar Fecha
                        </a>
                    </div>
                <?php endif; ?>
                <div id="dialog-event-occurrence" class="js-dialog">
                    <?php if($this->controller->action == 'create'): ?>
                        <span class="js-dialog-disabled" data-message="Para agregar Fechas, primero es preciso salvar el evento"></span>
                    <?php else: ?>
                        <div class="js-dialog-content"></div>
                    <?php endif; ?>
                </div>
            </div>


            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3>Descripción</h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descripción del Evento" data-emptytext="Agregue una Descripción del Evento" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>


            <!-- Video Gallery BEGIN -->
            <?php $this->part('video-gallery.php', array('entity' => $entity)); ?>
            <!-- Video Gallery END -->

            <!-- Image Gallery BEGIN -->
            <?php $this->part('gallery.php', array('entity' => $entity)); ?>
            <!-- Image Gallery END -->
        </div>
        <!-- #sobre.aba-content -->
    
        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>
    
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<!--.main-content-->
<div class="sidebar-left sidebar event">
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php if($this->isEditable()): ?>
        <div class="widget">
            <h3>proyecto</h3>
            <?php if($request_project): $proj = $request_project->destination; ?>
                <a href="<?php echo $proj->singleUrl ?>"><?php echo $proj->name ?></a>
            <?php else: ?>
                <a class="js-search js-include-editable"
                    data-field-name='projectId'
                    data-emptytext="Seleccione un proyecto"
                    data-search-box-width="400px"
                    data-search-box-placeholder="Seleccione un proyecto"
                    data-entity-controller="project"
                    data-search-result-template="#agent-search-result-template"
                    data-selection-template="#agent-response-template"
                    data-no-result-template="#agent-response-no-results-template"
                    data-selection-format="chooseProject"
                    data-multiple="true"
                    data-allow-clear="1"
                    data-auto-open="true"
                    data-value="<?php echo $entity->project ? $entity->project->id : ''; ?>"
                    data-value-name="<?php echo $entity->project ? $entity->project->name : ''; ?>"
                    title="Selecionar um Projeto">
                    <?php echo $entity->project ? $entity->project->name : ''; ?>
                </a>
            <?php endif; ?>
            <span class="warning pending js-pending-project hltip" data-hltip-classes="hltip-warning" hltitle="Esperando confirmación" <?php if(!$request_project) echo 'style="display:none"'; ?>></span>
        </div>
    <?php elseif($entity->project): ?>
        <div class="widget">
            <h3>Proyecto</h3>
            <a class="event-project-link" href="<?php echo $entity->project->singleUrl; ?>"><?php echo $entity->project->name; ?></a>
        </div>
    <?php endif; ?>
    <div class="widget">
        <h3>Tipos de Eventos</h3>
        <?php if ($this->isEditable()): ?>
            <span id="term-linguagem" class="js-editable-taxonomy" data-original-title="Tipos de Eventos" data-emptytext="Seleccione por lo menos un tipo de evento" data-restrict="true" data-taxonomy="linguagem"><?php echo implode('; ', $entity->terms['linguagem']) ?></span>
        <?php else: ?>
            <?php $linguagens = array_values($app->getRegisteredTaxonomy($entity->getClassName(), 'linguagem')->restrictedTerms); sort($linguagens); ?>
            <?php foreach ($linguagens as $i => $t): if(in_array($t, $entity->terms['linguagem'])): ?>
                <a class="tag tag-event" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(linguagens:!(<?php echo $i ?>)),global:(enabled:(event:!t),filterEntity:event))"><?php echo $t ?></a>
            <?php endif; endforeach; ?>
        <?php endif; ?>
    </div>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar event sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info">Para agregar archivos para descargar o links, primero es preciso salvar el evento.<span class="close"></span></p>
        </div>
    <?php endif; ?>

    <!-- Related Agents BEGIN -->
    <?php $this->part('related-agents.php', array('entity' => $entity)); ?>
    <!-- Related Agents END -->


    <!-- Downloads BEGIN -->
    <?php $this->part('downloads.php', array('entity' => $entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
    <?php $this->part('link-list.php', array('entity' => $entity)); ?>
    <!-- Link List END -->
</div>
<?php if ($this->isEditable()): ?>
<script id="event-occurrence-form" type="text/html" class="js-mustache-template">
    <form action="{{formAction}}" method="POST">
        <div class="alert danger hidden"></div>
        <input type="hidden" name="eventId" value="<?php echo $entity->id; ?>"/>
        <input id="espaco-do-evento" type="hidden" name="spaceId" value="{{space.id}}">

        <div class="clearfix js-space">
            <label><?php $this->dict('entities: Space') ?>:</label><br>
            <span class="js-search-occurrence-space"
                data-field-name='spaceId'
                data-emptytext="Seleccione <?php $this->dict('entities: a space') ?>"
                data-search-box-width="400px"
                data-search-box-placeholder="Seleccione <?php $this->dict('entities: a space') ?>"
                data-entity-controller="space"
                data-search-result-template="#agent-search-result-template"
                data-selection-template="#agent-response-template"
                data-no-result-template="#agent-response-no-results-template"
                data-selection-format="chooseSpace"
                data-auto-open="true"
                data-value="{{space.id}}"
                title="Selecione <?php $this->dict('entities: a space') ?>"
                >{{space.name}}</span>
        </div>

        <!--mostrar se não encontrar o <?php $this->dict('entities: space') ?> registrado
        <div class="alert warning">
            Aparentemente el <?php $this->dict('entities: space') ?> buscado ya no se encuentra registrado en nuestro sistema. Intente una nueva busqueda o antes de continuar, agregue un <?php $this->dict('entities: new space') ?> cliqueando en botón abajo.
        </div>
        <a class="btn btn-default add" href="#">Agregar <?php $this->dict('entities: space') ?></a>-->
        <div class="clearfix">
            <div class="grupo-de-campos">
                <label for="horario-de-inicio">Horario inicial:</label><br>
                <input id="horario-de-inicio" class="horario-da-ocorrencia js-event-time" type="text" name="startsAt" placeholder="00:00" value="{{rule.startsAt}}">
            </div>
            <div class="grupo-de-campos">
                <label for="duracao">Duración:</label><br>
                <input id="duracao" class="horario-da-ocorrencia js-event-duration" type="text" name="duration" placeholder="minutos"  value="{{rule.duration}}">
            </div>
            <div class="grupo-de-campos">
                <label for="horario-de-fim">Horario final:</label><br>
                <input id="horario-de-fim" class="horario-da-ocorrencia js-event-end-time" type="text" name="endsAt" placeholder="00:00" value="{{rule.endsAt}}">
            </div>
            <div class="grupo-de-campos">
                <span class="label">Frecuencia:</span><br>
                    <select name="frequency" class="js-select-frequency">
                        <option value="once" {{#rule.freq_once}}selected="selected"{{/rule.freq_once}}>una vez</option>
                        <option value="daily" {{#rule.freq_daily}}selected="selected"{{/rule.freq_daily}}>todos los días</option>
                        <option value="weekly" {{#rule.freq_weekly}}selected="selected"{{/rule.freq_weekly}}>semanal</option>
                        <!-- for now we will not support monthly recurrences.
                        <option value="monthly" {{#rule.freq_monthly}}selected="selected"{{/rule.freq_monthly}}>mensual</option>
                        -->
                    </select>
                </div>
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos">
                <label for="data-de-inicio">Fecha inicial:</label><br>
                <input id="starts-on-{{id}}-visible" type="text" class="js-event-dates js-start-date data-da-ocorrencia" readonly="readonly" placeholder="00/00/0000" value="{{rule.screen_startsOn}}">
                <input id="starts-on-{{id}}" name="startsOn" type="hidden" data-alt-field="#starts-on-{{id}}-visible" value="{{rule.startsOn}}"/>
            </div>
            <div class="grupo-de-campos js-freq-hide js-daily js-weekly js-monthly">
                <label for="data-de-fim">Fecha final:</label><br>
                <input id="until-{{id}}-visible" type="text" class="js-event-dates js-end-date data-da-ocorrencia" readonly="readonly" placeholder="00/00/0000" value="{{rule.screen_until}}">
                <input id="until-{{id}}" name="until" type="hidden" value="{{rule.until}}"/>
                <!--(Se repetir mostra o campo de data final)-->
            </div>
            <div class="alignleft js-freq-hide js-weekly">
                <span class="label">Repite:</span><br>
                <div>
                    <label><input type="checkbox" name="day[0]" {{#rule.day.0}}checked="checked"{{/rule.day.0}}/> D </label>
                    <label><input type="checkbox" name="day[1]" {{#rule.day.1}}checked="checked"{{/rule.day.1}}/> L </label>
                    <label><input type="checkbox" name="day[2]" {{#rule.day.2}}checked="checked"{{/rule.day.2}}/> M </label>
                    <label><input type="checkbox" name="day[3]" {{#rule.day.3}}checked="checked"{{/rule.day.3}}/> M </label>
                    <label><input type="checkbox" name="day[4]" {{#rule.day.4}}checked="checked"{{/rule.day.4}}/> J </label>
                    <label><input type="checkbox" name="day[5]" {{#rule.day.5}}checked="checked"{{/rule.day.5}}/> V </label>
                    <label><input type="checkbox" name="day[6]" {{#rule.day.6}}checked="checked"{{/rule.day.6}}/> S </label>
                </div>
                <!-- for now we will not support monthly recurrences.
                <div>
                    <label style="display:inline;"><input type="radio" name="monthly" value="month" {{#rule.monthly_month}}checked="checked"{{/rule.monthly_month}}/> día deL mes </label>
                    <label style="display:inline;"><input type="radio" name="monthly" value="week" {{#rule.monthly_week}}checked="checked"{{/rule.monthly_week}}/> día de la semana </label>
                </div>
                -->
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos descricao-horario-legivel">
                <label for="description">Descripción legible del horaio:</label>
                <p class="form-help">Puede agregar una descripción propia o una automática cliqueando en el botón.</p>
                <div class="grupo-descricao-automatica clearfix">
                    <p id="descricao-automatica" class="alert automatic">Descripción generada por el sistema automáticamente.</p>
                    <a class="btn btn-default insert"></a>
                </div>
                <input type="text" name="description" value="{{rule.description}}" placeholder="Coloque en este campo solamente información sobre la fecha y hora de este evento.">
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos" >
                <label for="price">Precio:</label><br>
                <input type="text" name="price" value="{{rule.price}}">
            </div>
        </div>
        <footer class="clearfix">
            <input type="submit" value="Enviar">
        </footer>
    </form>
</script>
<?php endif; ?>
<script type="text/html" id="event-occurrence-item" class="js-mustache-template">
    <?php echo $eventOccurrenceItemTemplate; ?>
</script>
