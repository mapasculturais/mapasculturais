<div class="widget">
    <h4>Fases</h4>
    <ul>
    <?php foreach($phases as $phase): ?>
        <li><a href="<?php echo $phase->singleUrl ?>"><?php echo $phase->name ?></a></li>
    <?php endforeach; ?>
    </ul>
    <a class="btn btn-default add" href="<?php echo $this->controller->createUrl('createNextPhase', [$project->id]) ?>">Adicionar fase ao projeto</a>
</div>