<?php
$app->hook("template(embedtools.evaluationforms.embedtools-article):after", function() use ($entity){
    $this->part("evaluation--form", ['entity' => $entity]);
});

