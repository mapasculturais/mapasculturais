<?php
namespace Ceara;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    protected static function _getTexts(){
        $self = App::i()->view;
        $url_search_agents = $self->searchAgentsUrl;
        $url_search_spaces = $self->searchSpacesUrl;
        $url_search_events = $self->searchEventsUrl;
        $url_search_projects = $self->searchProjectsUrl;

        return array(
            'site: in the region' => 'no Estado do Ceará',
            'site: of the region' => 'do Estado do Ceará',
            'site: owner' => 'Secretaria da Cultura do Estado do Ceará',
            'site: by the site owner' => 'pela Secretaria da Cultura do Estado do Ceará',

            'home: abbreviation' => "SECULT",
//            'home: colabore' => "Colabore com o Mapas Culturais",
            'home: welcome' => "O Mapa Cultural do Ceará é a plataforma livre, gratuita e colaborativa de mapeamento da Secretaria da Cultura do Estado do Ceará sobre cenário cultural cearense. Ficou mais fácil se programar para conhecer as opções culturais que as cidades cearenses oferecem: shows musicais, espetáculos teatrais, sessões de cinema, saraus, entre outras. Além de conferir a agenda de eventos, você também pode colaborar na gestão da cultura do estado: basta criar seu perfil de <a href=\"$url_search_agents\" >agente cultural</a>. A partir deste cadastro, fica mais fácil participar dos editais e programas da Secretaria e também divulgar seus <a href=\"{$url_search_events}\">eventos</a>, <a href=\"{$url_search_spaces}\">espaços</a> ou <a href=\"$url_search_projects\">projetos</a>.",
//            'home: events' => "Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
//            'home: agents' => "Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural paulistana. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
//            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.",
//            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
//            'home: home_devs' => 'Existem algumas maneiras de desenvolvedores interagirem com o Mapas Culturais. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Mapas Culturais é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.',
//
//            'search: verified results' => 'Resultados Verificados',
//            'search: verified' => "Verificados"
        );
    }

    static function getThemeFolder() {
        return __DIR__;
    }

    public function addDocumentMetas() {
        parent::addDocumentMetas();
        $app = App::i();
        foreach ($this->documentMeta as $key => $meta){
            if(isset($meta['property']) && ($meta['property'] === 'og:image' || $meta['property'] === 'og:image:url')){
                $this->documentMeta[$key] = array('property' => $meta['property'] , 'content' => $app->view->asset('img/share-ca.png', false));
            }
        }
    }

   function register() {
        parent::register();

        /* Adicionando novas áreas de atuação*/
        $term = App::i()->getRegisteredTaxonomyBySlug('area');
        $terms = $term->restrictedTerms;
        $new_terms = Array('humor'=>'Humor');
        $terms = array_merge($terms, $new_terms);
        sort($terms);
        $term->restrictedTerms = $terms;

        /* Adicionando novas linguagens na listagem de eventos*/
        $language = App::i()->getRegisteredTaxonomyBySlug('linguagem');
        $languages = $language->restrictedTerms;
        $new_languages = Array('performance'=>'Performance',
        'poesia'=>'Poesia','poema'=>'Poema','sarau'=>'Sarau',
        'feira'=>'Feira','artesanato'=>'Artesanato',
        'teatro infantil'=>'Teatro infantil','arte urbana'=>'Arte urbana');
        $languages = array_merge($languages, $new_languages);
        sort($languages);
        $language->restrictedTerms = $languages;

        /* registrando o meta  dos novos campos no formulário de cadastro de agente*/
      //   $this->registerAgentMetadata(
      //     'rg', array(
      //        'private' =>true,
      //        'label' => 'Número de seu RG',
      //        'type' => 'string'
      //     )
      //   );
    }

   //  function _init() {
   //      parent::_init();
   //      $app = App::i();
    //
   //      /* Adicionando novos campos no formulário de cadastro de agentes (RG)*/
   //      $app->hook('template(agent.<<create|single|edit>>.tab-about-service):end', function() use($app){
   //      $entity = $this->controller->requestedEntity;
   //      if($this->isEditable()):
   //       echo '<p class="privado">
   //             <span class="icon icon-private-info"></span>
   //             <span class="label">RG:</span>
   //             <span class="js-editable" data-edit="rg" data-original-title="Insira o número de seu RG"
   //             data-emptytext="Insira o número de seu RG da Carteira de Identidade">'. $entity->rg .'</span> </p>';
   //          endif;
   //       });
    //
   //  }
}
