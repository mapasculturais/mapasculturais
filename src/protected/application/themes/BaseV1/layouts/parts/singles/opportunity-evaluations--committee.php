<?php
use MapasCulturais\i;

$this->addOpportunityEvaluationCommitteeToJs($entity);

$method = $entity->getEvaluationMethod();
?>
<style>
    .committee {
        margin: 1em;
        padding:1em;
        background: #eee;
        border-bottom: 1px solid #aaa;
    }

    .committee .committee--info img {
        height: 48px;
        width: 48px;
        margin-right: 1em;
        float:left;
    }

    .committee .committee--info .committee--name {
        font-size:15px;
        font-weight: bold;
    }

    .committee .committee--fetch {
        margin-top:1em;
    }

    .committee .committee--fetch input:first-of-type {
        width:75px;
    }
    .committee .committee--fetch input:last-of-type {
        width: 80%;
    }

    .committee .committee--fetch input::placeholder {
        color:#bbb;
        font-style: italic;
    }

</style>
<div class="agentes-relacionados">
    <div class="registration-fieldset">
        <h4><?php i::_e('Comissão de Avaliação'); ?></h4>
        <?php if($method->fetchRegistrations()): ?>
            <div id='status-info' class="alert info">
                <p>
                    <?php \MapasCulturais\i::_e("Se você quiser <strong>dividir as inscrições</strong> entre os avaliadores você pode utilizar os <strong>campos de fatiamento</strong> para cada avaliador. Você pode dividir as inscrições pelo final dos números das inscrições e/ou pela categoria definida nas inscrições."); ?>
                </p>
                <p>
                    <?php \MapasCulturais\i::_e("No <strong>primeiro</strong> campo do fatiamento informe o <strong>final do número de inscrição</strong>, de acordo com os exemplos abaixo.") ?>
                    <ul>
                        <li><?php \MapasCulturais\i::_e("<strong>00-09</strong> - para as inscrições com final entre 0 e 9</li>");?></li>
                        <li><?php \MapasCulturais\i::_e("<strong>10-60</strong> - para as inscrições com final entre 10 e 60</li>");?></li>
                        <li><?php \MapasCulturais\i::_e("<strong>61-99</strong> - para as inscrições com final entre 61 e 99</li>");?></li>
                    </ul>
                </p>
                <p>
                    <?php \MapasCulturais\i::_e("No <strong>segundo</strong> campo do fatiamento informe a(s) <strong>categoria(s) de inscrição</strong>.")?>
                </p>
                <div class="close"></div>
            </div>
        <?php endif; ?>
            <div class="committee" ng-repeat="admin in data.committee">
                <div class="committee--info ">
                    <span class="btn btn-danger delete alignright" ng-click="deleteAdminRelation(admin)"><?php i::_e("Excluir");?></span>
                    <img class="committee--avatar" ng-src="{{avatarUrl(admin.agent)}}" />
                    <span class="committee--name" >{{admin.agent.name}}</span>
                    <div ng-if="admin.agent.terms.area">{{admin.agent.terms.area.join(', ')}}</div>
                </div>
                <?php if($method->fetchRegistrations()): ?>
                    <div class="committee--fetch clear">
                        <label class="hltip" title="Fatiamento das inscrições: use para dividir as inscrições entre os avaliadores"> <?php i::_e('Fatiamento'); ?> </label><br>
                        <input ng-model="config['fetch'][admin.agentUserId]" ng-model-options="{ debounce: 1000, updateOn: 'blur'}" placeholder="<?php i::_e('0-9') ?>"/>
                        <input ng-model="config['fetchCategories'][admin.agentUserId]" ng-model-options="{ debounce: 1000, updateOn: 'blur'}"  placeholder="<?php i::_e('Categorias separadas por ponto e vírgula') ?>"/>
                    </div>
                <?php endif; ?>
            </div>
        <p ng-if="committee.length < 1"><?php i::_e('Não há nenhum avaliador definido.'); ?></p>
        <span class="btn btn-default add" ng-click="editbox.open('add-committee-agent', $event)" ><?php i::esc_attr_e('Adicionar avaliador'); ?></span>

        <edit-box ng-if="isEditable" id="add-committee-agent" position="right" title="Adicionar agente à comissão de avaliadores" cancel-label="Cancelar" close-on-cancel='true'>
            <find-entity entity="agent" api-query="findQuery" no-results-text="<?php i::esc_attr_e('Nenhum agente encontrado'); ?>" description="" spinner-condition="false" select="createAdminRelation"></find-entity>
        </edit-box>
    </div>
</div>
