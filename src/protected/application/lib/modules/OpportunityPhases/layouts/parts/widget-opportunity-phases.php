<?php
use MapasCulturais\i;
use OpportunityPhases\Module;

$_of_the_type = [
    7	=> i::__("do Ciclo"),
    10	=> i::__("do Concurso"),
    28	=> i::__("da Conferência Pública Estadual"),
    29	=> i::__("da Conferência Pública Municipal"),
    27	=> i::__("da Conferência Pública Nacional"),
    26	=> i::__("da Conferência Pública Setorial"),
    19	=> i::__("do Congresso"),
    6	=> i::__("da Convenção"),
    23	=> i::__("do Curso"),
    9	=> i::__("do Edital"),
    2	=> i::__("do Encontro"),
    13	=> i::__("da Exibição"),
    11	=> i::__("da Exposição"),
    14	=> i::__("da Feira"),
    16	=> i::__("da Festa Popular"),
    17	=> i::__("da Festa Religiosa"),
    1	=> i::__("do Festival"),
    22	=> i::__("do Fórum"),
    15	=> i::__("do Intercâmbio Cultural"),
    12	=> i::__("da Jornada"),
    25	=> i::__("da Jornada"),
    5	=> i::__("da Mostra"),
    24	=> i::__("da Oficina"),
    20	=> i::__("da Palestra"),
    31	=> i::__("da Parada e Desfile Cívico"),
    32	=> i::__("da Parada e Desfile Festivo"),
    30	=> i::__("da Parada e Desfile Militar"),
    33	=> i::__("da Parada e Desfile Político"),
    34	=> i::__("da Parada e Desfile de Ações Afirmativas"),
    8	=> i::__("do Programa"),
    4	=> i::__("da Reunião"),
    3	=> i::__("do Sarau"),
    18	=> i::__("do Seminário"),
    21	=> i::__("do Simpósio"),
    35  => i::__("da Inscrição"),
];

$viewing_phase = $this->controller->requestedEntity;


$evaluation_methods = $app->getRegisteredEvaluationMethods();

$last_created_phase = Module::getLastCreatedPhase($opportunity);
?>
<?php if($this->isEditable() || count($phases) > 0): ?>
<?php if($this->isEditable()): ?>
<div ng-controller="OpportunityPhasesController">
<edit-box id="new-opportunity-phase" position="top" title="<?php i::esc_attr_e('Criar nova fase') ?>"  cancel-label="<?php i::esc_attr_e("Cancelar");?>" close-on-cancel="true" submit-label="{{data.step == 1 ? '<?php i::_e("Avançar");?>' : '<?php i::_e("Criar");?>' }}" on-cancel="newPhaseEditBoxCancel" on-submit="newPhaseEditBoxSubmit" spinner-condition=data.spinner>
	<?php $this->applyTemplateHook('new-phase-form', 'begin') ?>
    <ul ng-if="data.step == 1" class="evaluation-methods">
	    <?php $this->applyTemplateHook('new-phase-form-step1', 'begin') ?>
        <?php foreach($evaluation_methods as $method): ?>
        <label for="evaluationItem-<?php echo $method->slug; ?>">
        <li class="evaluation-methods--item">
            <input type="radio" id="evaluationItem-<?php echo $method->slug; ?>" ng-change="data.step = 2" name="evaluationMethod" value="<?php echo $method->slug ?>" ng-model="newPhasePostData.evaluationMethod">
            <?php echo $method->name; ?>
            <p class="evaluation-methods--name"><?php echo $method->description; ?></p>
        </li>
        </label>
        <?php endforeach; ?>
	    <?php $this->applyTemplateHook('new-phase-form-step1', 'end') ?>
    </ul>
    <div ng-if="data.step == 2">
        <?php $this->applyTemplateHook('new-phase-form-step2', 'begin') ?>
        <ul class="evaluation-methods">
            <?php foreach($evaluation_methods as $method): ?>
                <label>
                    <li ng-if="newPhasePostData.evaluationMethod=='<?php echo $method->slug; ?>'" class="evaluation-methods--item">
                    <input type="radio" value="<?php echo $method->slug ?>" checked>
                <?php echo $method->name; ?>
                <p class="evaluation-methods--name"><?php echo $method->description; ?></p>
            </li>
                </label>
            <?php endforeach; ?>
        </ul>
        <hr style="height:1px;border-width:0;color:gray;">
        <ul class="evaluation-methods">
            <li class="evaluation-methods--item">
                <input type="checkbox" name="lastPhase" id="lastPhase" ng-model="newPhasePostData.isLastPhase" ng-false-value="">
                <label for="lastPhase"><?php i::_e("Está será a última fase"); ?></label>
                <p class="evaluation-methods--name"><?php i::_e("Assinale apenas se for a fase final"); ?></p>
            </li>
        </ul>
        <?php $this->applyTemplateHook('new-phase-form-step2', 'end') ?>
    </div>
	<?php $this->applyTemplateHook('new-phase-form', 'end') ?>
