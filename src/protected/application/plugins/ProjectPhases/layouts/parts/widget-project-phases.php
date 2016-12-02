<?php 
$_of_the_type = [
    7	=> "do Ciclo",
    10	=> "do Concurso",
    28	=> "da Conferência Pública Estadual",
    29	=> "da Conferência Pública Municipal",
    27	=> "da Conferência Pública Nacional",
    26	=> "da Conferência Pública Setorial",
    19	=> "do Congresso",
    6	=> "da Convenção",
    23	=> "do Curso",
    9	=> "do Edital",
    2	=> "do Encontro",
    13	=> "da Exibição",
    11	=> "da Exposição",
    14	=> "da Feira",
    16	=> "da Festa Popular",
    17	=> "da Festa Religiosa",
    1	=> "do Festival",
    22	=> "do Fórum",
    15	=> "do Intercâmbio Cultural",
    12	=> "da Jornada",
    25	=> "da Jornada",
    5	=> "da Mostra",
    24	=> "da Oficina",
    20	=> "da Palestra",
    31	=> "da Parada e Desfile Cívico",
    32	=> "da Parada e Desfile Festivo",
    30	=> "da Parada e Desfile Militar",
    33	=> "da Parada e Desfile Político",
    34	=> "da Parada e Desfile de Ações Afirmativas",
    8	=> "do Programa",
    4	=> "da Reunião",
    3	=> "do Sarau",
    18	=> "do Seminário",
    21	=> "do Simpósio"
];

$viewing_phase = $this->controller->requestedEntity;
?>
<?php if($this->isEditable() || count($phases) > 0): ?>
    <div class="project-phases clear">
        <?php if($this->isEditable()): ?>
            <a class="btn btn-default add" href="<?php echo $this->controller->createUrl('createNextPhase', [$project->id]) ?>"><?php \MapasCulturais\i::_e("Adicionar fase");?></a>
        <?php endif; ?>
        <!--<h3>Fases <?= $_of_the_type[$project->type->id] ?></h3>-->
        <ul>

        <?php foreach($phases as $phase): ?>
            <?php if($viewing_phase->equals($phase)): ?>
                <li class="active">
                    <span><?= $phase->name ?></span>
                    <?php 
                    /* Translators: "de" como início de um intervalo de data *DE* 25/1 a 25/2 às 13:00 */
                    \MapasCulturais\i::_e('de'); ?>
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Data inicial'); ?>"><?php echo $phase->registrationFrom ? $phase->registrationFrom->format( 'd/m/Y' ) : \MapasCulturais\i::__('Data inicial'); ?></strong>
                    <?php 
                    /* Translators: "a" indicando intervalo de data de 25/1 *A* 25/2 às 13:00 */
                    \MapasCulturais\i::_e('a'); ?>
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Data final'); ?>"><?php echo $phase->registrationTo ? $phase->registrationTo->format('d/m/Y') : \MapasCulturais\i::__('Data final'); ?></strong>
                    <?php 
                    /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */
                    \MapasCulturais\i::_e('às'); ?>
                    <strong class="js-editable" id="registrationTo_time" data-datetime-value="<?php echo $phase->registrationTo ? $phase->registrationTo->format('d/m/Y H:i') : ''; ?>" data-placeholder="<?php \MapasCulturais\i::esc_attr_e('Hora final'); ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Hora final'); ?>"><?php echo $phase->registrationTo ? $phase->registrationTo->format('H:i') : ''; ?></strong>
                    .
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= $phase->singleUrl ?>"><?= $phase->name ?></a>
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
