<?php
use MapasCulturais\i;
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$viewModeString = $entityName !== 'project' ? '' : ',viewMode:list';

$this->addSealsToJs(true,array(),$entity);

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';

function printSubsiteFilter($property){
    if($property){
        echo implode('; ', $property);
    }
}

?>
<?php $this->applyTemplateHook('subsite-filters','before'); ?>
<div id="filtros" class="aba-content">
    <?php $this->applyTemplateHook('subsite-filters','begin'); ?>
    <p class="alert info">
        <?php i::_e('Configure aqui os filtros que serão aplicados sobre os dados cadastrados na instalação principal. Deixe em branco os campos onde você não quer aplicar filtro algum, deixando aparecer todos os dados da instalação principal.'); ?>
    </p>

    <?php $this->applyTemplateHook('subsite-filters-agent','before'); ?>
    <section class="filter-section">
        <header><?php i::_e('Agentes'); ?></header>
        <?php $this->applyTemplateHook('subsite-filters-agent','begin'); ?>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_term_area") && $editEntity? 'required': '');?>"><?php i::_e('Área de Atuação do Agente:'); ?> </span>
          <span class="js-editable" data-edit="filtro_agent_term_area" data-original-title="<?php i::esc_attr_e('Área de Atuação'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione a(s) área(s) de atuação'); ?>"><?php printSubsiteFilter($entity->filtro_agent_term_area) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Estado") && $editEntity? 'required': '');?>"><?php i::_e('Estado(s):'); ?> </span>
          <span class="js-editable" data-edit="filtro_agent_meta_En_Estado" data-original-title="<?php i::esc_attr_e('Estado(s)'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) estado(s) para o(s) Agente(s)'); ?>"><?php printSubsiteFilter($entity->filtro_agent_meta_En_Estado) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Municipio") && $editEntity? 'required': '');?>"><?php i::_e('Municipio(s):'); ?> </span>
          <span class="js-editable" data-edit="filtro_agent_meta_En_Municipio" data-original-title="<?php i::esc_attr_e('Município'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) município(s) para o(s) Agente(s)'); ?>"><?php printSubsiteFilter($entity->filtro_agent_meta_En_Municipio) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Bairro") && $editEntity? 'required': '');?>"><?php i::_e('Bairro(s):'); ?> </span>
          <span class="js-editable" data-edit="filtro_agent_meta_En_Bairro" data-original-title="<?php i::esc_attr_e('Bairro'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) bairro(s) para o(s) Agente(s)'); ?>"><?php printSubsiteFilter($entity->filtro_agent_meta_En_Bairro) ?></span>
        </p>
        <?php $this->applyTemplateHook('subsite-filters-agent','end'); ?>
    </section>
    <?php $this->applyTemplateHook('subsite-filters-agent','after'); ?>

    <?php $this->applyTemplateHook('subsite-filters-space','before'); ?>
    <section class="filter-section">
        <header>Espaços</header>
        <?php $this->applyTemplateHook('subsite-filters-space','begin'); ?>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_term_area") && $editEntity? 'required': '');?>"><?php i::_e('Área de Atuação do Espaço:'); ?> </span>
          <span class="js-editable" data-edit="filtro_space_term_area" data-original-title="<?php i::esc_attr_e('Área de Atuação'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione a(s) área(s) de atuação'); ?>"><?php printSubsiteFilter($entity->filtro_space_term_area) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_type") && $editEntity? 'required': '');?>"><?php i::_e('Tipo de Espaço:'); ?> </span>
          <span class="js-editable" data-edit="filtro_space_meta_type" data-original-title="<?php i::esc_attr_e('Tipo de Espaço'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) tipo(s) de espaço(s)'); ?>"><?php printSubsiteFilter($entity->filtro_space_meta_type) ?></span>
        </p>

        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_En_Estado") && $editEntity? 'required': '');?>"><?php i::_e('Estado:'); ?> </span>
          <span class="js-editable" data-edit="filtro_space_meta_En_Estado" data-original-title="<?php i::esc_attr_e('Estado'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) estado(s) para o(s) Espaço(s)'); ?>"><?php printSubsiteFilter($entity->filtro_space_meta_En_Estado) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_En_Municipio") && $editEntity? 'required': '');?>"><?php i::_e('Municipio(s):'); ?> </span>
          <span class="js-editable" data-edit="filtro_space_meta_En_Municipio" data-original-title="<?php i::esc_attr_e('Município'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) município(s) para o(s) Agente(s)'); ?>"><?php printSubsiteFilter($entity->filtro_space_meta_En_Municipio) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_En_Bairro") && $editEntity? 'required': '');?>"><?php i::_e('Bairro(s):'); ?> </span>
          <span class="js-editable" data-edit="filtro_space_meta_En_Bairro" data-original-title="<?php i::esc_attr_e('Bairro'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) bairro(s) para o(s) Agente(s)'); ?>"><?php printSubsiteFilter($entity->filtro_space_meta_En_Bairro) ?></span>
        </p>
        <?php $this->applyTemplateHook('subsite-filters-space','end'); ?>
    </section>
    <?php $this->applyTemplateHook('subsite-filters-space','after'); ?>

    <?php $this->applyTemplateHook('subsite-filters-event','before'); ?>
    <section class="filter-section">
        <header>Eventos</header>
        <?php $this->applyTemplateHook('subsite-filters-event','begin'); ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_event_term_linguagem") && $editEntity? 'required': '');?>"><?php i::_e('Linguagem:'); ?> </span>
            <span class="js-editable" data-edit="filtro_event_term_linguagem" data-original-title="<?php i::esc_attr_e('Linguagem'); ?>" data-emptytext="<?php i::esc_attr_e('Selecione o(s) tipos(s) de linguagem'); ?>"><?php printSubsiteFilter($entity->filtro_event_term_linguagem) ?></span>
        </p>
        <?php $this->applyTemplateHook('subsite-filters-event','end'); ?>
    </section>
    <?php $this->applyTemplateHook('subsite-filters-event','after'); ?>
    <?php $this->applyTemplateHook('subsite-filters-seal','before'); ?>
    <section class="filter-section">
        <header>Selos Verificadores</header>
        <?php $this->applyTemplateHook('subsite-filters-seal','begin'); ?>
        <span class="label <?php echo ($entity->isPropertyRequired($entity, "verifiedSeals") && $editEntity ? 'required' : ''); ?>"><?php i::_e('Selos:'); ?> </span>
        <div class="subsite-related-seal-configuration" ng-controller="SealsSubSiteController">
            <div class="selos-relacionados">
                <input type="hidden" id="verifiedSeals" name="verifiedSeals" class="js-editable" data-edit="verifiedSeals" data-name="verifiedSeals" data-value="<?php printSubsiteFilter($entity->verifiedSeals) ?>">
                <edit-box id='set-seal-subsite' cancel-label="Cancelar" close-on-cancel='true'>
                    <div ng-if="seals.length > 0" class="widget">
                        <div class="selos clearfix">
                            <div class="avatar-seal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}"  ng-click="setSeal(seal)">
                                <img ng-src="{{avatarUrl(seal['@files:avatar.avatarSmall'].url)}}" width="48">
                                <div class="descricao-do-selo">
                                    <h1>{{seal.name}}</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </edit-box>
                <div class="widget">
                    <div class="selos clearfix">
                        <div ng-if="entity.verifiedSeals.length > 0" class="avatar-seal" ng-repeat="item in entity.verifiedSeals">
                            <img ng-if="item" class="" ng-src="{{avatarUrl(allowedSeals[getArrIndexBySealId(item)]['@files:avatar.avatarSmall'].url)}}">
                            <div class="botoes"><a class="delete hltip js-remove-item"  data-href="" data-target="" data-confirm-message="" title="Excluir selo" ng-click="removeSeal(item)" rel='noopener noreferrer'></a></div>
                            <div ng-if="item" class="descricao-do-selo">
                                <h1><a href="{{allowedSeals[getArrIndexBySealId(item)].singleUrl}}" class="ng-binding" rel='noopener noreferrer'>{{allowedSeals[getArrIndexBySealId(item)].name}}</a></h1>
                            </div>
                        </div>
                        <div ng-if="seals.length > 0" ng-click="editbox.open('set-seal-subsite', $event)" class="hltip editable editable-empty" title="Adicionar selo"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->applyTemplateHook('subsite-filters-seal','end'); ?>
    </section>
    <?php $this->applyTemplateHook('subsite-filters-seal','after'); ?>

    <div ng-controller="ConfigFilterSubsiteController">
        <h2><?php i::_e("Filtragem dos dados") ?></h2>
        <edit-box
            id='new-filter'
            close-on-cancel='true'
            position="left"
            on-submit="save_filter"
            cancel-label="Cancelar"
            submit-label="Adicionar"
            >
            <!-- class-add="modal" -->
            <h4>{{ readable_names[filter_entity] }}</h4>

            <p>
                <span class="label"><?php i::_e("Descrição") ?>: </span>
                <input type="text" ng-model="new_filter.label"/>
            </p>

            <p>
                <span class="label"><?php i::_e("Campo") ?>: </span>
                <select ng-model="new_filter.field">
                    <option ng-repeat="(field, conf) in conf_filters[filter_entity]" ng-value="field">{{ conf.label }}</option>
                </select>
            </p>

            <p>
                <span class="label"><?php i::_e("Tipo do campo") ?>: </span>
                <select ng-model="new_filter.fieldType">
                    <option ng-repeat="(key, value) in conf_filters[filter_entity][new_filter.field]['types']" ng-value="key">{{ value }}</option>
                </select>
            </p>

            <p>
                <a class="hltip btn" ng-class="{'selected': new_filter.isInline}" title="{{filter.placeholder}}" ng-click="new_filter.isInline = !new_filter.isInline" rel='noopener noreferrer'>
                    <span class="icon icon-check" ng-class="{'selected': new_filter.isInline}"></span><?php i::_e("Filtro Avançado") ?>
                </a>
            </p>

            <div id="filter-error" class="widget" style="display: none">
                <p class="alert danger"><?php i::_e("Preencha todos os campos para adicionar um filtro") ?>.</p>
            </div>
        </edit-box>
        <section class="ficha-spcultura" ng-repeat="(entity, entitiy_filter) in filters">
            <input
                type="hidden"
                class="js-editable"
                data-emptytext=""
                data-edit="user_filters__{{ entity }}"
                id="user_filters__{{ entity }}" />
            <header class="agentes-relacionados">
                <h4>{{ readable_names[entity] }}
                    <button class="add hltip alignright" hltitle="Adicionar filtro" ng-click="add_filter(entity, $event)"></button>
                </h4>
            </header>

            <div class="servico" ng-repeat="filter in entitiy_filter track by $index">
                <p class="alignleft">
                    <span class="label"><?php i::_e("Descrição") ?>:</span> {{ filter.label }}<br/>
                    <span class="label"><?php i::_e("Campo") ?>:</span> {{ filter.field }}<br/>
                    <span class="label"><?php i::_e("Tipo") ?>:</span> {{ filter.fieldType }}<br/>
                    <span class="label"><?php i::_e("Filtro Avançado") ?>:</span>
                        <span ng-if="filter.isInline"><?php i::_e("Sim") ?></span>
                        <span ng-if="!filter.isInline"><?php i::_e("Não") ?></span>
                </p>
                <div class="actions alignright">
                    <button
                        class="hltip btn icon icon-arrow-up"
                        hltitle="<?php i::_e("Mover para cima") ?>"
                        ng-click="move_filter(entitiy_filter, filter, -1)"
                        ng-class="{'disabled': $first}"></button>
                    <button
                        class="hltip btn icon icon-select-arrow"
                        hltitle="<?php i::_e("Mover para baixo") ?>"
                        ng-click="move_filter(entitiy_filter, filter, 1)"
                        ng-class="{'disabled': $last}"></button>
                    <button
                        class="delete hltip btn"
                        hltitle="<?php i::_e("Remover filtro") ?>"
                        ng-click="delete_filter(entitiy_filter, filter)"></button>
                </div>
                <div style="clear: both"></div>
            </div>
            <div class="servico">
                <p class="aligncenter" ng-hide="entitiy_filter.length"><?php i::_e("Sem filtros configurados") ?></p>
            </div>
        </section>
    </div>

</div>
