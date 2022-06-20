<?php 
$this->layout = 'entity'; 
$this->import('entity-terms card container share-links files-list entity-links entity-owner entity-seals entity-header entity-gallery popover image-uploader');

?>
<container class="single-1">

    <entity-header :entity="entity"></entity-header>
    
    <div class="divider"></div>
    
    <main>
        <files-list :files="entity.files.downloads" title="Arquivos para download"></files-list>
        <entity-links :entity="entity" title="Links"></entity-links>
        <entity-gallery :entity="entity"></entity-gallery>
        
    </main>
    
    <aside>
        <entity-terms :entity="entity" taxonomy="area" title="Áreas de interesse"></entity-terms>
        
        
        <entity-seals :entity="entity" title="Verificações"></entity-seals>
        
        <entity-terms :entity="entity" taxonomy="tag" title="Tags"></entity-terms>  
        
        <entity-owner :entity="entity" title="Publicado por"></entity-owner>

        <share-links title="Compartilhar" text="Veja este link:"></share-links>
    </aside>
    
</container>
<image-uploader group="avatar" ></image-uploader>