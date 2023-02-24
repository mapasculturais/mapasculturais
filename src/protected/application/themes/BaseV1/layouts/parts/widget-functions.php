<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);

$funcoes = [];
if($taxonomy = $app->getRegisteredTaxonomy($entityClass, 'funcao')){
    if(property_exists($taxonomy, 'restrictedTerms')){
        $funcoes = array_values($app->getRegisteredTaxonomy($entityClass, 'funcao')->restrictedTerms);
    }
}

sort($funcoes);

?>
<?php if($funcoes):?>
<div class="widget areas">
    <br>
    
    <h3> <?php \MapasCulturais\i::_e("Funções");?> </h3>
    <?php if($this->isEditable()): ?>
        <span id="term-funcao" class="js-editable-taxonomy" data-original-title="<?php \MapasCulturais\i::_e("Funções");?>" data-emptytext="<?php \MapasCulturais\i::_e("Selecione suas funções") ?>" data-restrict="true" data-taxonomy="funcao"><?php echo implode('; ', $entity->terms['funcao'])?></span>
    <?php else: ?>
        <?php
        foreach($funcoes as $i => $t): if(in_array($t, $entity->terms['funcao'])): ?>
            <a class="tag tag-<?php echo $this->controller->id ?>" href="<?php echo $app->createUrl('site', 'search') ?>##(<?php echo $entityName ?>:(funcao:!(<?php echo $i ?>)),global:(enabled:(<?php echo $entityName ?>:!t),filterEntity:<?php echo $entityName ?>))">
                <?php echo $t ?>
            </a>
        <?php endif; endforeach; ?>
    <?php endif;?>
</div>
<?php endif?>