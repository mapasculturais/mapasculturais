<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('entity-header entity-cover entity-profile entity-field entity-terms entity-social-media mapas-container mapas-card');
?>

<div class="main-app">

    <entity-header :entity="entity" :editable="true"></entity-header>
    <mapas-container>

        <mapas-card class="feature">
            <template #title>
                <h3 class="card__title--title"><?php i::_e("Informações de Apresentação")?></h3>
                <p class="card__title--description"><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?></p>
            </template>
            <template #content>
                
                <div class="feature__left">

                    <div class="row">
                        <entity-cover :entity="entity"></entity-cover>
                    </div>    
                    
                    <div class="row">
                        <entity-profile :entity="entity"></entity-profile>
                        <entity-field :entity="entity" label="Nome do Evento" prop="name"></entity-field>
                        <entity-field :entity="entity" label="Subtítulo do evento" prop="subTitle"></entity-field>

                    </div>
                    
                    <div class="row">
                        <entity-field :entity="entity" prop="shortDescription"></entity-field>
                    </div>  
                    <div class="row">
                        <entity-field :entity="entity" label="Link para página ou site do evento" prop="site"></entity-field>
                    </div>
                    
                </div>

                <div class="feature__divider"></div>

                <div class="feature__right">
                    <entity-terms :entity="entity" taxonomy="area" :editable="true" title="Áreas de interesse"></entity-terms>
                    <entity-social-media :entity="entity" :editable="true"></entity-social-media>
                </div>
                

            </template>
        </mapas-card>

    </mapas-container>

</div>