<?php
namespace SpCultura;
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
            'home: welcome' => "O SP Cultura é a plataforma livre, gratuita e colaborativa de mapeamento da Secretaria Municipal de Cultura de São Paulo sobre o cenário cultural paulistano. Ficou mais fácil se programar para conhecer as opções culturais que a cidade oferece: shows musicais, espetáculos teatrais, sessões de cinema, saraus, entre outras. Além de conferir a agenda de eventos, você também pode colaborar na gestão da cultura da cidade: basta criar seu perfil de <a href=\"$url_search_agents\" >agente cultural</a>. A partir deste cadastro, fica mais fácil participar dos editais de fomento às artes da Prefeitura e também divulgar seus <a href=\"{$url_search_events}\">eventos</a>, <a href=\"{$url_search_spaces}\">espaços</a> ou <a href=\"$url_search_projects\">projetos</a>.",
            'home: events' => "Você pode pesquisar eventos culturais da cidade nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
            'home: agents' => "Você pode colaborar na gestão da cultura da cidade com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural paulistana. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais na cidade.",
            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criado pela Secretaria Municipal de Cultura, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
            'home: colabore' => "Colabore com o SpCultura",
            'search: verified results' => 'Resultados da SMC'
        );
    }

    static function getThemeFolder() {
        return __DIR__;
    }
}
