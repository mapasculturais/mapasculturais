<div class="agentes-relacionados" ng-controller="RelatedAgentsController">
    <div class="bloco" ng-repeat="(group, relations) in groups">
        <h3 class="subtitulo">{{group}}</h3>
        <div class="agentes clearfix">
            <div class="avatar" ng-repeat="(i, relation) in relations">
                <a href="{{relation.agent.singleUrl}}" ng-if="!isEditable">
                    <img ng-src="{{avatarUrl(relation.agent)}}" />
                </a>
                <img ng-if="isEditable" ng-src="{{avatarUrl(relation.agent)}}" />
                
                <div class="descricao-do-agente">
                    <h1><a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a></h1>
                    <div class="objeto-meta">
                        <div ng-if="relation.agent.terms.area">
                            <span class="label">área de atuação:</span>
                            <span ng-repeat="area in relation.agent.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                        </div>
                        <div><span class="label">tipo:</span> {{relation.agent.type.name}}</div>
                    </div>
                </div>
            </div>
            <div ng-if="isEditable" ng-click="showCreateDialog[group] = ! showCreateDialog[group]" class="hltip editable editable-empty" title="Adicionar Integrante a este Grupo"></div>
            <find-entity ng-if="isEditable" ng-show="showCreateDialog[group]" entity="agent" description="" id="group:{{group}}}" filterResult="filterResult"></find-entity>
        </div>
    </div>
</div>


<?php return; ?>

<?php foreach($entity->agentRelationsGrouped as $group => $agentRelations): ?>
<div class="js-related-group bloco" data-related-group="<?php echo $group; ?>">

    <h3 class="subtitulo js-related-group-name"><?php echo $group ?></h3>
	<div class="agentes clearfix js-relatedAgentsContainer">
        <?php
        foreach($agentRelations as $agentRelation):
            $relatedAgent = $agentRelation->agent;
            $agent_url = $relatedAgent->status > 0 ? $relatedAgent->singleUrl : '';

        ?>
        <div class="avatar" data-id="<?php echo $relatedAgent->id; ?>">
                <?php if($agent_url && !is_editable()): ?><a href="<?php echo $agent_url; ?>"><?php endif; ?>
                <?php if($avatar = $relatedAgent->avatar): ?>
                   <img src="<?php echo $avatar->transform('avatarSmall')->url; ?>" />
                   <?php else: ?>
                   <img class="js-avatar-img" src="<?php echo $app->assetUrl ?>/img/avatar-padrao.png" />
                <?php endif; ?>
                <?php if($agent_url && !is_editable()): ?></a><?php endif; ?>
                <div class="descricao-do-agente">
                    <h1 class="js-relatedAgent-name">
                        <a href="<?php echo $agent_url; ?>"><?php echo $relatedAgent->name; ?></a>
                    </h1>
                    <div class="objeto-meta">
                        <div>
                            <span class="label">área de atuação:</span>
                            <?php echo implode(', ', $relatedAgent->terms['area']) ?>
                        </div>
                        <div><span class="label">tipo:</span><?php echo is_object($relatedAgent->type) ? $relatedAgent->type->name : $relatedAgent->type ?></div>
                    </div>
                    <?php if(is_editable() && $agentRelation->canUser('changeControl')): ?>
                        <div class="clearfix">
                            <span class="label">Permitir editar:</span>
                            <div class="slider-frame">
                                <?php if($agentRelation->hasControl): ?>
                                    <span class="slider-button on">Sim</span>
                                <?php else: ?>
                                    <span class="slider-button">Não</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if(is_editable() && $agentRelation->canUser('remove')): ?><div class="textright"><button type="submit" class="bt-remove-agent js-remove-agent">Excluir</button></div><?php endif; ?>
                    <?php /*if(is_editable()):
                    //REMOVED TO AVOID THE COMPLEXITY OF EDITING AGENTS HERE. CAN BE REENABLED WHEN AND IF APPROPRIATE
                    ?>
                    <a class="action js-open-dialog" data-dialog="#dialog-editar-integrante-<?php echo $relatedAgent->id; ?>" href="#">
                    <span class="glyphicons plus"></span>editar integrante
                    </a>
                    <?php endif; */?>
                </div>
            </div>

        <?php endforeach; ?>
		<?php if(is_editable()): // Create Modal - Now with Search ?>
        <span class="js-search hltip" title="Adicionar Integrante a este Grupo"
              data-emptytext=""
              data-search-box-width="400px"
              data-search-box-placeholder="Adicionar Integrante"
              data-entity-controller="agent"
              data-search-result-template="#agent-search-result-template"
              data-target-action="append"
              data-selection-target=".js-relatedAgentsContainer"
              data-selection-template="#agent-response-template"
              data-no-result-template="#agent-response-no-results-template"
              data-selection-format="createAgentRelation"
              data-disable-buttons="1"
            ></span>
		<?php endif; //End Search || Create?>

    </div>


</div>
<?php endforeach; ?>


