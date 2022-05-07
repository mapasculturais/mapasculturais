<?php 
$this->layout = 'entity'; 
$this->import('entity-terms share-links');
?>
<h1>{{entity.name}}</h1>
<div v-if="entity.files.avatar">
    <img :src="entity.files.avatar?.transformations?.avatarMedium?.url">
</div>
<div>{{entity.shortDescription}}</div>

<entity-terms :entity="entity" taxonomy="area" title="Áreas de atuação"></entity-terms>
<entity-terms :entity="entity" taxonomy="tag" title="Tags"></entity-terms>

<share-links title="Compartilhar" text="Veja este link:"></share-links>