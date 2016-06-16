<div class="widget">
    <ul>
    <?php foreach($phases as $phase): ?>
        <li><?php echo $phase->name ?></li>
    <?php endforeach; ?>
    </ul>
    <a class="btn btn-default add" href="<?php echo $this->controller->createUrl('createNextPhase', [$project->id]) ?>">Adicionar fase ao projeto</a>
</div>