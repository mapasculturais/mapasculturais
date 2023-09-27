<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\ApiQuery;

$this->addRequestedEntityToJs($entity ? $entity->className : null, $entity ? $entity->id : null);
$query = new ApiQuery(\MapasCulturais\Entities\Opportunity::class, [
    'id' => "EQ({$entity->opportunity->id})",
    '@select' => "*"
]);
$this->jsObject['opportunity'] = $query->getFindOneResult();
$this->useOpportunityAPI();
$this->addRegistrationFieldsToJs($entity->opportunity);
?>
<?php $this->part('header', $render_data) ?>
<mc-entity #default="{entity}">
<?= $TEMPLATE_CONTENT ?>
</mc-entity>
<?php $this->part('footer', $render_data); 