</edit-box>
</div>
<?php endif; ?>
    <div class="opportunity-phases clear">
        <?php if($this->isEditable()): ?>
            <?php if(!$last_created_phase->isLastPhase): ?>
                    <a class="btn btn-default add" ng-click="editbox.open('new-opportunity-phase', $event)"  rel='noopener noreferrer'><?php i::_e("Adicionar fase");?></a>
            <?php endif; ?>
        <?php endif; ?>
        <ul>

        <?php foreach($phases as $phase): ?>
            <?php if($viewing_phase->equals($phase)): ?>
                <li class="active">
                    <span><?= $phase->name ?></span>
                    <?php
                    /* Translators: "de" como início de um intervalo de data *DE* 25/1 a 25/2 às 13:00 */
                    i::_e('de'); ?>
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+25" <?php echo $phase->registrationFrom ? "data-value='".$phase->registrationFrom->format('Y-m-d') . "'" : ''?> data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="<?php i::esc_attr_e('Data inicial'); ?>"><?php echo $phase->registrationFrom ? $phase->registrationFrom->format( 'd/m/Y' ) : i::__('Data inicial'); ?></strong>
                    <?php
                    /* Translators: "a" indicando intervalo de data de 25/1 *A* 25/2 às 13:00 */
                    i::_e('a'); ?>
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+25" <?php echo $phase->registrationTo ? "data-value='".$phase->registrationTo->format('Y-m-d') . "'" : ''?> data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="<?php i::esc_attr_e('Data final'); ?>"><?php echo $phase->registrationTo ? $phase->registrationTo->format('d/m/Y') : i::__('Data final'); ?></strong>
                    <?php
                    /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */
                    i::_e('às'); ?>
                    <strong class="js-editable" id="registrationTo_time" data-viewformat="dd/mm/yyyy" data-datetime-value="<?php echo $phase->registrationTo ? $phase->registrationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="<?php i::esc_attr_e('Hora final'); ?>" data-emptytext="<?php i::esc_attr_e('Hora final'); ?>"><?php echo $phase->registrationTo ? $phase->registrationTo->format('H:i') : ''; ?></strong>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= $this->isEditable() ? $phase->editUrl : $phase->singleUrl?>"><?=  $phase->name ?>
                    </a>
                    <?php if($phase->registrationFrom && $phase->registrationTo): ?>
                        - <em>
                            <?php
                            /* Translators: "de" como início de um intervalo de data *DE* 25/1 a 25/2 às 13:00 */
                            i::_e('de'); ?>
                            <?= $phase->registrationFrom->format('d/m/Y') ?>
                            <?php
                            /* Translators: "a" indicando intervalo de data de 25/1 *A* 25/2 às 13:00 */
                            i::_e('a'); ?>
                            <?= $phase->registrationTo->format('d/m/Y') ?>
                            <?php
                            /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */
                            i::_e('às'); ?>
                            <?= $phase->registrationTo->format('H:i') ?>
                        </em>

                    <?php elseif($phase->registrationTo): ?>
                        - <em>
                            <?php
                            /* Translators: até uma data: até 3/11 */
                            i::_e('até'); ?>
                            <?= $phase->registrationTo->format('d/m/Y') ?>
                            <?php
                            /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */
                            i::_e('às'); ?>
                            <?= $phase->registrationTo->format('H:i') ?>
                        </em>

                    <?php elseif($phase->registrationFrom): ?>
                        - <em>

                            <?php
                            /* Translators: "a partir de" uma data: a partir de 25/11 */
                            i::_e('a partir de'); ?>
                            <?= $phase->registrationFrom->format('d/m/Y') ?>
                        </em>

                    <?php endif; ?>

                    <?php if($phase->status === 0): ?>
                        <em><?php i::_e('(rascunho)'); ?></em>
                    <?php endif; ?>
                    <?php if($phase->isLastPhase && $opportunity->canUser('@control')): ?>
                        <em><?php i::_e('(última fase)'); ?></em>
                    <?php endif; ?>
                </li>
            <?php endif; ?>

        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>