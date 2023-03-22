<?php


$this->jsObject['isEditable'] = true;

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity->opportunity);

$this->addOpportunitySelectFieldsToJs($entity->opportunity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);

$this->jsObject['request']['controller'] = "registration";

$title = isset($entity) ? $this->getTitle($entity) : $this->getTitle();
$site_name = $this->dict('site: name', false);
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
        <article class="main-content registration" ng-controller="OpportunityController">
            <article ng-controller="RegistrationFieldsController">
                <?php echo $TEMPLATE_CONTENT; ?>
            </article>
        </article>
        <?php $this->part('footer', $render_data); ?>