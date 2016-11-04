<?php

namespace MecTeatros;

use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme {

    protected function _init() {
        $app = App::i();

        /*
         *  Modifica a consulta da API de espaços para só retornar Teatros
         *
         * @see protectec/application/conf/space-types.php
         */
        $app->hook('API.<<*>>(space).query', function(&$data, &$select_properties, &$dql_joins, &$dql_where) {
            $dql_where .= ' AND e._type >= 30 AND e._type <= 39';
        });

        parent::_init();


        $app->hook('template(space.<<create|edit|single>>.tabs):end', function(){
            //$this->part('tabs-tecnica', ['entity' => $this->data->entity]);
            echo '<li><a href="#tab-tecnica">Detalles Tecnicos</a></li>';
        });

        $app->hook('template(space.<<create|edit|single>>.tabs-content):end', function(){
            $this->part('tab-tecnica', ['entity' => $this->data->entity]);
        });

    }

    static function getThemeFolder() {
        return __DIR__;
    }
    
    protected static function _getTexts(){
        
        $texts = parent::_getTexts();
        
        return array_merge($texts, array(
            'site: name' => App::i()->config['app.siteName'],
            'site: description' => App::i()->config['app.siteDescription'],
            'site: in the region' => 'en la región',
            'site: of the region' => 'de la región',
            'site: owner' => 'Secretaría',
            'site: by the site owner' => 'por la Secretaría',

            'home: title' => "¡Bienvenidos!",
            'home: abbreviation' => "MC",
            'home: colabore' => "Colabore con Mapas Culturales",
            'home: welcome' => "Mapas Culturales es una plataforma de software libre, de cartografía cultural libre y colaborativa.",
            'home: spaces' => "Busque espacios culturales incluidos en la plataforma, con el acceso a los campos de búsqueda combinados que ayudan a la exactitud de su búsqueda. Firmar los espacios donde se desarrollan sus actividades artísticas y culturales.",
            'home: home_devs' => 'Hay varias formas en las que los desarrolladores interactúan con los mapas culturales. La primera es a través de nuestra <a href="https://github.com/LibreCoopUruguay/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Con ella usted puede acceder a los datos públicos de nuestra base de datos y utilizarlos para desarrollar aplicaciones externas. Además, los Mapas Culturales está realizado a partir de sofware libre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturales</a>, creado en asociación con el <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>,  y puede contribuir con su desarrollo a través de <a href="https://github.com/LibreCoopUruguay/mapasculturais" target="_blank">GitHub</a>. La traducción fue realizada por <a href="http://libre.coop" target="_blank">Libre Coop</a>, además de adaptarlo para el <a href="http://www.mec.gub.uy" target="_blank">M.E.C.</a>.',

            'entities: Spaces of the agent'=> 'Teatros del agente',
            'entities: Space Description'=> 'Descripción del Teatro',
            'entities: My Spaces'=> 'Mis Teatros',
            'entities: My spaces'=> 'Mis Teatros',

            'entities: no registered spaces'=> 'ningún teatro registrado',
            'entities: no spaces'=> 'ningún teatro',

            'entities: Space' => 'Teatro',
            'entities: Spaces' => 'Teatros',
            'entities: space' => 'teatro',
            'entities: spaces' => 'teatros',
            'entities: parent space' => 'teatro padre',
            'entities: a space' => 'un teatro',
            'entities: the space' => 'el teatro',
            'entities: of the space' => 'del teatro',
            'entities: In this space' => 'En este teatro',
            'entities: in this space' => 'en este teatro',
            'entities: registered spaces' => 'teatros registrados',
            'entities: new space' => 'nuevo teatro',
            'entities: space found' => 'teatro encontrado',
            'entities: spaces found' => 'teatros encontrados',

        ));
    }

    function register() {
        parent::register();

        $this->registerSpaceMetadata('teatros_aforo', array(
            'label' => 'Aforo',
            'type' => 'int',
            'validations' => [
                'v::intVal()' => 'El valor deve ser un número'
            ]
        ));

        $this->registerSpaceMetadata('teatros_boca_escenario', array(
            'label' => 'Boca de escenario (en metros)',
            'type' => 'int',
            'validations' => [
                'v::intVal()' => 'El valor deve ser un número'
            ]
        ));

        $this->registerSpaceMetadata('teatros_profundidad', array(
            'label' => 'Profundidad (en metros)',
            'type' => 'int',
            'validations' => [
                'v::intVal()' => 'El valor deve ser un número'
            ]
        ));

        $this->registerSpaceMetadata('teatros_altura', array(
            'label' => 'Altura (en metros)',
            'type' => 'int',
            'validations' => [
                'v::intVal()' => 'El valor deve ser un número'
            ]
        ));

        $this->registerSpaceMetadata('teatros_piso', array(
            'label' => 'Piso',
            'type' => 'string',
        ));

        $this->registerSpaceMetadata('teatros_equipamento_lumnico', array(
            'label' => 'Equipamento Lumnico',
            'type' => 'text',
        ));

        $this->registerSpaceMetadata('teatros_equipamento_sonido', array(
            'label' => 'Equipamento de Sonido',
            'type' => 'text',
        ));

        $this->registerSpaceMetadata('teatros_equipamento_audiovisual', array(
            'label' => 'Equipamento Audiovisual',
            'type' => 'text',
        ));


    }

}
