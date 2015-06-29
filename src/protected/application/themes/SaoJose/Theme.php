<?php
namespace SaoJose;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    protected static function _getTexts(){
        return array(
            'site: name' => 'Lugares da Cultura',
            'site: in the region' => 'na cidade de São José dos Campos',
            'site: of the region' => 'da cidade de São José dos Campos',
            'site: owner' => 'Fundação Cultural Cassiano Ricardo',
            'site: by the site owner' => 'pela Fundação Cultural Cassiano Ricardo',

            'home: abbreviation' => "FCCR",
            'home: colabore' => "Colabore com o Lugares da Cultura",
            'home: welcome' => "O Lugares da Cultura é uma plataforma livre, gratuita e colaborativa de mapeamento cultural de São José dos Campos. Todo(a) cidadão e cidadã pode contribuir com a plataforma inserindo dados sobre sua atividade e produção cultural. Esta ferramenta é operacionalizada pela Fundação Cultural Cassiano Ricardo e pela Prefeitura Municipal de São José dos Campos.",
            'home: events' => "Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
            'home: agents' => "Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural joseense. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.",
            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
            'home: home_devs' => 'Existem algumas maneiras de desenvolvedores interagirem com o Lugares da Cultura. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Lugares da Cultura é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.',

            'search: verified results' => 'Resultados da FCCR',
            'search: verified' => "FCCR"
        );
    }

    protected function _init() {
        parent::_init();
        $app = App::i();
        $app->hook("controller(site).render(page)", function() use ($app) {
            $page = $this->data[0];
            $app->view->bodyClasses[] = "page-" . $page;
        });

        $app->hook('view.partial(<<*>>widget-areas):after', function($part, &$html) use($app){

            if($this->controller->id !== 'agent' || $this->controller->action === 'create'){
                return;
            }
            $html = '
                <div class="widget">
                    <h3>Código do Agente</h3>
                    <div class="agent-code">' . $this->controller->requestedEntity->id . '</div>
                </div>' . $html;
        });

    }

    static function getThemeFolder() {
        return __DIR__;
    }

    public function addDocumentMetas() {
        parent::addDocumentMetas();
        if(in_array($this->controller->action, ['single', 'edit'])){
            return;
        }
        $app = App::i();
        foreach ($this->documentMeta as $key => $meta){
            if(isset($meta['property']) && ($meta['property'] === 'og:image' || $meta['property'] === 'og:image:url')){
                $this->documentMeta[$key] = array('property' => $meta['property'] , 'content' => $app->view->asset('img/share-sj.png', false));
            }
        }
    }
}
