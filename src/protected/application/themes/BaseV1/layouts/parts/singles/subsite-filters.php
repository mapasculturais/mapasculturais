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
                            <div class="avatar-seal modal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}"  ng-click="setSeal(seal)">
                                <img ng-src="{{avatarUrl(seal['@files:avatar.avatarSmall'].url)}}" width="48">
                                <h3>{{seal.name}}</h3>
                            </div>
                        </div>
                    </div>
                </edit-box>
                <div class="widget">
                    <div class="selos clearfix">
                        <div ng-if="entity.verifiedSeals.length > 0" class="avatar-seal" ng-repeat="item in entity.verifiedSeals">
                            <img ng-if="item" class="img-seal" ng-src="{{avatarUrl(allowedSeals[getArrIndexBySealId(item)]['@files:avatar.avatarSmall'].url)}}">
                            <div class="botoes"><a class="delete hltip js-remove-item"  data-href="" data-target="" data-confirm-message="" title="Excluir selo" ng-click="removeSeal(item)"></a></div>
                            <div ng-if="item" class="descricao-do-selo">
                                <h1><a href="{{allowedSeals[getArrIndexBySealId(item)].singleUrl}}" class="ng-binding">{{allowedSeals[getArrIndexBySealId(item)].name}}</a></h1>
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
</div>
