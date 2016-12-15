<?php if (!$entity->isNew()): ?>
    <div id="eventos" ng-if="data.entity.userHasControl && data.entity.events.length" ng-controller="ProjectEventsController">

        <div class="alignright" >
            <span class="btn btn-small btn-default" ng-click="selectAll()"><?php \MapasCulturais\i::_e("marcar eventos listados");?></span>
            <span class="btn btn-small btn-default" ng-click="deselectAll()"><?php \MapasCulturais\i::_e("desmarcar eventos listados");?></span>
        </div>
        <input type="text" ng-model="data.eventFilter" ng-change="filterEvents()" placeholder="<?php \MapasCulturais\i::esc_attr_e("filtrar eventos");?>" style="width:300px;"><br>

        <div class="eventos-selecionados">
            <div class="alignright" ng-show="!data.processing">
                <span class="btn btn-small btn-default" ng-click="unpublishSelectedEvents()"><?php \MapasCulturais\i::_e("tornar rascunho");?></span>
                <span class="btn btn-small btn-success" ng-click="publishSelectedEvents()"><?php \MapasCulturais\i::_e("publicar");?></span>
            </div>
            <div ng-show="data.processing" class="mc-spinner alignright" ><img ng-src="{{data.spinnerUrl}}" /> {{data.processingText}}</div>
            {{numSelectedEvents}} {{numSelectedEvents == 1 ? 'evento selecionado' : 'eventos selecionados' }}
        </div>


        <article class="objeto clearfix" ng-repeat="event in events" ng-show="!event.hidden" ng-class="{'selected': event.selected, 'evt-publish': event.status == 1, 'evt-draft': event.status == 0}">
            <h1><input type='checkbox' ng-model="event.selected" ng-checked="event.selected">
                <a href='{{event.singleUrl}}'>{{event.name}}</a></h1>
            <div class="objeto-content clearfix">
                <div class="objeto-thumb"><img src="" ng-src="{{event['@files:avatar.avatarSmall'] ? event['@files:avatar.avatarSmall'].url : data.assets.avatarEvent }}"></div>
                <div class="objeto-resumo">
                    <ul class="event-ocurrences">
                        <li ng-repeat='occ in event.occurrences'>
                            <a href="{{occ.space.singleUrl}}">{{occ.space.name}}</a> - {{occ.rule.description}} <span ng-if='occ.rule.price'>({{occ.rule.price}})</span>
                        </li>
                    </ul>
                </div>

                <div class="objeto-meta">
                    <div><span class="label"><?php \MapasCulturais\i::_e("Status");?>:</span> {{event.status === 0 ? 'rascunho' : 'publicado'}}</div>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Autor");?>:</span> <a href='{{event.owner.singleUrl}}'>{{event.owner.name}}</a></div>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Linguagem");?>:</span> {{event.terms.linguagem.join(', ')}}</div>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Classificação");?>:</span> {{event.classificacaoEtaria}}</div>
                </div>
            </div>
        </article>
    </div>
<?php endif; ?>