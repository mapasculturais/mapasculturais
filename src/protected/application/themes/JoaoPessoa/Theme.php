<?php
namespace JoaoPessoa;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    protected static function _getTexts(){
        return array(
            'site: name' => 'JP Cultura',
            'site: in the region' => 'na cidade de João Pessoa',
            'site: of the region' => 'da cidade de João Pessoa',
            'site: owner' => 'Fundação Cultural de João Pessoa',
            'site: by the site owner' => 'pela Fundação Cultural de João Pessoa',

            'home: abbreviation' => "FUNJOPE",
//            'home: colabore' => "Colabore com o Mapas Culturais",
            'home: welcome' => "O JP Cultura é a plataforma livre, gratuita e colaborativa de mapeamento da Fundação Cultural de João Pessoa – Funjope, sobre o cenário cultural pessoense. Ficou mais fácil se programar para conhecer as opções culturais que a cidade oferece: shows musicais, espetáculos teatrais, saraus, cultura popular, exposições, entre outras. Além de conferir a agenda de eventos, você também pode colaborar na gestão da cultura da cidade: basta criar seu perfil de agente cultural. A partir deste cadastro, fica mais fácil participar dos editais de fomento às artes da Prefeitura e também divulgar seus eventos, espaços ou projetos.",
//            'home: events' => "Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
//            'home: agents' => "Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural paulistana. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
//            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.",
//            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
//            'home: home_devs' => 'Existem algumas maneiras de desenvolvedores interagirem com o Mapas Culturais. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Mapas Culturais é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.',
//
            'search: verified results' => 'Resultados de Governo',
//            'search: verified' => "Verificados"
            );
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
                $this->documentMeta[$key] = array('property' => $meta['property'] , 'content' => $app->view->asset('img/share-jp.png', false));
            }
        }
    }
}
