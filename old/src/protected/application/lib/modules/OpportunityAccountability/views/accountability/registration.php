<?php

$this->layout = 'nolayout';

$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";
$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($entity);
$this->addOpportunityToJs($entity->opportunity);
$this->addOpportunitySelectFieldsToJs($entity->opportunity);
$this->addRegistrationToJs($entity);
$this->includeAngularEntityAssets($entity);
$this->includeEditableEntityAssets();

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];

?>

<article class="main-content registration registration-tab" ng-controller="OpportunityController">
    <?php $this->part('singles/registration-single--header', $_params) ?>
    <?php $this->part('singles/registration-single--categories', $_params) ?>
    <?php $this->part('singles/registration-single--agents', $_params) ?>
    <?php $this->part('singles/registration-single--spaces', $_params) ?>
    <?php $this->part('singles/registration-single--fields', $_params) ?>
</article>

<script type="text/javascript">

    function setIframeHeight() {
        var body = document.body,
            html = document.documentElement;

        var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);

        window.parent.postMessage({'height': height}, MapasCulturais.baseURL);
    }

    window.addEventListener('load', (e) => {
        setIframeHeight();
    });

    window.addEventListener('message', (e) => {
        setIframeHeight();
    });

</script>