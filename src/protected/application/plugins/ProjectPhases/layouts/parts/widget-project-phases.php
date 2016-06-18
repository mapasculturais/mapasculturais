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
            <a class="btn btn-default add" href="<?php echo $this->controller->createUrl('createNextPhase', [$project->id]) ?>">Adicionar fase</a>
        <?php endif; ?>
        <!--<h3>Fases <?= $_of_the_type[$project->type->id] ?></h3>-->
        <ul>

        <?php foreach($phases as $phase): ?>
            <?php if($viewing_phase->equals($phase)): ?>
                <li class="active">
                    <span><?= $phase->name ?></span>
                    de
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="Data inicial"><?php echo $phase->registrationFrom ? $phase->registrationFrom->format('d/m/Y') : 'Data inicial'; ?></strong>
                    a
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="Data final"><?php echo $phase->registrationTo ? $phase->registrationTo->format('d/m/Y') : 'Data final'; ?></strong>
                    às
                    <strong class="js-editable" id="registrationTo_time" data-datetime-value="<?php echo $phase->registrationTo ? $phase->registrationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="Hora final" data-emptytext="Hora final"><?php echo $phase->registrationTo ? $phase->registrationTo->format('H:i') : ''; ?></strong>
                    .
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= $phase->singleUrl ?>"><?= $phase->name ?></a>
                    <?php if($phase->registrationFrom && $phase->registrationTo): ?>
                        - <em>de <?= $phase->registrationFrom->format('d/m/Y') ?> a <?= $phase->registrationTo->format('d/m/Y') ?> às <?= $phase->registrationTo->format('H:i') ?></em>

                    <?php elseif($phase->registrationTo): ?>
                        - <em>até <?= $phase->registrationTo->format('d/m/Y') ?> às <?= $phase->registrationTo->format('H:i') ?></em>

                    <?php elseif($phase->registrationFrom): ?>
                        - <em>a partir de <?= $phase->registrationFrom->format('d/m/Y') ?> </em>

                    <?php endif; ?>
                        
                    <?php if($phase->status === 0): ?>
                        <em>(rascunho)</em>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
                
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>