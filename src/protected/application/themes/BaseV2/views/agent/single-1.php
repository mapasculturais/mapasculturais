<?php 
$this->layout = 'entity'; 
$this->import('entity-terms share-links files-list entity-links entity-owner entity-seals entity-header entity-gallery');
?>



<entity-header :entity="entity"></entity-header>


<h1>{{entity.name}}</h1>
<div v-if="entity.files.avatar">
    <img :src="entity.files.avatar?.transformations?.avatarMedium?.url">
</div>
<div>{{entity.shortDescription}}</div>


<entity-terms :entity="entity" taxonomy="area" title="Áreas de interesse" :editable="true"></entity-terms>

<entity-owner :entity="entity" title="Publicado por"></entity-owner>

<entity-seals :entity="entity" title="Verificações" :editable="true"></entity-seals>

<entity-terms :entity="entity" taxonomy="tag" title="Tags" :editable="true"></entity-terms>  

<share-links title="Compartilhar" text="Veja este link:"></share-links>



<entity-links :entity="entity" title="Links"></entity-links>

<files-list :files="entity.files.downloads" title="Arquivos para download"></files-list>

<entity-gallery :entity="entity"></entity-gallery>