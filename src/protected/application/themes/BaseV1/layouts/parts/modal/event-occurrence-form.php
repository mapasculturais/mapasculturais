<script id="event-occurrence-form" type="text/html" class="js-mustache-template">
    <h2><?php \MapasCulturais\i::_e("Novo local e data");?></h2>
    <?php $this->renderModalFor('space', true, "", "", false); ?>

    <form action="{{formAction}}" method="POST" id="evt-date-local">
        <div class="alert danger hidden"></div>
        <input class="event-id" type="hidden" name="eventId" value="{{eventId}}"/>
        <input id="espaco-do-evento" type="hidden" name="spaceId" value="{{space.id}}">

        <div class="clearfix js-space">
            <label><?php $this->dict('entities: Space') ?>:</label><br>
            <span class="js-search-occurrence-space"
                data-field-name='spaceId'
                data-emptytext="Selecione <?php $this->dict('entities: a space') ?>"
                data-search-box-width="400px"
                data-search-box-placeholder="<?php \MapasCulturais\i::esc_attr_e('Selecione'); ?> <?php $this->dict('entities: a space') ?>"
                data-entity-controller="space"
                data-search-result-template="#agent-search-result-template"
                data-selection-template="#agent-response-template"
                data-no-result-template="#agent-response-no-results-template"
                data-selection-format="chooseSpace"
                data-auto-open="true"
                data-value="{{space.id}}"
                title="<?php \MapasCulturais\i::esc_attr_e('Selecione'); ?> <?php $this->dict('entities: a space') ?>"
                >{{space.name}}</span>
        </div>

        <a href="javascript:void(0)" class="btn btn-default" onclick="toggleEventModal()" rel='noopener noreferrer'>
            <?php \MapasCulturais\i::esc_attr_e('Ou crie e vincule um novo espaço'); ?>
        </a>

        <!--mostrar se não encontrar o <?php $this->dict('entities: space') ?> cadastrado
        <div class="alert warning">
            Aparentemente o <?php $this->dict('entities: space') ?> procurado ainda não se encontra registrado em nosso sistema. Tente uma nova busca ou antes de continuar, adicione um <?php $this->dict('entities: new space') ?> clicando no botão abaixo.
        </div>
        <a class="btn btn-default add" href="#" rel='noopener noreferrer'>Adicionar <?php $this->dict('entities: space') ?></a>-->
        <div class="clearfix">
            <div class="grupo-de-campos">
                <label for="horario-de-inicio"><?php \MapasCulturais\i::_e("Horário inicial");?>:</label><br>
                <input id="horario-de-inicio" class="horario-da-ocorrencia js-event-time" type="text" name="startsAt" placeholder="00:00" value="{{rule.startsAt}}">
            </div>
            <div class="grupo-de-campos">
                <label for="duracao"><?php \MapasCulturais\i::_e("Duração");?>:</label><br>
                <input id="duracao" class="horario-da-ocorrencia js-event-duration" type="text" name="duration" placeholder="minutos"  value="{{rule.duration}}">
            </div>
            <div class="grupo-de-campos">
                <label for="horario-de-fim"><?php \MapasCulturais\i::_e("Horário final");?>:</label><br>
                <input id="horario-de-fim" class="horario-da-ocorrencia js-event-end-time" type="text" name="endsAt" placeholder="00:00" value="{{rule.endsAt}}">
            </div>
            <div class="grupo-de-campos">
                <span class="label"><?php \MapasCulturais\i::_e("Frequência");?>:</span><br>
                    <select name="frequency" class="js-select-frequency">
                        <option value="once" {{#rule.freq_once}}selected="selected"{{/rule.freq_once}}> <?php \MapasCulturais\i::_e("uma vez");?></option>
                        <option value="daily" {{#rule.freq_daily}}selected="selected"{{/rule.freq_daily}}> <?php \MapasCulturais\i::_e("todos os dias");?></option>
                        <option value="weekly" {{#rule.freq_weekly}}selected="selected"{{/rule.freq_weekly}}> <?php \MapasCulturais\i::_e("semanal");?></option>
                        <!-- for now we will not support monthly recurrences.
                        <option value="monthly" {{#rule.freq_monthly}}selected="selected"{{/rule.freq_monthly}}>mensal</option>
                        -->
                    </select>
                </div>
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos">
                <label for="data-de-inicio"><?php \MapasCulturais\i::_e("Data inicial");?>:</label><br>
                <input id="starts-on-{{id}}-visible" type="text" class="js-event-dates js-start-date data-da-ocorrencia" readonly="readonly" placeholder="00/00/0000" value="{{rule.screen_startsOn}}">
                <input id="starts-on-{{id}}" name="startsOn" type="hidden" data-alt-field="#starts-on-{{id}}-visible" value="{{rule.startsOn}}"/>
            </div>
            <div class="grupo-de-campos js-freq-hide js-daily js-weekly js-monthly">
                <label for="data-de-fim"><?php \MapasCulturais\i::_e("Data final");?>:</label><br>
                <input id="until-{{id}}-visible" type="text" class="js-event-dates js-end-date data-da-ocorrencia" readonly="readonly" placeholder="00/00/0000" value="{{rule.screen_until}}">
                <input id="until-{{id}}" name="until" type="hidden" value="{{rule.until}}"/>
                <!--(Se repetir mostra o campo de data final)-->
            </div>
            <div class="alignleft js-freq-hide js-weekly">
                <span class="label"><?php \MapasCulturais\i::_e("Repete");?>:</span><br>
                <div>
                    <?php $weekDaysInitials = explode('|', \MapasCulturais\i::__('D|S|T|Q|Q|S|S')); ?>
                    <label><input type="checkbox" name="day[0]" {{#rule.day.0}}checked="checked"{{/rule.day.0}}/> <?php echo $weekDaysInitials[0];?> </label>
                    <label><input type="checkbox" name="day[1]" {{#rule.day.1}}checked="checked"{{/rule.day.1}}/> <?php echo $weekDaysInitials[1];?> </label>
                    <label><input type="checkbox" name="day[2]" {{#rule.day.2}}checked="checked"{{/rule.day.2}}/> <?php echo $weekDaysInitials[2];?> </label>
                    <label><input type="checkbox" name="day[3]" {{#rule.day.3}}checked="checked"{{/rule.day.3}}/> <?php echo $weekDaysInitials[3];?> </label>
                    <label><input type="checkbox" name="day[4]" {{#rule.day.4}}checked="checked"{{/rule.day.4}}/> <?php echo $weekDaysInitials[4];?> </label>
                    <label><input type="checkbox" name="day[5]" {{#rule.day.5}}checked="checked"{{/rule.day.5}}/> <?php echo $weekDaysInitials[5];?> </label>
                    <label><input type="checkbox" name="day[6]" {{#rule.day.6}}checked="checked"{{/rule.day.6}}/> <?php echo $weekDaysInitials[6];?> </label>
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
                <label for="description"><?php \MapasCulturais\i::_e("Descrição legível do horário");?>:</label>
                <p class="form-help"><?php \MapasCulturais\i::_e("Você pode inserir uma descrição própria ou inserir a descrição gerada automaticamente clicando no botão ao lado");?>.</p>
                <div class="grupo-descricao-automatica clearfix">
                    <p id="descricao-automatica" class="alert automatic"><?php \MapasCulturais\i::_e("Descrição gerada pelo sistema automaticamente");?>.</p>
                    <a class="btn btn-default insert" rel='noopener noreferrer'></a>
                </div>
                <input type="text" name="description" value="{{rule.description}}" placeholder="<?php \MapasCulturais\i::esc_attr_e("Coloque neste campo somente informações sobre a data e hora desta ocorrência do evento.");?>">
            </div>
        </div>
        <div class="clearfix">
            <div class="grupo-de-campos" >
                <label for="price"><?php \MapasCulturais\i::_e("Preço");?>:</label><br>
                <input type="text" name="price" value="{{rule.price}}">
            </div>
        </div>
        <footer class="clearfix">
            <input type="submit" value="<?php \MapasCulturais\i::esc_attr_e("Enviar");?>">
        </footer>
    </form>
</script>

