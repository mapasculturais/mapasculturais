<?php 
$this->layout = 'entity'; 
$this->import('
    mapas-container mapas-card
    entity-terms share-links entity-files-list entity-links entity-owner entity-seals entity-header entity-gallery entity-social-media');
?>
<div class="main-app single-1">

    <entity-header :entity="entity"></entity-header>    
    
    <mapas-container>        
        <div class="divider"></div>
        
        <main>
            <div class="row">
                <div class="col-12">
                    <h3>Endereço</h3>
                    <p>
                        <span v-if="entity.En_Nome_Logradouro">{{entity.En_Nome_Logradouro}},</span>
                        <span v-if="entity.En_Num">{{entity.En_Num}},</span>
                        <span v-if="entity.En_Bairro">{{entity.En_Bairro}}.</span>
                        <span v-if="entity.En_CEP">CEP: {{entity.En_CEP}}.</span>
                        <span v-if="entity.En_Municipio">{{entity.En_Municipio}}/</span>
                        <span v-if="entity.En_Estado">{{entity.En_Estado}}</span>
                        <span v-else> sem endereço </span>
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h2>Descrição Detalhada</h2>
                    <p>{{entity.longDescription}}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <entity-files-list :entity="entity" group="downloads" title="Arquivos para download"></entity-files-list>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <entity-gallery :entity="entity"></entity-gallery>
                </div>
            </div>
        </main>
        
        <aside>         
            <div class="row">
                <div class="col-12">
                    <entity-terms :entity="entity" taxonomy="area" title="Areas de atuação"></entity-terms>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <entity-social-media :entity="entity"></entity-social-media>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <entity-seals :entity="entity" title="Verificações"></entity-seals>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <entity-terms :entity="entity" taxonomy="tag" title="Tags"></entity-terms>  
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <entity-owner :entity="entity" title="Publicado por"></entity-owner>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <share-links title="Compartilhar" text="Veja este link:"></share-links>
                </div>
            </div>
        </aside>
        
    </mapas-container>
</div>