<?php

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';
$this->jsObject['angularAppDependencies'][] = 'ui.sortable';

$this->jsObject['opportunityControl'] = $entity->canUser('@control');

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity);

$this->addOpportunitySelectFieldsToJs($entity);

$this->includeAngularEntityAssets($entity);

$this->jsObject['request']['controller'] = "opportunity";

$site_name = $this->dict('site: name', false);
$title = isset($entity) ? $this->getTitle($entity) : $this->getTitle();
$this->jsObject['evaluationConfiguration'] = $entity->evaluationMethodConfiguration;
?>
<!DOCTYPE html>
<html lang="<?php echo $app->getCurrentLCode(); ?>" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?php echo $title == $site_name ? $title : "{$site_name} - {$title}"; ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="shortcut icon" href="<?php $this->asset('img/favicon.ico') ?>" />
    <?php $this->head(isset($entity) ? $entity : null); ?>
</head>

<body <?php $this->bodyProperties() ?>>
    <section id="main-section" class="clearfix">
        <article class="main-content opportunity" ng-controller="OpportunityController">
            <?php echo $TEMPLATE_CONTENT; ?>
        </article>
        <?php $this->part('footer', $render_data); ?>