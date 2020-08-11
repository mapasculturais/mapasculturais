<?php
$_of_the_type = [
    7	=> \MapasCulturais\i::__("do Ciclo"),
    10	=> \MapasCulturais\i::__("do Concurso"),
    28	=> \MapasCulturais\i::__("da Conferência Pública Estadual"),
    29	=> \MapasCulturais\i::__("da Conferência Pública Municipal"),
    27	=> \MapasCulturais\i::__("da Conferência Pública Nacional"),
    26	=> \MapasCulturais\i::__("da Conferência Pública Setorial"),
    19	=> \MapasCulturais\i::__("do Congresso"),
    6	=> \MapasCulturais\i::__("da Convenção"),
    23	=> \MapasCulturais\i::__("do Curso"),
    9	=> \MapasCulturais\i::__("do Edital"),
    2	=> \MapasCulturais\i::__("do Encontro"),
    13	=> \MapasCulturais\i::__("da Exibição"),
    11	=> \MapasCulturais\i::__("da Exposição"),
    14	=> \MapasCulturais\i::__("da Feira"),
    16	=> \MapasCulturais\i::__("da Festa Popular"),
    17	=> \MapasCulturais\i::__("da Festa Religiosa"),
    1	=> \MapasCulturais\i::__("do Festival"),
    22	=> \MapasCulturais\i::__("do Fórum"),
    15	=> \MapasCulturais\i::__("do Intercâmbio Cultural"),
    12	=> \MapasCulturais\i::__("da Jornada"),
    25	=> \MapasCulturais\i::__("da Jornada"),
    5	=> \MapasCulturais\i::__("da Mostra"),
    24	=> \MapasCulturais\i::__("da Oficina"),
    20	=> \MapasCulturais\i::__("da Palestra"),
    31	=> \MapasCulturais\i::__("da Parada e Desfile Cívico"),
    32	=> \MapasCulturais\i::__("da Parada e Desfile Festivo"),
    30	=> \MapasCulturais\i::__("da Parada e Desfile Militar"),
    33	=> \MapasCulturais\i::__("da Parada e Desfile Político"),
    34	=> \MapasCulturais\i::__("da Parada e Desfile de Ações Afirmativas"),
    8	=> \MapasCulturais\i::__("do Programa"),
    4	=> \MapasCulturais\i::__("da Reunião"),
    3	=> \MapasCulturais\i::__("do Sarau"),
    18	=> \MapasCulturais\i::__("do Seminário"),
    21	=> \MapasCulturais\i::__("do Simpósio"),
    35  => \MapasCulturais\i::__("da Inscrição"),
];

$viewing_phase = $this->controller->requestedEntity;


$evaluation_methods = $app->getRegisteredEvaluationMethods();

?>
<?php if($this->isEditable() || count($phases) > 0): ?>
<?php if($this->isEditable()): ?>
<edit-box id="new-opportunity-phase" position="top" title="<?php \MapasCulturais\i::esc_attr_e('Escolha o método de avaliação da nova fase') ?>"  cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel="true">
    <ul class="evaluation-methods">
        <?php foreach($evaluation_methods as $method): ?>
        <li class="evaluation-methods--item">
            <a href="<?php echo $this->controller->createUrl('createNextPhase', [$opportunity->id, 'evaluationMethod' => $method->slug]) ?>">
                <span class="evaluation-methods--name"><?php echo $method->name; ?></span>
                <p class="evaluation-methods--name"><?php echo $method->description; ?></p>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</edit-box>
<?php endif; ?>
    <div class="opportunity-phases clear">
        <?php if($this->isEditable()): ?>
            
            <a class="btn btn-default add" ng-click="editbox.open('new-opportunity-phase', $event)" ><?php \MapasCulturais\i::_e("Adicionar fase");?></a>
        <?php endif; ?>
        <ul>

        <?php foreach($phases as $phase): ?>
            <?php if($viewing_phase->equals($phase)): ?>
                <li class="active">
                    <span><?= $phase->name ?></span>
                    <?php
                    /* Translators: "de" como início de um intervalo de data *DE* 25/1 a 25/2 às 13:00 */
                    \MapasCulturais\i::_e('de'); ?>
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+25" <?php echo $phase->registrationFrom ? "data-value='".$phase->registrationFrom->format('Y-m-d') . "'" : ''?> data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Data inicial'); ?>"><?php echo $phase->registrationFrom ? $phase->registrationFrom->format( 'd/m/Y' ) : \MapasCulturais\i::__('Data inicial'); ?></strong>
                    <?php
                    /* Translators: "a" indicando intervalo de data de 25/1 *A* 25/2 às 13:00 */
                    \MapasCulturais\i::_e('a'); ?>
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+25" <?php echo $phase->registrationTo ? "data-value='".$phase->registrationTo->format('Y-m-d') . "'" : ''?> data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Data final'); ?>"><?php echo $phase->registrationTo ? $phase->registrationTo->format('d/m/Y') : \MapasCulturais\i::__('Data final'); ?></strong>
                    <?php
                    /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */
                    \MapasCulturais\i::_e('às'); ?>
                    <strong class="js-editable" id="registrationTo_time" data-viewformat="dd/mm/yyyy" data-datetime-value="<?php echo $phase->registrationTo ? $phase->registrationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="<?php \MapasCulturais\i::esc_attr_e('Hora final'); ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Hora final'); ?>"><?php echo $phase->registrationTo ? $phase->registrationTo->format('H:i') : ''; ?></strong>
                    .
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= $this->isEditable() ? $phase->editUrl : $phase->singleUrl?>"><?= $phase->name ?></a>
                    <?php if($phase->registrationFrom && $phase->registrationTo): ?>
                        - <em>
                            <?php
                            /* Translators: "de" como início de um intervalo de data *DE* 25/1 a 25/2 às 13:00 */
                            \MapasCulturais\i::_e('de'); ?>
                            <?= $phase->registrationFrom->format('d/m/Y') ?>
                            <?php
                            /* Translators: "a" indicando intervalo de data de 25/1 *A* 25/2 às 13:00 */
                            \MapasCulturais\i::_e('a'); ?>
                            <?= $phase->registrationTo->format('d/m/Y') ?>
                            <?php
                            /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */
                            \MapasCulturais\i::_e('às'); ?>
                            <?= $phase->registrationTo->format('H:i') ?>
                        </em>

                    <?php elseif($phase->registrationTo): ?>
                        - <em>
                            <?php
                            /* Translators: até uma data: até 3/11 */
                            \MapasCulturais\i::_e('até'); ?>
                            <?= $phase->registrationTo->format('d/m/Y') ?>
                            <?php
                            /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */
                            \MapasCulturais\i::_e('às'); ?>
                            <?= $phase->registrationTo->format('H:i') ?>
                        </em>

                    <?php elseif($phase->registrationFrom): ?>
                        - <em>

                            <?php
                            /* Translators: "a partir de" uma data: a partir de 25/11 */
                            \MapasCulturais\i::_e('a partir de'); ?>
                            <?= $phase->registrationFrom->format('d/m/Y') ?>
                        </em>

                    <?php endif; ?>

                    <?php if($phase->status === 0): ?>
                        <em><?php \MapasCulturais\i::_e('(rascunho)'); ?></em>
                    <?php endif; ?>
                </li>
            <?php endif; ?>

        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