<?php if(is_editable()): ?>

    <script type="text/html" id="related-group-template">
        <div class="bloco js-related-group" data-related-group="{{group}}" id="group-id {{group}}">

            <h3 class="subtitulo js-related-group-name">{{group}}</h3>

            <div class="agentes clearfix js-relatedAgentsContainer">
			<span class="js-search hltip" title="Adicionar Integrante a este Grupo"
                  data-emptytext=""
                  data-search-box-width="400px"
                  data-search-box-placeholder="Adicionar Integrante"
                  data-entity-controller="agent"
                  data-search-result-template="#agent-search-result-template"
                  data-target-action="append"
                  data-selection-target=".js-relatedAgentsContainer"
                  data-selection-template="#agent-response-template"
                  data-no-result-template="#agent-response-no-results-template"
                  data-selection-format="createAgentRelation"
                  data-disable-buttons="1"
                ></span>
            </div>


        </div>
    </script>

    <script type="text/html" id="agent-response-template" class="js-mustache-template">
        <div class="avatar" data-id="{{id}}" style="background-image: url({{thumbnail}})">
            <div class="descricao-do-agente">
                <h1 class="js-relatedAgent-name"><a href="{{singleUrl}}">{{name}}</a></h1>
                <div class="objeto-meta">
                <div>
                    <span class="label">área de atuação:</span>
                    {{areas}}
                </div>
                <div><span class="label">tipo:</span>{{type.name}}</div>
            </div>
                <div class="clearfix"><span class="label">Permitir editar:</span> <div class="slider-frame"><span class="slider-button">Não</span></div></div>
                <div class="textright"><button type="submit" class="bt-remove-agent js-remove-agent">Excluir</button></div>
            </div>
        </div>
    </script>

    <script type="text/html" id="agent-response-no-results-template">
        <p class="mensagem alerta">Aparentemente o agente procurado ainda não se encontra registrado em nosso sistema. Tente uma nova busca ou antes de continuar, adicione este agente clicando no botão abaixo.</p>
        <p class="textright bottom">
            <a class="botao adicionar js-add-agent staging-hidden" data-group="{{group}}" data-dialog="#dialog-adicionar-integrante" href="#" data-button-initialized="false" onclick="MapasCulturais.RelatedAgentsEditables.openCreateAgentDialog(this); return false;">
                adicionar agente
            </a>
        </p>
    </script>
    <!--END Search Templates-->
<?php endif; ?>
<?php if(is_editable()): ?>
<div id="dialog-adicionar-integrante" class="js-dialog" title="Adicionar Integrante">
    <!--div class="avatar js-relatedAgent-avatar" ></div-->
    Nome Artístico:
    <h2><span class="js-editable-container"><span class="js-related-editable" data-related-edit="agent[name]" data-original-title="Nome Artístico" data-placeholder="Nome Artístico" data-emptytext="Nome Artístico" ></span></span></h2>
    <input type='hidden' class="js-related-editable js-group" data-related-edit="group" />
    <input id='related-invite' type='hidden' class="js-related-editable js-invite" data-related-edit="invite" />

    <label class="clear " style="display:inline-block"><input type="radio" checked name="invite" value="1" onclick="$('.agente-mais-info').addClass('escondido'); $('#related-invite').editable('setVal',1)"> Quero Convidar este agente cultural</label>
    <label class="clear " style="display:inline-block"><input type="radio" name="invite" value="0" onclick="$('.agente-mais-info').removeClass('escondido'); $('#related-invite').editable('setVal',0)"> Este agente é meu</label>
    <p class="clear privado"><span class="label">Email Privado:</span> <span class="js-editable-container"><span class="js-related-editable" data-related-edit="agent[emailPrivado]" data-placeholder="Email Privado" data-emptytext="Email Privado"></span></span></p>
    <!--a href="#" onclick="$('.agente-mais-info').toggleClass('hidden'); return false;"><i class="icon-plus"></i>mais info</a-->

    <div class="agente-mais-info escondido">

        <p class="clear privado"><span class="label">Nome Completo:</span> <span class="js-editable-container"><span class="js-related-editable" data-related-edit="agent[nomeCompleto]" data-placeholder="Nome Completo" data-emptytext="Nome Completo"></span></span></p>
        <p class="clear privado"><span class="label">Telefone 1:</span> <span class="js-editable-container"><span class="js-related-editable" data-related-edit="agent[telefone1]" data-placeholder="Telefone 1" data-emptytext="Telefone 1"></span></span></p>
        <p class="clear privado"><span class="label">CPF/CNPJ:</span> <span class="js-editable-container"><span class="js-related-editable" data-related-edit="agent[documento]" data-placeholder="CPF/CNPJ" data-emptytext="CPF/CNPJ"></span></span></p>
        <p class="clear">
            <span class="label">Mini biografia:</span>
            <span class="js-editable-container"><span class="js-related-editable" data-related-edit="agent[shortDescription]" data-type="textarea"><?php //echo $relatedAgent->shortDescription; ?></span></span>
        </p>
    </div>
    <button class="related-submit js-related-submit" data-action="create" data-target="#dialog-adicionar-integrante">Enviar</button>
</div>
<!--#dialog-adicionar-integrante Modal que adiciona integrante não cadastrado-->
<div id="dialog-related-groups" class="js-dialog" title="Adicionar Grupo de Agentes" >
    <?php if($this->controller->action == 'create'): ?>
        <span class='js-dialog-disabled' data-message='Para adicionar agentes relacionados você primeiro deve salvar.'></span>
    <?php else: ?>
    <form class="js-metalist-form">
        <label ><span>Nome</span> <input type="text"> </label>
        <div class="js-metalist-item-delete" style="display: none;">Excluir</div>
        <input type="submit">
    </form>
    <?php endif; ?>
</div>
<!-- #dialog-related-groups modal que adiciona grupo de agentes -->
<div class="bloco textright">
	<a class="botao adicionar js-related-group-add js-open-dialog hltip" href="#" data-dialog="#dialog-related-groups" data-clone="js-related-group" title="Grupos de agentes podem ser usados para exibir membros de um coletivo, equipes técnicas, etc.">
		adicionar grupo de agentes
	</a>
</div>
<?php endif; ?>

<style>
    .editable-popup-botoes-escondidos .editable-buttons{
        display:none;
    }
</style>
