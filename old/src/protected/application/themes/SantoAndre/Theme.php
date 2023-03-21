<?php
namespace SantoAndre;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    protected static function _getTexts(){
        return array(
            'site: in the region' => 'no Estado de Santo André',
            'site: of the region' => 'do Estado de Santo André',
            'site: owner' => 'Secretaria da Cultura do Estado de Santo André',
            'site: by the site owner' => 'pela Secretaria da Cultura do Estado de Santo André',

            'home: abbreviation' => "SECULT",
//            'home: colabore' => "Colabore com o Mapas Culturais",
            'home: welcome' => "CulturAZ é a plataforma livre, gratuita e colaborativa de mapeamento da Secretaria de Cultura e Turismo de Santo André sobre o cenário cultural andreense. Mais que a facilidade de se programar, o mapeamento permite conhecer a diversidade cultural que a cidade oferece e contribui na elaboração de políticas públicas. Além de conferir a agenda de shows musicais, espetáculos teatrais, sessões de cinema, saraus, entre outras, você também pode colaborar: basta criar perfil de agente cultural. A partir deste cadastro, fica mais fácil participar dos editais do Fundo de Cultura e também divulgar eventos, espaços ou projetos.",
            'home: events' => "Você pode pesquisar eventos culturais da cidade nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
            'home: agents' => "Você pode colaborar na gestão da cultura da cidade com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural andreense. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais na cidade.",
            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados pela Secretaria de Cultura e Turismo, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
            'home: home_devs' => 'stem algumas maneiras de desenvolvedores interagirem com CulturAZ. A primeira é através da nossa API, por meio da qual você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o CulturAZ é construído a partir do sofware livre Mapas Culturais, criado em parceria com o Instituto TIM, e você pode contribuir para o desenvolvimento através do GitHub.'
//
//            'search: verified results' => 'Resultados Verificados',
//            'search: verified' => "Verificados"
        );
    }

    static function getThemeFolder() {
        return __DIR__;
    }
}
