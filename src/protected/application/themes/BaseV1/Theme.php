<?php

namespace MapasCulturais\Themes\BaseV1;

use MapasCulturais;
use MapasCulturais\App;
use MapasCulturais\Controllers\Agent;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Notification;
use Respect\Validation\length;
use MapasCulturais\i;


class Theme extends MapasCulturais\Theme {

    protected $_libVersions = array(
        'leaflet' => '0.7.3',
        'angular' => '1.5.5',
        'jquery' => '2.1.1',
        'jquery-ui' => '1.11.4',
        'select2' => '3.5.0',
        'magnific-popup' => '0.9.9',
        'x-editable' => 'jquery-editable-dev-1.5.2'
    );

    // The default fields that are queried to display the search results both on map and list modes
    public $searchQueryFields = array('id','singleUrl','name','subTitle','type','shortDescription','terms','project.name','project.singleUrl, user, owner.userId'); //user funciona bem para agente e outras entidade, não creio que seja a melhor opção.

    static function getThemeFolder() {
        return __DIR__;
    }

    static function getDictGroups(){
        $groups = [
            'site' => [
                'title' => i::__('Diversos'),
                'description' => i::__('Textos utilizados em diversos lugares do site')
            ],
            'home' => [
                'title' => i::__('Página Inicial'),
                'description' => i::__('Textos utilizados exclusivamente na home do site')
            ],
            'search' => [
                'title' => i::__('Busca / Mapa'),
                'description' => i::__('Textos utilizados exclusivamente na página de busca do site')
            ],
            'entities' => [
                'title' => i::__('Nomes das entidades'),
                'description' => i::__('Textos relativos aos nomes das entidades utilizadas no site. Em alguns casos é interessante renomear as entidades, como por exemplo em uma instalação que só exibe museus é interessante chamar os espaços de museus.')
            ],
//            'error' => [
//                'title' => 'Erros',
//                'description' => 'Textos das mensagens de erro'
//            ],
//            'roles' => [
//                'title' => 'Perfis e Papéis',
//                'description' => 'Textos referente a perfis e papéis de usuários'
//            ],
//            'taxonomies' => [
//                'title' => 'Tipologia',
//                'description' => 'Nomes da taxonomias utilizadas no site'
//            ]
        ];

        return $groups;
    }

    static function _dict() {
        $app = App::i();
        return [
            // TEXTOS GERAIS
            'site: name' => [
                'name' => i::__('nome do site'),
                'description' => i::__('usado para formar o título do site e título do compartilhamento das páginas do site'),
                'examples' => [i::__('Mapa da Cultura'), i::__('Mapa Cultural do Estado do Acre'), i::__('Mapa da Cultura de Rio Branco')],
                'text' => $app->config["app.siteName"],
                'required' => true
            ],
            'site: description' => [
                'name' => i::__('descrição do site'),
                'description' => i::__('usado principalmente como texto do compartilhamento da home do site'),
                'text' => $app->config["app.siteDescription"],
                'required' => true
            ],
            'site: owner' => [
                'name' => i::__('nome da instituição responsável pelo site'),
                'description' => i::__('usado nos lugares do site onde aparece o nome da instituição'),
                'examples' => [i::__('Ministério da Cultura'), i::__('Fundação de Cultura Elias Mansour')],
                'text' => i::__('Secretaria'),
                'required' => true
            ],
            'site: by the site owner' => [
                'name' => i::__('texto "pela instituição responsável pelo site"'),
                'description' => i::__('usado nos lugares do site onde se quer dizer que algo foi feito "pela instituição responsável"'),
                'exampless' => [i::__('pelo Ministério da Cultura'), i::__('pela Fundação de Cultura Elias Mansour')],
                'text' => i::__('pela Secretaria'),
                'required' => true
            ],
            'site: in the region' => [
                'name' => i::__('texto "na região "'),
                'description' => i::__('usado nos lugares do site onde se quer dizer "na região"'),
                'examples' => [i::__('no Brasil'), i::__('no Estado do Acre'), i::__('na cidade de Rio Branco')],
                'text' => i::__('na região'),
                'required' => true
            ],
            'site: of the region' => [
                'name' => i::__('texto "da região"'),
                'description' => i::__('usado nos lugares do site onde se quer dizer que algo é "da região"'),
                'examples' => [i::__('do Brasil'), i::__('do Estado do Acre'), i::__('da cidade de Rio Branco')],
                'text' => i::__('da região'),
                'required' => true
            ],
            'site: panel' => [
                'name' => i::__('nome do painel de controle'),
                'description' => i::__('usado principalmente como título dos links para a área administrativa do site'),
                'examples' => [i::__('Painel'), i::__('Painel de Controle'), i::__('Área Administrativa')],
                'text' => i::__('Painel de Controle')
            ],
            'site: howto' => [
                'name' => i::__('como usar do site'),
                'description' => i::__('usado para orientar o usuário a utilizar a plataforma Mapas Culturais'),
                'examples' => [i::__('como Usar'), i::__('Manual do Usuário'), i::__('Manual de Utilização')],
                'text' => i::__('Como Usar')
            ],

            // TEXTOS DA HOME DO SITE
            'home: title' => [
                'name' => i::__('título da mensagem de boas-vindas'),
                'description' => i::__('usado na home do site para desejar as boas-vindas'),
                'examples' => [i::__('Bem-vind@!'), i::__('Bem-vindo')],
                'text' => i::__('Bem-vind@!')
            ],
            'home: welcome' => [
                'name' => i::__('texto de boas-vindas'),
                'description' => i::__('texto que aparece embaixo da mensagem de boas-vindas na home do site'),
                'text' => i::__('O Mapas Culturais é uma plataforma livre, gratuita e colaborativa de mapeamento cultural.')
            ],
            'home: abbreviation' => [
                'name' => i::__('abreviação ou sigla da instituição responsável pelo site'),
                'description' => i::__('usado principalmente na home para se referir à instituição de maneira curta'),
                'examples' => [i::__('MinC'), i::__('FEM'), i::__('SECULT'), i::__('SMC')],
                'text' => i::__('MC'),
                'required' => true
            ],
            'home: logo institute url' => [
                'name' => i::__('Url da página da instituição responsável pelo site'),
                'description' => i::__('usado principalmente na home para criar um link à página da instituição responsável pelo site'),
                'examples' => [i::__($app->getBaseUrl())],
                'text' => $app->getBaseUrl(),
                'required' => true
            ],
            'home: colabore' => [
                'name' => i::__('texto do botão colabore'),
                'description' => i::__('texto do botão que chama o usuário para colaborar com o mapeamento'),
                'examples' => [i::__('Colabore com o SNIIC'), i::__('Colabore com o Mapa da Cultura'), i::__('Colabore com o SpCultura')],
                'text' => i::__('Colabore com o Mapas Culturais')
            ],
            'home: events' => [
                'name' => i::__('texto da seção "eventos" da home'),
                'description' => '',
                'text' => i::__('Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.')
            ],
            'home: agents' => [
                'name' => i::__('texto da seção "agentes" da home'),
                'description' => '',
                'text' => i::__('Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural da região. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.')
            ],
            'home: spaces' => [
                'name' => i::__('texto da seção "espaços" da home'),
                'description' => '',
                'text' => i::__('Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.')
            ],
            'home: projects' => [
                'name' => i::__('texto da seção "projetos" da home'),
                'description' => '',
                'text' => i::__('Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.')
            ],
            'home: opportunities' => [
                'name' => i::__('texto da seção "oportunidades" da home'),
                'description' => '',
                'text' => i::__('Faça a sua inscrição ou acesse o resultado de diversas convocatórias como editais, oficinas, prêmios e concursos. Você também pode criar o seu próprio formulário e divulgar uma oportunidade para outros agentes culturais.')
            ],
            'home: home_devs' => [
                'name' => i::__('texto da seção "desenvolvedores" da home'),
                'description' => '',
                'text' => i::__('Existem algumas maneiras de desenvolvedores interagirem com o Mapas Culturais. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_api.md" target="_blank" rel="noopener noreferrer">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Mapas Culturais é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank" rel="noopener noreferrer">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank" rel="noopener noreferrer">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank" rel="noopener noreferrer">GitHub</a>.')
            ],

            // TEXTOS UTILIZADOS NA PÁGINA DE BUSCA, MAPA
            'search: verified results' => [
                'name' => i::__('resultados verifiados'),
                'description' => i::__('texto do botão que aplica/remove o filtro por resultados verificados'),
                'examples' => [i::__('resultados verificados'), i::__('resultados certificados'), i::__('entidades certificadas'), i::__('entidades validadas')],
                'text' => i::__('resultados verificados')
            ],
            'search: display only verified results' => [
                'name' => i::__('exibir somente resultados verifiados'),
                'description' => i::__('texto explicativo do botão que aplica/remove o filtro por resultados verificados'),
                'examples' => [i::__('exibir somente resultados verificados'), i::__('exibir somente resultados certificados'), i::__('exibir somente entidades certificadas'), i::__('exibir somente entidades validadas')],
                'text' => i::__('exibir somente resultados verificados')
            ],
            'search: verified' => [
                'name' => i::__('label do filtro de resultados verificados'),
                'description' => i::__('texto do label do filtro de resultados que aparece quando o filtro por resultados veridicados é aplicado'),
                'examples' => [i::__('verificados'), i::__('certificados'), i::__('SMC'), i::__('SECULT')],
                'text' => i::__('verificados')
            ],


            // NOMES DAS ENTIDADES

// ======== Espaços
            'entities: Space' => [
                'name' => i::__('texto "Espaço"'),
                'description' => i::__('nome da entidade Espaço no singular'),
                'examples' => [i::__('Espaço'), i::__('Museu'), i::__('Biblioteca')],
                'text' => i::__('Espaço')
            ],
            'entities: Spaces' => [
                'name' => i::__('texto "Espaços"'),
                'description' => i::__('nome da entidade Espaço no plural'),
                'examples' => [i::__('Espaços'), i::__('Museus'), i::__('Bibliotecas')],
                'text' => i::__('Espaços')
            ],
            'entities: space' => [
                'name' => i::__('texto "espaço"'),
                'description' => i::__('nome da entidade espaço no singular'),
                'examples' => [i::__('espaço'), i::__('museu'), i::__('biblioteca')],
                'text' => i::__('espaço')
            ],
            'entities: spaces' => [
                'name' => i::__('texto "espaços"'),
                'description' => i::__('nome da entidade espaço no plural'),
                'examples' => [i::__('espaços'), i::__('museus'), i::__('bibliotecas')],
                'text' => i::__('espaços')
            ],
            'entities: My Spaces' => [
                'name' => i::__('texto "Meus Espaços"'),
                'description' => '',
                'examples' => [i::__('Meus Espaços'), i::__('Meus Museus'), i::__('Minhas Bibliotecas')],
                'text' => i::__('Meus Espaços')
            ],
            'entities: My spaces' => [
                'name' => i::__('texto "Meus espaços"'),
                'description' => '',
                'examples' => [i::__('Meus espaços'), i::__('Meus museus'), i::__('Minhas bibliotecas')],
                'text' => i::__('Meus espaços')
            ],
            'entities: Space Description' => [
                'name' => i::__('texto "Descrição do Espaço"'),
                'description' => '',
                'examples' => [i::__('Descrição do Espaço'), i::__('Descrição do Museu'), i::__('Descrição da Biblioteca')],
                'text' => i::__('Descrição do Espaço')
            ],
            'entities: parent space' => [
                'name' => i::__('texto "espaço pai"'),
                'description' => '',
                'examples' => [i::__('espaço pai'), i::__('museu pai'), i::__('biblioteca mãe')],
                'text' => i::__('espaço pai')
            ],
            'entities: a space' => [
                'name' => i::__('texto "um espaço"'),
                'description' => '',
                'examples' => [i::__('um espaço'), i::__('um museu'), i::__('uma biblioteca')],
                'text' => i::__('um espaço')
            ],
            'entities: the space' => [
                'name' => i::__('texto "o espaço"'),
                'description' => '',
                'examples' => [i::__('o espaço'), i::__('o museu'), i::__('a biblioteca')],
                'text' => i::__('o espaço')
            ],
            'entities: of the space' => [
                'name' => i::__('texto "do espaço"'),
                'description' => '',
                'examples' => [i::__('do espaço'), i::__('do museu'), i::__('da biblioteca')],
                'text' => i::__('do espaço')
            ],
            'entities: Description of the space' => [
                'name' => i::__('texto "Descrição do espaço"'),
                'description' => '',
                'examples' => [i::__('Descrição do espaço'), i::__('Descrição do museu'), i::__('Descrição da biblioteca')],
                'text' => i::__('Descrição do espaço')
            ],
            'entities: Usage criteria of the space' => [
                'name' => i::__('texto "Critérios de uso do espaço"'),
                'description' => '',
                'examples' => [i::__('Critérios de uso do espaço'), i::__('Critérios de uso do museu'), i::__('Critérios de uso da biblioteca')],
                'text' => i::__('Critérios de uso do espaço')
            ],
            'entities: In this space' => [
                'name' => i::__('texto "Neste espaço"'),
                'description' => '',
                'examples' => [i::__('Neste espaço'), i::__('Neste museu'), i::__('Nesta biblioteca')],
                'text' => i::__('Neste espaço')
            ],
            'entities: in this space' => [
                'name' => i::__('texto "neste espaço"'),
                'description' => '',
                'examples' => [i::__('neste espaço'), i::__('neste museu'), i::__('nesta biblioteca')],
                'text' => i::__('neste espaço')
            ],
            'entities: registered spaces' => [
                'name' => i::__('texto "espaços cadastrados"'),
                'description' => '',
                'examples' => [i::__('espaços cadastrados'), i::__('museus cadastrados'), i::__('bibliotecas cadastradas')],
                'text' => i::__('espaços cadastrados')
            ],
            'entities: no registered spaces' => [
                'name' => i::__('texto "nenhum espaço cadastrado"'),
                'description' => '',
                'examples' => [i::__('nenhum espaço cadastrado'), i::__('nenhum museu cadastrado'), i::__('nenhuma biblioteca cadastrada')],
                'text' => i::__('nenhum espaço cadastrado')
            ],
            'entities: no spaces' => [
                'name' => i::__('texto "nenhum espaço"'),
                'description' => '',
                'examples' => [i::__('nenhum espaço'), i::__('nenhum museu'), i::__('nenhuma biblioteca')],
                'text' => i::__('nenhum espaço')
            ],
            'entities: new space' => [
                'name' => i::__('texto "novo espaço"'),
                'description' => '',
                'examples' => [i::__('novo espaço'), i::__('novo museu'), i::__('nova biblioteca')],
                'text' => i::__('novo espaço')
            ],
            'entities: Children spaces' => [
                'name' => i::__('texto "Subespaços"'),
                'description' => '',
                'examples' => [i::__('Subespaços'), i::__('Espaços filhos'), i::__('Museus filhos'), i::__('Subespaços do museu'), i::__('Bibliotecas filhas'), i::__('Subespaços da biblioteca')],
                'text' => i::__('Subespaços')
            ],
            'entities: Add child space' => [
                'name' => i::__('texto "Adicionar subespaço"'),
                'description' => '',
                'examples' => [i::__('Adicionar subespaço'), i::__('Adicionar espaço filho'), i::__('Adicionar museu filho'), i::__('Adicionar subespaço do museu'), i::__('Adicionar biblioteca filha'), i::__('Adicionar subespaço da biblioteca')],
                'text' => i::__('Adicionar subespaço')
            ],
            'entities: space found' => [
                'name' => i::__('teto "espaço encontrado"'),
                'description' => '',
                'examples' => [i::__('espaço encontrado'), i::__('museu encontrado'), i::__('biblioteca encontrada')],
                'text' => i::__('espaço encontrado')
            ],
            'entities: spaces found' => [
                'name' => i::__('espaços encontrado'),
                'description' => '',
                'examples' => [i::__('espaços encontrados'), i::__('museus encontrados'), i::__('bibliotecas encontradas')],
                'text' => i::__('espaços encontrados')
            ],
            'entities: Spaces of the agent' => [
                'name' => i::__('texto "Espaços do agente"'),
                'description' => '',
                'examples' => [i::__('Espaços do agente'), i::__('Museus do agente'), i::__('Bibliotecas do agente')],
                'text' => i::__('Espaços do agente')
            ],




// ======== Agentes
            'entities: Agents' => [
                'name' => i::__('texto "Agentes"'),
                'description' => i::__('nome da entidade Agente no plural'),
                'examples' => [i::__('Agentes')],
                'text' => i::__('Agentes')
            ],
            'entities: agent found' => [
                'name' => i::__('texto "agente econtrado"'),
                'description' => '',
                'examples' => [i::__('agente encontrado')],
                'text' => i::__('agente encontrado')
            ],
            'entities: agents found' => [
                'name' => i::__('texto "agentes encontrados"'),
                'description' => '',
                'examples' => [i::__('agentes encontrados')],
                'text' => i::__('agentes encontrados')
            ],
            'entities: My Agents' => [
                'name' => i::__('texto "Meus Agentes"'),
                'description' => '',
                'examples' => [i::__('Meus Agentes')],
                'text' => i::__('Meus Agentes')
            ],
            'entities: My agents' => [
                'name' => i::__('texto "Meus agentes"'),
                'description' => '',
                'examples' => [i::__('Meus agentes')],
                'text' => i::__('Meus agentes')
            ],
            'entities: Agent children' => [
                'name' => i::__('texto "Sub-agentes"'),
                'description' => i::__('texto nomeia os agentes filhos, ou sub-agentes, de outro agente'),
                'examples' => [i::__('Sub-agentes'), i::__('Agentes Filhos'), i::__('Agentes')],
                'text' => i::__('Agentes')
            ],
            'entities: agent' => [
                'name' => i::__('texto "agente"'),
                'description' => i::__('nome da entidade Agente no singular em minúsculo'),
                'examples' => [i::__('agente')],
                'text' => i::__('agente')
            ],
            'entities: agents' => [
                'name' => i::__('texto "agentes"'),
                'description' => i::__('nome da entidade Agente no plural em minúsculo'),
                'examples' => [i::__('agentes')],
                'text' => i::__('agentes')
            ],




// ======== Projetos
            'entities: Projects' => [
                'name' => i::__('texto "Projetos"'),
                'description' => i::__('nome da entidade Projeto no plural'),
                'examples' => [i::__('Projetos')],
                'text' => i::__('Projetos')
            ],
            'entities: My Projects' => [
                'name' => i::__('texto "Meus Projetos"'),
                'description' => '',
                'examples' => [i::__('Meus Projetos')],
                'text' => i::__('Meus Projetos')
            ],
            'entities: My projects' => [
                'name' => i::__('texto "Meus projetos"'),
                'description' => '',
                'examples' => [i::__('Meus projetos')],
                'text' => i::__('Meus projetos')
            ],
            'entities: project found' => [
                'name' => i::__('texto "projeto encontrado"'),
                'description' => '',
                'examples' => [i::__('projeto encontrado')],
                'text' => i::__('projeto encontrado')
            ],
            'entities: projects found' => [
                'name' => i::__('texto "projetos encontrados"'),
                'description' => '',
                'examples' => [i::__('projetos encontrados')],
                'text' => i::__('projetos encontrados')
            ],
            'entities: Projects of the agent' => [
                'name' => i::__('texto "Projetos do agente"'),
                'description' => i::__('Título da listagem dos projetos do agente em seu perfil'),
                'examples' => [i::__('Projetos do agente'), i::__('Editais do agente'), i::__('Convocatórias do agente')],
                'text' => i::__('Projetos do agente')
            ],
            'entities: project' => [
                'name' => i::__('texto "projeto"'),
                'description' => i::__('nome da entidade Projeto no singular em minúsculo'),
                'examples' => [i::__('projeto')],
                'text' => i::__('projeto')
            ],
            'entities: projects' => [
                'name' => i::__('texto "projetos"'),
                'description' => i::__('nome da entidade Projeto no plural em minúsculo'),
                'examples' => [i::__('projetos')],
                'text' => i::__('projetos')
            ],

// ======== Oportunidades
            'entities: Opportunities' => [
                'name' => i::__('texto "Oportunidades"'),
                'description' => i::__('nome da entidade Oportunidade no plural'),
                'examples' => [i::__('Oportunidades')],
                'text' => i::__('Oportunidades')
            ],
            'entities: My Opportunities' => [
                'name' => i::__('texto "Minhas Oportunidades"'),
                'description' => '',
                'examples' => [i::__('Minhas Oportunidades')],
                'text' => i::__('Minhas Oportunidades')
            ],
            'entities: My opportunities' => [
                'name' => i::__('texto "Minhas oportunidades"'),
                'description' => '',
                'examples' => [i::__('Minhas oportunidades')],
                'text' => i::__('Minhas oportunidades')
            ],
            'entities: opportunity found' => [
                'name' => i::__('texto "oportunidade encontrada"'),
                'description' => '',
                'examples' => [i::__('oportunidade encontrada')],
                'text' => i::__('oportunidade encontrada')
            ],
            'entities: opportunities found' => [
                'name' => i::__('texto "oportunidades encontradas"'),
                'description' => '',
                'examples' => [i::__('oportunidades encontradas')],
                'text' => i::__('oportunidades encontradas')
            ],
            'entities: Opportunities of the agent' => [
                'name' => i::__('texto "Oportunidades do agente"'),
                'description' => i::__('Título da listagem das oportunidades do agente em seu perfil'),
                'examples' => [i::__('Oportunidades do agente'), i::__('Editais do agente'), i::__('Convocatórias do agente')],
                'text' => i::__('Oportunidades do agente')
            ],
            'entities: Opportunities of the space' => [
                'name' => i::__('texto "Oportunidades do espaço"'),
                'description' => i::__('Título da listagem das oportunidades do espaço'),
                'examples' => [i::__('Oportunidades do espaço'), i::__('Editais do espaço'), i::__('Convocatórias do espaço')],
                'text' => i::__('Oportunidades do espaço')
            ],
            'entities: Opportunities of the event' => [
                'name' => i::__('texto "Oportunidades do evento"'),
                'description' => i::__('Título da listagem das oportunidades do evento'),
                'examples' => [i::__('Oportunidades do evento'), i::__('Editais do evento'), i::__('Convocatórias do evento')],
                'text' => i::__('Oportunidades do evento')
            ],
            'entities: opportunity' => [
                'name' => i::__('texto "oportunidade"'),
                'description' => i::__('nome da entidade Oportunidade no singular em minúsculo'),
                'examples' => [i::__('oportunidade')],
                'text' => i::__('oportunidade')
            ],
            'entities: opportunities' => [
                'name' => i::__('texto "oportunidades"'),
                'description' => i::__('nome da entidade Oportunidade no plural em minúsculo'),
                'examples' => [i::__('oportunidades')],
                'text' => i::__('oportunidades')
            ],

// ======== Eventos
            'entities: Events' => [
                'name' => i::__('texto "Eventos"'),
                'description' => i::__('nome da entidade Evento no plural'),
                'examples' => [i::__('Eventos'), i::__('Ações')],
                'text' => i::__('Eventos')
            ],
            'entities: My Events' => [
                'name' => i::__('texto "Meus Eventos"'),
                'description' => '',
                'examples' => [i::__('Meus Eventos'), i::__('Minhas Ações')],
                'text' => i::__('Meus Eventos')
            ],
            'entities: My events' => [
                'name' => i::__('texto "Meus eventos"'),
                'description' => '',
                'examples' => [i::__('Meus eventos'), i::__('Minhas ações')],
                'text' => i::__('Meus eventos')
            ],
            'entities: event found' => [
                'name' => i::__('texto "evento encontrado"'),
                'description' => '',
                'examples' => [i::__('evento encontrado'), i::__('ação encontrada')],
                'text' => i::__('evento encontrado')
            ],
            'entities: events found' => [
                'name' => i::__('texto "eventos encontrados"'),
                'description' => '',
                'examples' => [i::__('eventos encontrados'), i::__('ações encontradas')],
                'text' => i::__('eventos encontrados')
            ],
            'entities: event' => [
                'name' => i::__('texto "evento"'),
                'description' => i::__('nome da entidade Evento no singular em minúsculo'),
                'examples' => [i::__('evento')],
                'text' => i::__('evento')
            ],
            'entities: events' => [
                'name' => i::__('texto "eventos"'),
                'description' => i::__('nome da entidade Evento no plural em minúsculo'),
                'examples' => [i::__('eventos')],
                'text' => i::__('eventos')
            ],

// ======== Subsites
            'entities: Subsite Description' => [
                'name' => i::__('texto "Descrição do Subsite"'),
                'description' => '',
                'examples' => [i::__('Descrição do Subsite'), i::__('Descrição da Instalação')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('Descrição do Subsite')
            ],
            'entities: My Subsites' => [
                'name' => i::__('texto "Meus Subsites"'),
                'description' => '',
                'examples' => [i::__('Meus Subsites'), i::__('Minhas Instalações')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('Meus Subsites')
            ],
            'entities: My subsites' => [
                'name' => i::__('texto "Meus subsites"'),
                'description' => '',
                'examples' => [i::__('Meus subsites'), i::__('Minhas instalações')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('Meus subsites')
            ],
            'entities: Subsite' => [
                'name' => i::__('texto "Subsite"'),
                'description' => '',
                'examples' => [i::__('Subsite'), i::__('Instalação')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('Subsite')
            ],
            'entities: no registered subsite' => [
                'name' => i::__('texto "nenhum subsite cadastrado"'),
                'description' => '',
                'examples' => [i::__('nenhum subsite cadastrado'), i::__('nenhuma instalação cadastrada')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('nenhum subsite cadastrado')
            ],
            'entities: no subsite' => [
                'name' => i::__('texto "nenhum subsite"'),
                'description' => '',
                'examples' => [i::__('nenhum subsite'), i::__('nenhuma instalação')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('nenhum subsite')
            ],
            'entities: registered subsite' => [
                'name' => i::__('texto "subsite cadastrado"'),
                'description' => '',
                'examples' => [i::__('subsite cadastrado'), i::__('instalação cadastrada')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('subsite cadastrado')
            ],
            'entities: add new subsite' => [
                'name' => i::__('texto "adicionar novo subsite"'),
                'description' => '',
                'examples' => [i::__('adicionar novo subsite'), i::__('adcionar nova instalação')],
                'skip' => true, // não aparece na configuração do subsite
                'text' => i::__('adicionar novo subsite')
            ],

// ========= Selos
            'entities: Seals' => [
                'name' => i::__('texto "Selos"'),
                'description' => i::__('nome da entidade Selos no plural'),
                'examples' => [i::__('Selos'), i::__('Selos Certificadores'), i::__('Certificações')],
                'text' => i::__('Selos')
            ],
            'entities: My Seals' => [
                'name' => i::__('texto "Meus Selos"'),
                'description' => '',
                'examples' => [i::__('Meus Selos'), i::__('Meus Selos Certificadores'), i::__('Minhas Certificações')],
                'text' => i::__('Meus Selos')
            ],
            'entities: My seals' => [
                'name' => i::__('texto "Meus selos"'),
                'description' => '',
                'examples' => [i::__('Meus selos'), i::__('Meus selos certificadores'), i::__('Minhas certificações')],
                'text' => i::__('Meus selos')
            ],

            'entities: Users and roles' => [
                'name' => i::__('texto "Usuários e papéis"'),
                'description' => '',
                'examples' => [],
                'skip' => true,
                'text' => i::__('Usuários e papéis')
            ],


            // Taxonomias
            'taxonomies:area: name' => [
                'name' => i::__('Área de Atuação'),
                'description' => i::__('Colocar qual é a área de atuação'),
                'examples' => [],
                'skip' => true,
                'text' => i::__('Área de Atuação')
            ],
            'taxonomies:area: select at least one' => [
                'name' => i::__('Selecione pelos menos uma área'),
                'description' => i::__('Precisa ter pelo menos uma área selecionada'),
                'examples' => [],
                'skip' => true,
                'text' => i::__('Selecione pelo menos uma área')
            ],
            'taxonomies:area: select' => [
                'name' => i::__('Selecione as áreas'),
                'description' => i::__('Selecionar quantas áreas for preciso'),
                'examples' => [],
                'skip' => true,
                'text' => i::__('Selecione as áreas')
            ],

            'taxonomies:linguagem: name' => [
                'name' => i::__('Linguagem'),
                'description' => i::__('Informar qual é a linguagem'),
                'examples' => [],
                'skip' => true,
                'text' => i::__('Linguagem')
            ],
            'taxonomies:linguagem: select at least one' => [
                'name' => i::__('Selecione pelos menos uma linguagem'),
                'description' => i::__('Precisa ter pelo menos uma linguagem selecionada'),
                'examples' => [],
                'skip' => true,
                'text' => i::__('Selecione pelo menos uma linguagem')
            ],
            'taxonomies:linguagem: select' => [
                'name' => i::__('Selecione as linguagens'),
                'description' => i::__('Selecionar quantas linguagens for preciso'),
                'examples' => [],
                'skip' => true,
                'text' => i::__('Selecione as linguagens')
            ],
            // Mensagens de erro
            'error:403: title' => [
                'name' => i::__('Permissão negada'),
                'description' => '',
                'examples' => [],
                'skip' => true,
                'text' => i::__('Permissão negada')
            ],
            'error:403: message' => [
                'name' => i::__('Você não tem permissão para executar esta ação'),
                'description' => '',
                'examples' => [],
                'skip' => true,
                'text' => i::__('Você não tem permissão para executar esta ação.')
            ],
            'error:404: title' => [
                'name' => i::__('Página não encontrada'),
                'description' => '',
                'examples' => [],
                'skip' => true,
                'text' => i::__('Página não encontrada.')
            ],
            'error:404: message' => [
                'name' => i::__('Messagem Error 404'),
                'description' => i::__('Messagem Error 404'),
                'examples' => [],
                'skip' => true,
                'text' => ''
            ],
            'error:500: title' => [
                'name' => i::__('Um erro inesperado aconteceu'),
                'description' => '',
                'examples' => [],
                'skip' => true,
                'text' => i::__('Um erro inesperado aconteceu')
            ],
            'error:500: message' => [
                'name' => i::__('Error 500'),
                'description' => i::__('Mensagem Error 500'),
                'examples' => [],
                'skip' => true,
                'text' => ''
            ],

        ];
    }

    protected static function _getTexts(){
        $app = App::i();
        $class = get_called_class();

        return array_map(function($e) { return $e['text']; }, $class::_dict());
    }

    function getSearchAgentsUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(enabled:(agent:!t),filterEntity:agent))";
    }

    function getSearchSpacesUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(enabled:(space:!t),filterEntity:space))";
    }

    function getSearchEventsUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(enabled:(event:!t),filterEntity:event))";
    }

    function getSearchProjectsUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(filterEntity:project,viewMode:list))";
    }

    function getSearchOpportunitiesUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(filterEntity:opportunity,viewMode:list))";
    }

    function getSearchSealsUrl(){
    	return App::i()->createUrl('site', 'search')."##(global:(enabled:(seal:!t),filterEntity:seal))";
    }

    protected function _init() {
        $app = App::i();

        $app->hook('template(seal.edit.tabs):end',function(){
            $this->part('tab',['id'=>'locked-fields', 'label'=> i::__('Bloqueio de campos')]);
        });

        $app->hook('template(seal.edit.tabs-content):end',function(){
            $entity = $this->controller->requestedEntity;
            $this->includeSealAssets();
            $this->part('singles/seal-locked-fields', ['entity'=>$entity]);
        });

        if(!$app->user->is('guest') && $app->user->profile->status < 1){
            $app->hook('view.partial(nav-main-user).params', function($params, &$name){
                $name = 'header-profile-link';
            });

            $app->hook('GET(panel.<<*>>):before, GET(<<*>>.create):before', function() use($app){
                $app->redirect($app->user->profile->editUrl);
            });
        }

        $app->hook('mapasculturais.body:before', function() use($app) {
            if($this->controller && ($this->controller->action == 'single' || $this->controller->action == 'edit' )): ?>
                <!--facebook compartilhar-->
                    <div id="fb-root"></div>
                    <script>(function(d, s, id) {
                      var js, fjs = d.getElementsByTagName(s)[0];
                      if (d.getElementById(id)) return;
                      js = d.createElement(s); js.id = id;
                      js.src = "//connect.facebook.net/<?php echo i::get_locale(); ?>/all.js#xfbml=1";
                      fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>
                <!--fim do facebook-->
                <?php
            endif;
        });

        $this->jsObject['notificationsInterval'] = $app->config['notifications.interval'];

        $this->jsObject['searchQueryFields'] = implode(',', $this->searchQueryFields);

        $this->jsObject['EntitiesDescription'] = [
        		"agent"         => Entities\Agent::getPropertiesMetadata(),
        		"event"         => Entities\Event::getPropertiesMetadata(),
        		"space"         => Entities\Space::getPropertiesMetadata(),
        		"project"       => Entities\Project::getPropertiesMetadata(),
        		"opportunity"   => Entities\Opportunity::getPropertiesMetadata(),
                        "subsite"       => Entities\Subsite::getPropertiesMetadata(),
        		"seal"          => Entities\Seal::getPropertiesMetadata()
        ];

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->assetManager->publishAsset('css/main.css.map', 'css/main.css.map');

            $this->jsObject['assets'] = array();
            $this->jsObject['templateUrl'] = array();
            $this->jsObject['spinnerUrl'] = $this->asset('img/spinner.gif', false, false);

            $this->jsObject['lcode'] = $app->config['app.lcode'];

            $this->jsObject['assets']['fundo'] = $this->asset('img/fundo.png', false, false);
            $this->jsObject['assets']['instituto-tim'] = $this->asset('img/instituto-tim-white.png', false, false);
            $this->jsObject['assets']['verifiedIcon'] = $this->asset('img/verified-icon.png', false, false);
            $this->jsObject['assets']['avatarAgent'] = $this->asset('img/avatar--agent.png', false, false);
            $this->jsObject['assets']['avatarSeal'] = $this->asset('img/avatar--seal.png', false, false);
            $this->jsObject['assets']['avatarSpace'] = $this->asset('img/avatar--space.png', false, false);
            $this->jsObject['assets']['avatarEvent'] = $this->asset('img/avatar--event.png', false, false);
            $this->jsObject['assets']['avatarProject'] = $this->asset('img/avatar--project.png', false, false);
            $this->jsObject['assets']['avatarOpportunity'] = $this->asset('img/avatar--opportunity.png', false, false);

            $this->jsObject['isEditable'] = $this->isEditable();
            $this->jsObject['isSearch'] = $this->isSearch();

            $this->jsObject['angularAppDependencies'] = [
                'entity.module.relatedAgents',
                'entity.module.subsiteAdmins',
            	'entity.module.relatedSeals',
                'entity.module.subsite',
                'entity.module.changeOwner',
                'entity.directive.editableMultiselect',
                'entity.directive.editableSingleselect',

                'mc.directive.singleselect',
                'mc.directive.multiselect',
                'mc.directive.editBox',
                'mc.directive.mcSelect',
                'mc.module.notifications',
                'mc.module.findEntity',
                'ng.module.chat',

                'ngSanitize',
            ];

            if(!$app->isEnabled('subsite') || $app->config['themes.active'] <> 'MapasCulturais\Themes\Subsite') {
              $this->jsObject['mapsDefaults'] = array(
                  'zoomMax' => $app->config['maps.zoom.max'],
                  'zoomMin' => $app->config['maps.zoom.min'],
                  'zoomDefault' => $app->config['maps.zoom.default'],
                  'zoomPrecise' => $app->config['maps.zoom.precise'],
                  'zoomApproximate' => $app->config['maps.zoom.approximate'],
                  'includeGoogleLayers' => $app->config['maps.includeGoogleLayers'],
                  'latitude' => $app->config['maps.center'][0],
                  'longitude' => $app->config['maps.center'][1]
              );
            };

            $this->jsObject['mapsTileServer'] = $app->config['maps.tileServer'];

            $this->jsObject['mapMaxClusterRadius']          = $app->config['maps.maxClusterRadius'];
            $this->jsObject['mapSpiderfyDistanceMultiplier']= $app->config['maps.spiderfyDistanceMultiplier'];
            $this->jsObject['mapMaxClusterElements']        = $app->config['maps.maxClusterElements'];
            $this->jsObject['mapGeometryFieldQuery']        = $app->config['maps.geometryFieldQuery'];

            $this->jsObject['labels'] = array(
                'agent' => \MapasCulturais\Entities\Agent::getPropertiesLabels(),
                'project' => \MapasCulturais\Entities\Project::getPropertiesLabels(),
                'opportunity' => \MapasCulturais\Entities\Opportunity::getPropertiesLabels(),
                'event' => \MapasCulturais\Entities\Event::getPropertiesLabels(),
                'space' => \MapasCulturais\Entities\Space::getPropertiesLabels(),
                'subsite' => \MapasCulturais\Entities\Subsite::getPropertiesLabels(),
                'registration' => \MapasCulturais\Entities\Registration::getPropertiesLabels(),
                'seal' => \MapasCulturais\Entities\Seal::getPropertiesLabels()

            );

            $this->jsObject['routes'] = $app->config['routes'];

            $this->addDocumentMetas();
            $this->includeVendorAssets();
            $this->includeCommonAssets();
            $this->includeChatAssets();
            $this->_populateJsObject();

            $this->enqueueScript('app', 'events-js', 'js/events.js', array('mapasculturais'));
            $this->localizeScript('singleEvents', [
                'correctErrors' => \MapasCulturais\i::__('Corrija os erros indicados abaixo.'),
                'requestAddToSpace' => \MapasCulturais\i::__('Sua requisição para adicionar este evento no espaço %s foi enviada.'),
                'notAllowed' => \MapasCulturais\i::__('Você não tem permissão para criar eventos nesse espaço.'),
                'unexpectedError' => \MapasCulturais\i::__('Erro inesperado.'),
                'confirmDescription' => \MapasCulturais\i::__('As datas foram alteradas mas a descrição não. Tem certeza que deseja salvar?'),
                'Erro'=> \MapasCulturais\i::__('Erro'),
            ]);


        });

        $app->hook('entity(<<agent|space>>).<<insert|update>>:before', function() use ($app) {

            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('type', 'type');
            $rsm->addScalarResult('name', 'name');
            $rsm->addScalarResult('cod', 'cod');

            $x = $this->location->longitude;
            $y = $this->location->latitude;

            $strNativeQuery = "SELECT type, name, cod FROM geo_division WHERE ST_Contains(geom, ST_Transform(ST_GeomFromText('POINT($x $y)',4326),4326))";

            $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);

            $divisions = $query->getScalarResult();

            foreach ($app->getRegisteredGeoDivisions() as $d) {
                $metakey = $d->metakey;
                $this->$metakey = '';
            }

            foreach ($divisions as $div) {
                $metakey = 'geo' . ucfirst($div['type']);
                $this->$metakey = $div['name'];

                $metakey2 = 'geo' . ucfirst($div['type']) . '_cod';
                $this->$metakey2 = $div['cod'];
            }
        });

        $app->hook('entity(<<agent|space|event|project|opportunity|seal>>).insert:after', function() use($app){
            if(!$app->user->is('guest')){
                if($app->config['notifications.entities.new']) {
                    $user = $this->ownerUser;
                    $dataValue = [
                        'name'          => $app->user->profile->name,
                        'entityType'    => $this->entityTypeLabel,
                        'entityName'    => $this->name,
                        'url'           => $this->origin_site,
                        'createTimestamp'=> $this->createTimestamp->format('d/m/Y - H:i')
                    ];

                    $message = $app->renderMailerTemplate('new',$dataValue);

                    $app->createAndSendMailMessage([
                        'from' => $app->config['mailer.from'],
                        'to' => $user->email,
                        'subject' => sprintf(i::__($message['title'],$this->entityTypeLabel)),
                        'body' => $message['body']
                    ]);
                }
            }
        });

        // sempre que insere uma imagem cria o avatarSmall
        $app->hook('entity(<<agent|space|event|project|opportunity|subsite|seal>>).file(avatar).insert:after', function() {
            $this->transform('avatarSmall');
            $this->transform('avatarMedium');
            $this->transform('avatarBig');
        });

        $app->hook('entity(<<agent|space|event|project|opportunity|subsite|seal>>).file(header).insert:after', function() {
            $this->transform('header');
        });

        $app->hook('entity(<<subsite>>).file(logo).insert:after', function() {
            $this->transform('logo');
        });

        $app->hook('entity(<<subsite>>).file(background).insert:after', function() {
            $this->transform('background');
        });

        $app->hook('entity(<<subsite>>).file(institute).insert:after', function() {
            $this->transform('institute');
        });

        $app->hook('entity(<<subsite>>).file(favicon).insert:after', function() {
            $this->transform('favicon');
        });

        $app->hook('entity(<<agent|space|event|project|opportunity|seal>>).file(gallery).insert:after', function() {
            $this->transform('galleryThumb');
            $this->transform('galleryFull');
        });

        $app->hook('entity(event).save:before', function() {
            $this->type = 1;
        });


        $format_doc = function($documento){
            $documento = preg_replace('#[^\d]*#','',$documento);
            $formatted = false;
            if (strlen($documento) == 11) {
                $b1 = substr($documento,0,3);
                $b2 = substr($documento,3,3);
                $b3 = substr($documento,6,3);
                $dv = substr($documento,-2);
                $formatted = "$b1.$b2.$b3-$dv";
            } else if(strlen($documento)==14) {
                $b1 = substr($documento,0,2);
                $b2 = substr($documento,2,3);
                $b3 = substr($documento,5,3);
                $b4 = substr($documento,8,4);
                $dv = substr($documento,-2);
                $formatted = "$b1.$b2.$b3/$b4-$dv";
            }

            return $formatted;
        };

        // faz a keyword buscar pelo documento do owner nas inscrições
        $app->hook('repo(Registration).getIdsByKeywordDQL.join', function(&$joins, $keyword) use($format_doc) {

            if ($format_doc($keyword)) {
                $joins .= " LEFT JOIN o.__metadata doc WITH doc.key = 'documento'";
            }
        });

        $app->hook('repo(Registration).getIdsByKeywordDQL.where', function(&$where, $keyword) use($format_doc) {

            if ($doc = $format_doc($keyword)) {
                $doc2 = trim(str_replace(['%','.','/','-'],'', $keyword));
                $where .= " OR doc.value = '$doc' OR doc.value = '$doc2'";
            }
        });

        // faz a keyword buscar pelos termos das taxonomias
        $app->hook('repo(<<*>>).getIdsByKeywordDQL.join,-repo(Registration).getIdsByKeywordDQL.join', function(&$joins, $keyword) {
            $taxonomy = App::i()->getRegisteredTaxonomyBySlug('tag');

            $class = $this->getClassName();

            $joins .= "LEFT JOIN e.__termRelations tr
                LEFT JOIN
                        tr.term
                            t
                        WITH
                            t.taxonomy = '{$taxonomy->slug}'";
        });

        $app->hook('repo(<<*>>).getIdsByKeywordDQL.where,-repo(Registration).getIdsByKeywordDQL.where', function(&$where, $keyword) {
            $where .= " OR unaccent(lower(t.term)) LIKE unaccent(lower(:keyword)) ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.join', function(&$joins, $keyword) {
            $joins .= " LEFT JOIN e.project p
                    LEFT JOIN e.__metadata m
                    WITH
                        m.key = 'subTitle'
                     JOIN e.occurrences oc
                     JOIN oc.space sp
                ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.where', function(&$where, $keyword) use($app) {
            $projects = $app->repo('Project')->findByKeyword($keyword);
            $project_ids = [];
            foreach($projects as $project){
                $project_ids = array_merge($project_ids, [$project->id], $project->getChildrenIds());
            }
            if($project_ids){
                $where .= " OR p.id IN ( " . implode(',', $project_ids) . ")";
            }
            $where .= " OR unaccent(lower(m.value)) LIKE unaccent(lower(:keyword))";
            $where .= " OR unaccent(lower(sp.name)) LIKE unaccent(lower(:keyword))";
        });

        $theme = $this;
        $app->hook("GET(site.address_by_postalcode)", function() use($app, $theme) {

            $response = $theme->getAddressByPostalCode($app->request->get('postalcode'));
            if ($response['success'] === true) {
                echo json_encode($response);
            } else {
                $app->halt(400, $response['error_msg']);
            }

        });

        //
        $app->hook('GET(subsite.single):before', function() use($app) {

            $entities = [
                'event' => $app->view->dict('entities: Events', false),
                'space' => $app->view->dict('entities: Spaces', false),
                'agent' => $app->view->dict('entities: Agents', false),
                'project' => $app->view->dict('entities: Projects', false),
                'opportunity' => $app->view->dict('entities: Opportunities', false),
            ];

            $app->view->jsObject['readable_names'] = $entities;

            $conf_data = function($metadatas, $terms){
                $new_data = [];

                // add registered terms to list
                foreach ($terms as $term => $conf) {

                    if (!$conf->slug)
                        continue;

                    $new_data[$term] = [
                        'label' => ucwords($conf->slug),
                        'type' => 'term'
                    ];
                    if ($conf->restrictedTerms)
                        $new_data['options'] = $conf->restrictedTerms;


                    // @todo: reduce type options foreach field properties
                    $new_data[$term]['types'] = [
                        'checklist' => 'Checklist',
                        'singleselect' => 'Select',
                        'text' => 'Texto',
                        'checkbox' => 'Checkbox',
                        'checkbox-verified' => 'Resultados Verificados'
                    ];
                }

                // add metadata to list
                foreach ($metadatas as $metadata => $conf) {

                    if (!$conf['label'] || $conf['isEntityRelation'])
                        continue;

                    // @todo: dev field of type date/datetime to use BET() and remove this code
                    if (isset($conf['type']) && ($conf['type'] === 'datetime' || $conf['type'] === 'date'))
                        continue;


                    $new_data[$metadata] = [
                        'label' => $conf['label'],
                        'type' => 'metadata'
                    ];

                    if (isset($conf['options']))
                        $new_data[$metadata]['options'] = $conf['options'];


                    // @todo: reduce type options foreach field properties

                    switch($conf['type']) {
                        case 'select':
                            $new_data[$metadata]['types'] = [
                                'checklist' => 'Checklist',
                                'singleselect' => 'Select',
                                'text' => 'Texto',
                                'checkbox' => 'Checkbox'
                            ];
                            break;
                        case 'string':
                        case 'text':
                            $new_data[$metadata]['types'] = [
                                'text' => 'Texto'
                            ];
                            break;
                        case 'boolean':
                            $new_data[$metadata]['types'] = [
                                'checklist' => 'Checklist',
                                'singleselect' => 'Select',
                                'text' => 'Texto',
                                'checkbox' => 'Checkbox'
                            ];
                            break;
                        default:
                            $new_data[$metadata]['types'] = [
                                'checklist' => 'Checklist',
                                'singleselect' => 'Select',
                                'text' => 'Texto',
                                'checkbox' => 'Checkbox'
                            ];
                            break;
                    }
                }

                return $new_data;
            };


            foreach ($entities as $entity => $name) {

                $app->view->jsObject['user_filters__subsite'][$entity] = $this->requestedEntity->{'user_filters__'.$entity};

                $class_name = '\MapasCulturais\Entities\\'.ucwords($entity);

                $app->view->jsObject['user_filters__conf'][$entity] = $conf_data($class_name::getPropertiesMetadata(),$app->getRegisteredTaxonomies(substr($class_name, 1)));

                $app->view->jsObject['user_filters__conf'][$entity]['tipos'] = [
                    'label' => 'Tipos',
                    'type' => 'entitytype',
                    'types' => [
                        'checklist' => 'Checklist',
                        'singleselect' => 'Select',
                        'text' => 'Texto'
                    ]
                ];

                $app->view->jsObject['user_filters__conf'][$entity]['verificados'] = [
                    'label' => 'Resultados Verificados',
                    'type' => 'metadata',
                    'addClass' => 'verified-filter',
                    'types' => [
                        'checkbox-verified' => 'Resultados Verificados'
                    ]
                ];


            }

        });

        $app->hook('template(event.<<create|edit|single>>.tab-about-service):before', function(){
            $this->part('event-attendance', ['entity' => $this->data->entity]);
        });

        // Limita acesso ao botão de download da planila de agentes a administradores
        $app->hook('enabled.agent.spreadsheet.map', function(&$enabled_export_spreadsheet_map) use ($app){
            $enabled_export_spreadsheet_map = $app->user->is('admin') ? true : false;
        });
    }

    /*
     * This methods tries to fill the address fields using the postal code
     *
     * By default it relies on brazilian CEP, but you can override this methods
     * to use another API.
     *
     * It should return an Array with an item success set to true or false.
     *
     * If true, it has to return the following fields.
     * Note: lat & lon are optional, they are not being used yet but will probably be soon
     *
     * response example:
     *
     * [
     *    'success' => true,
     *      'lat' => $json->latitude,
     *      'lon' => $json->longitude,
     *      'streetName' => $json->logradouro,
     *      'neighborhood' => $json->bairro,
     *      'city' => $json->cidade,
     *      'state' => $json->estado
     * ]
     *
     */
    function getAddressByPostalCode($postalCode) {
        $app = App::i();
        if ($app->config['cep.token']) {
            $cep = str_replace('-', '', $postalCode);
            // $url = 'http://www.cepaberto.com/api/v2/ceps.json?cep=' . $cep;
            $url = sprintf($app->config['cep.endpoint'], $cep);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($app->config['cep.token_header']) {
                // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token token="' . $app->config['cep.token'] . '"'));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(sprintf($app->config['cep.token_header'], $app->config['cep.token'])));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            $json = json_decode($output);

            if (isset($json->cep)) {
                $response = [
                    'success' => true,
                    'lat' => @$json->latitude,
                    'lon' => @$json->longitude,
                    'streetName' => @$json->logradouro,
                    'neighborhood' => @$json->bairro,
                    'city' => @$json->cidade,
                    'state' => @$json->estado
                ];
            } else {
                $response = [
                    'success' => false,
                    'error_msg' => 'Falha a buscar endereço'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'error_msg' => 'No token for CEP'
            ];
        }

        return $response;
    }

    function register() {
        $app = App::i();
        $geoDivisionsHierarchyCfg = $app->config['app.geoDivisionsHierarchy'];
        foreach ($geoDivisionsHierarchyCfg as $slug => $division) {

            // Begin backward compability version < 4.0, $division is string not a array.
            $label = $division;
            if (is_array($division)) {
                $label = $division['name'];
            }
            // End backward compability

            foreach (array('MapasCulturais\Entities\Agent', 'MapasCulturais\Entities\Space') as $entity_class) {
                $entity_types = $app->getRegisteredEntityTypes($entity_class);

                foreach ($entity_types as $type) {
                    $metadata = new \MapasCulturais\Definitions\Metadata('geo' . ucfirst($slug), array('label' => $label));
                    $app->registerMetadata($metadata, $entity_class, $type->id);

                    $metadata = new \MapasCulturais\Definitions\Metadata('geo' . ucfirst($slug). '_cod', array('label' => $label));
                    $app->registerMetadata($metadata, $entity_class, $type->id);
                }
            }
        }

        // after plugin registration that creates the configuration types
        $app->hook('app.register', function(){
            $this->view->registerMetadata('MapasCulturais\Entities\EvaluationMethodConfiguration', 'infos', [
                'label' => i::__("Textos informativos para as fichas de avaliação"),
                'serialize' => function($val){ return json_encode($val); },
                'unserialize' => function($val){ return json_decode($val); },
            ]);
        });

        $this->registerEventMetadata('event_attendance', array(
            'label' => 'Público presente',
            'type' => 'integer',
            'validations' => [
                'v::intVal()->positive()' => 'O valor deve ser um número inteiro positivo'
            ]
        ));
    }

    function getLockedFieldsSeal(){
        $exclude_list = [
            'id','_type',
            'createTimestamp',
            'status',
            'userId',
            'updateTimestamp',
            '_subsiteId',
            'geoPais',
            'geoPais_cod',
            'geoRegiao',
            'geoRegiao_cod',
            'geoEstado',
            'geoEstado_cod',
            'geoMesorregiao',
            'geoMesorregiao_cod',
            'geoMicrorregiao',
            'geoMicrorregiao_cod',
            'geoMunicipio',
            'geoMunicipio_cod',
            'geoZona',
            'geoZona_cod',
            'geoSubprefeitura',
            'geoSubprefeitura_cod',
            'geoDistrito',
            'geoDistrito_cod',
            'geoSetor_censitario',
            'geoSetor_censitario_cod',
            'event_importer_processed_file',
            'opportunityTabName',
            'useOpportunityTab',
            'sentNotification',
            'public',
            'location',

        ];
        
        $props = [
            'agent' => \MapasCulturais\Entities\Agent::getPropertiesMetadata(),
            'space' => \MapasCulturais\Entities\Space::getPropertiesMetadata(),
        ];

        $_fields = [];
        foreach($props as $entity => $values){
            foreach($values as $field => $v){
                if(!$v["isEntityRelation"] && !in_array($field,$exclude_list)){
                    $_fields[$entity][$field] = $v;
                }
            }
        }

        return $_fields;
        
    }

    function head() {
        parent::head();

        $app = App::i();

        $this->printStyles('vendor');
        $this->printStyles('app');

        $app->applyHook('mapasculturais.styles');

        $this->_printJsObject();

        $this->printScripts('vendor');
        $this->printScripts('app');

        $app->applyHook('mapasculturais.scripts');
    }

    function addDocumentMetas() {
        $app = App::i();
        $entity = $this->controller->requestedEntity;

        $site_name = $this->dict('site: name', false);
        $title = $app->view->getTitle($entity);
        $image_url = $app->view->asset('img/share.png', false);
        if ($entity) {
            $description = $entity->shortDescription ? $entity->shortDescription : $title;
            if ($entity->avatar && $entity->avatar->transform('avatarBig')){
                $image_url = $entity->avatar->transform('avatarBig')->url;
            }
        }else {
            $description = $this->dict('site: description', false);
        }
        // for responsive
        $this->documentMeta[] = array("name" => 'viewport', 'content' => 'width=device-width, initial-scale=1, maximum-scale=1.0');
        // for google
        $this->documentMeta[] = array("name" => 'description', 'content' => $description);
        $this->documentMeta[] = array("name" => 'keywords', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'author', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'copyright', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'application-name', 'content' => $site_name);

        // for google+
        $this->documentMeta[] = array("itemprop" => 'author', 'content' => $title);
        $this->documentMeta[] = array("itemprop" => 'description', 'content' => $description);
        $this->documentMeta[] = array("itemprop" => 'image', 'content' => $image_url);

        // for twitter
        $this->documentMeta[] = array("name" => 'twitter:card', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'twitter:title', 'content' => $title);
        $this->documentMeta[] = array("name" => 'twitter:description', 'content' => $description);
        $this->documentMeta[] = array("name" => 'twitter:image', 'content' => $image_url);

        // for facebook
        $this->documentMeta[] = array("property" => 'og:title', 'content' => $title);
        $this->documentMeta[] = array("property" => 'og:type', 'content' => 'article');
        $this->documentMeta[] = array("property" => 'og:image', 'content' => $image_url);
        $this->documentMeta[] = array("property" => 'og:image:url', 'content' => $image_url);
        $this->documentMeta[] = array("property" => 'og:description', 'content' => $description);
        $this->documentMeta[] = array("property" => 'og:site_name', 'content' => $site_name);

        if ($entity) {
            $this->documentMeta[] = array("property" => 'og:url', 'content' => $entity->singleUrl);
            $this->documentMeta[] = array("property" => 'og:published_time', 'content' => $entity->createTimestamp->format('Y-m-d'));

            // @TODO: modified time is not implemented
            // $this->documentMeta[] = array( "property" => 'og:modified_time',   'content' => $entity->modifiedTimestamp->format('Y-m-d'));
        }
    }

    function includeVendorAssets() {
        $versions = $this->_libVersions;

        $this->enqueueStyle('vendor', 'x-editable', "vendor/x-editable-{$versions['x-editable']}/css/jquery-editable.css", array('select2'));

        $this->enqueueScript('vendor', 'mustache', 'vendor/mustache.js');

        $this->enqueueScript('vendor', 'jquery', "vendor/jquery-{$versions['jquery']}.js");
        $this->enqueueScript('vendor', 'jquery-slimscroll', 'vendor/jquery.slimscroll.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-form', 'vendor/jquery.form.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-mask', 'vendor/jquery.mask.js', array('jquery'));
        $this->enqueueScript('vendor', 'purl', 'vendor/purl/purl.js', array('jquery'));

        // select 2
        $this->enqueueStyle('vendor', 'select2', "vendor/select2-{$versions['select2']}/select2.css");
        $this->enqueueScript('vendor', 'select2', "vendor/select2-{$versions['select2']}/select2.js", array('jquery'));

        $this->enqueueScript('vendor', 'select2-BR', 'vendor/select2_locale_'.i::get_locale().'-edit.js', array('select2'));

        $this->enqueueScript('vendor', 'poshytip', 'vendor/x-editable-jquery-poshytip/jquery.poshytip.js', array('jquery'));
        $this->enqueueScript('vendor', 'x-editable', "vendor/x-editable-{$versions['x-editable']}/js/jquery-editable-poshytip.js", array('jquery', 'poshytip', 'select2'));

        //Leaflet -a JavaScript library for mobile-friendly maps
        $this->enqueueStyle('vendor', 'leaflet', "vendor/leaflet/lib/leaflet-{$versions['leaflet']}/leaflet.css");
        $this->enqueueScript('vendor', 'leaflet', "vendor/leaflet/lib/leaflet-{$versions['leaflet']}/leaflet-src.js");

        //Leaflet Vector Layers
        $this->enqueueScript('vendor', 'leaflet-vector-layers', 'vendor/leaflet-vector-layers/dist/lvector.js', array('leaflet'));

        //Conjuntos de Marcadores
        $this->enqueueStyle('vendor', 'leaflet-marker-cluster', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.css', array('leaflet'));
        $this->enqueueStyle('vendor', 'leaflet-marker-cluster-d', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.Default.css', array('leaflet-marker-cluster'));
        $this->enqueueScript('vendor', 'leaflet-marker-cluster', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/leaflet.markercluster-src.js', array('leaflet'));

        //Controle de Full Screen
        $this->enqueueStyle('vendor', 'leaflet-fullscreen', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.css', array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-fullscreen', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.js', array('leaflet'));

        //Leaflet Label Plugin
        $this->enqueueScript('vendor', 'leaflet-label', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.label-master/dist/leaflet.label-src.js', array('leaflet'));

        //Leaflet Draw
        $this->enqueueStyle('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw.css', array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw-src.js', array('leaflet'));

        // Google Maps API only needed in site/search and space, agent and event singles, or if the location patch is active
        if (preg_match("#site|space|agent|event|subsite#", $this->controller->id) &&
            preg_match("#search|single|edit|create#", $this->controller->action)) {
            $this->includeGeocodingAssets();
        }

        //Leaflet Plugins
        $this->enqueueScript('vendor', 'leaflet-google-tile', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-plugins-master/layer/tile/Google.js', array('leaflet'));

        $this->enqueueStyle('vendor', 'magnific-popup', "vendor/Magnific-Popup-{$versions['magnific-popup']}/magnific-popup.css");
        $this->enqueueScript('vendor', 'magnific-popup', "vendor/Magnific-Popup-{$versions['magnific-popup']}/jquery.magnific-popup.js", array('jquery'));

        $this->enqueueScript('vendor', 'momentjs', 'vendor/moment.js');
        $this->enqueueScript('vendor', 'momentjs-locale', 'vendor/moment.'.i::get_locale().'.js', array('momentjs'));

        $this->enqueueScript('vendor', 'jquery-ui', "vendor/jquery-ui-{$versions['jquery-ui']}/jquery-ui.js", array('jquery'));
        $this->enqueueScript('vendor', 'jquery-ui-datepicker-locale', "vendor/jquery-ui-{$versions['jquery-ui']}/datepicker-".i::get_locale().".js", array('jquery-ui'));

        $this->enqueueScript('vendor', 'angular', "vendor/angular-{$versions['angular']}/angular.js", array('jquery', 'jquery-ui-datepicker-locale'));
        $this->enqueueScript('vendor', 'angular-sanitize', "vendor/angular-{$versions['angular']}/angular-sanitize.js", array('angular'));

        $this->enqueueScript('vendor', 'angular-rison', '/vendor/angular-rison.js', array('angular'));
        $this->enqueueScript('vendor', 'ng-infinite-scroll', '/vendor/ng-infinite-scroll/ng-infinite-scroll.js', array('angular'));

        $this->enqueueScript('vendor', 'angular-ui-date', '/vendor/ui-date-master/src/date.js', array('jquery-ui-datepicker-locale', 'angular'));
        $this->enqueueScript('vendor', 'angular-ui-sortable', '/vendor/ui-sortable/sortable.js', array('jquery-ui', 'angular'));
        $this->enqueueScript('vendor', 'angular-checklist-model', '/vendor/checklist-model/checklist-model.js', array('jquery-ui', 'angular'));

        // It Javis ColorPicker
        $this->enqueueScript('vendor', 'bootstrap-colorpicker', '/vendor/bootstrap-colorpicker/js/bootstrap-colorpicker.js');
        $this->enqueueStyle('vendor', 'bootstrap-colorpicker', '/vendor/bootstrap-colorpicker/css/bootstrap-colorpicker.css');

        // Cropbox
        $this->enqueueScript('vendor', 'cropbox', '/vendor/cropbox/jquery.cropbox.js', array('jquery'));
        $this->enqueueStyle ('vendor', 'cropbox', '/vendor/cropbox/jquery.cropbox.css');

        // Quill
        $this->enqueueScript('vendor', 'quill-js', '/vendor/quill/quill.js');
        $this->enqueueStyle ('vendor', 'quill-css', '/vendor/quill/quill.css');

    }

    function includeCommonAssets() {
        $this->getAssetManager()->publishFolder('fonts/');

        $this->enqueueStyle('app', 'main', 'css/main.css');
        $this->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');


        $this->enqueueScript('app', 'tim', 'js/tim.js');
        $this->enqueueScript('app', 'modal-js', 'js/modal.js');

        $this->localizeScript('tim', [
            'previous' => i::__('Anterior'),
            'next' => i::__('Próxima'),
            /* Translators: Count of the photo gallery slideshow: 2 of 10 */
            'counter' => i::__('%curr% de %total%'),
        ]);
        $this->enqueueScript('app', 'mapasculturais', 'js/mapasculturais.js', array('tim'));
        $this->localizeScript('mapas', [
            'agente'        => i::__('agente'),
            'espaço'        => i::__('espaço'),
            'evento'        => i::__('evento'),
            'projeto'       => i::__('projeto'),
            'opportunity'   => i::__('oportunidade'),
            'selo'          => i::__('selo'),
            'Enviar'        => i::__('Enviar'),
            'Cancelar'      => i::__('Cancelar'),
            'confirmRemoveEntity'  => i::__('Você deseja excluir a entidade?'),
            'confirmDestroyEntity'  => i::__('Você deseja excluir definitivamente a entidade? Esta operação não poderá ser desfeita.'),
        ]);
        $locale_specific_js = file_exists(dirname(__FILE__)  . '/assets/js/locale-specific/' . i::get_locale() . '.js') ? 'js/locale-specific/' . i::get_locale() . '.js' : 'js/locale-specific/default.js' ;
        $this->enqueueScript('app', 'mapasculturais-locale-specific', $locale_specific_js, array('mapasculturais'));

        $this->enqueueScript('app', 'mapasculturais-customizable', 'js/customizable.js', array('mapasculturais'));

        // This replaces the default geocoder with the google geocoder
        if (App::i()->config['app.useGoogleGeocode']){
            $this->includeGeocodingAssets();
            $this->enqueueScript('app', 'google-geocoder', 'js/google-geocoder.js', array('mapasculturais-customizable'));
        }

        $this->enqueueScript('app', 'ng-mapasculturais', 'js/ng-mapasculturais.js', array('mapasculturais'));
        $this->enqueueScript('app', 'mc.module.notifications', 'js/ng.mc.module.notifications.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'usermanager.app', 'js/ng.user-management.js', array('mapasculturais'));
        $this->localizeScript('moduleNotifications', [
            'error'    => i::__('There was an error'),
        ]);


        if ($this->isEditable())
            $this->includeEditableEntityAssets();

        if (App::i()->config('mode') == 'staging')
            $this->enqueueStyle('app', 'staging', 'css/staging.css', array('main'));
    }

    function includeIbgeJS() {
        $this->enqueueScript('app', 'ibge', 'js/ibge.js');
    }

    function includeChatAssets() {

        $app = App::i();

        $app->view->enqueueScript('app', 'ng.mc.module.notifications', 'js/ng.mc.module.notifications.js');
        $app->view->enqueueScript('app', 'ng.mc.directive.editBox', 'js/ng.mc.directive.editBox.js');
        $app->view->enqueueScript('app', 'ng.module.chat', 'js/ng.module.chat.js', ['mapasculturais']);

    }

    function includeEditableEntityAssets() {

        $versions = $this->_libVersions;
        $this->assetManager->publishAsset('img/setinhas-editable.png');

        $this->assetManager->publishAsset("vendor/x-editable-{$versions['x-editable']}/img/clear.png", 'img/clear.png');
        $this->assetManager->publishAsset("vendor/x-editable-{$versions['x-editable']}/img/loading.gif", 'img/loading.gif');

        $this->assetManager->publishAsset("vendor/select2-{$versions['select2']}/select2.png", 'css/select2.png');
        $this->assetManager->publishAsset("vendor/select2-{$versions['select2']}/select2-spinner.gif", 'css/select2-spinner.gif');

        $this->assetManager->publishAsset("vendor/bootstrap-colorpicker/img/bootstrap-colorpicker/alpha-horizontal.png", 'img/bootstrap-colorpicker/alpha-horizontal.png');
        $this->assetManager->publishAsset("vendor/bootstrap-colorpicker/img/bootstrap-colorpicker/alpha.png", 'img/bootstrap-colorpicker/alpha.png');
        $this->assetManager->publishAsset("vendor/bootstrap-colorpicker/img/bootstrap-colorpicker/hue-horizontal.png", 'img/bootstrap-colorpicker/hue-horizontal.png');
        $this->assetManager->publishAsset("vendor/bootstrap-colorpicker/img/bootstrap-colorpicker/hue.png", 'img/bootstrap-colorpicker/hue.png');
        $this->assetManager->publishAsset("vendor/bootstrap-colorpicker/img/bootstrap-colorpicker/saturation.png", 'img/bootstrap-colorpicker/saturation.png');

        $this->enqueueScript('app', 'editable', 'js/editable.js', array('mapasculturais'));
        $this->localizeScript('editable', [
            'cancel'    => i::__('Cancelar Alteração (Esc)'),
            'confirm'    => i::__('Confirmar Alteração (Enter)'),
            'confirmC'    => i::__('Confirmar Alteração (Ctrl+Enter)'),
            'unsavedChanges'    => i::__('Há alterações não salvas nesta página.'),
            'freePublish'    => i::__('Publicação livre'),
            'restrictedPublish'    => i::__('Publicação restrita'),
            'freePlublishDescription'    => i::__('Qualquer pessoa pode criar eventos.'),
            'restrictedPublishDescription'    => i::__('Requer autorização para criar eventos.'),
            'this_agent' => i::__('este agente'),
            'this_space' => i::__('este espaço'),
            'this_event' => i::__('este evento'),
            'this_project' => i::__('este projeto'),
            'this_opportunity' => i::__('esta oportunidade'),
            'confirmPublish'    => i::__('Você tem certeza que deseja publicar %s?'),
            'confirmPublishFinal'    => i::__('Você tem certeza que deseja publicar %s? Isto não poderá ser desfeito.'),
            'requestChild'    => i::__('Sua requisição para fazer deste %s filho de %s foi enviada.'),
            'requestEventProject'    => i::__('Sua requisição para associar este evento ao projeto %s foi enviada.'),
            'correctErrors'    => i::__('Corrija os erros indicados abaixo.'),
            'changesSaved'    => i::__('Edições salvas.'),
            'unexpectedError'    => i::__('Um erro inesperado aconteceu.'),
            'insertVideoTitle'    => i::__('Insira um título para seu vídeo.'),
            'insertVideoUrl'    => i::__('Insira uma url de um vídeo do YouTube ou do Vimeo.'),
            'insertLinkTitle'    => i::__('Insira um título para seu link.'),
            'insertLinkUrl'    => i::__('A url do link é inválida, insira uma url completa como http://www.google.com/.'),
            'Limpar'    => i::__('Limpar'),
            'Crop'    => i::__('Recortar'),
            'CropHelp'    => i::__('Arraste para recortar'),
            'removeAgentBackground' => i::__('Tem certeza que deseja remover a imagem de capa?'),
        ]);

        $this->enqueueScript('app', 'evaluations', 'js/evaluations.js');
        $this->localizeScript('evaluations', [
            'saveMessage' => i::__('A avaliação foi salva')
        ]);
    }

    function includeGeocodingAssets()
    {
        $this->enqueueScript("vendor", "google-maps-api",
                             ("https://maps.googleapis.com/maps/api/js?key=" .
                              App::i()->config["app.googleApiKey"]));
        return;
    }

    function includeSearchAssets() {

        $this->jsObject['ngSearchAppDependencies'] = [
            'ng-mapasculturais',
            'rison',
            'infinite-scroll',
            'ui.date',
            'search.service.find',
            'search.service.findOne',
            'search.controller.map',
            'search.controller.spatial',
            'mc.module.notifications'
        ];

        $this->enqueueScript('app', 'search.service.find', 'js/ng.search.service.find.js', array('ng-mapasculturais', 'search.controller.spatial'));
        $this->enqueueScript('app', 'search.service.findOne', 'js/ng.search.service.findOne.js', array('ng-mapasculturais', 'search.controller.spatial'));
        $this->enqueueScript('app', 'search.controller.map', 'js/ng.search.controller.map.js', array('ng-mapasculturais', 'map'));
        $this->localizeScript('controllerMap', [
            /* Translators: serach results. Eventos encontrados no espaço {nome do espaço} */
            'eventsFound'    => i::__('Eventos encontrados no espaço'),
        ]);

        $this->enqueueScript('app', 'search.controller.spatial', 'js/ng.search.controller.spatial.js', array('ng-mapasculturais', 'map'));
        $this->localizeScript('controllerSpatial', [
            'tooltip.start' =>  i::__('Clique e arraste para desenhar o círculo'),
            'tooltip.end' =>    i::__('Solte o mouse para finalizar o desenho'),
            'title' =>          i::__('Cancelar desenho'),
            'text' =>           i::__('Cancelar'),
            'circle' =>         i::__('Desenhar um círculo'),
            'radius' =>         i::__('Raio'),
            'currentLocation' =>i::__('Segundo seu navegador, você está aproximadamente neste ponto com margem de erro de {{errorMargin}} metros. Buscando resultados dentro de um raio de {{radius}}KM deste ponto.'),
        ]);


        $this->enqueueScript('app', 'search.app', 'js/ng.search.app.js', array('ng-mapasculturais', 'search.controller.spatial', 'search.controller.map', 'search.service.findOne', 'search.service.find'));
        $this->localizeScript('searchApp', [
            'all' => i::__('Todos'),
            /* Translators: de uma data. Ex: *de* 12/12 a 13/12 */
            'dateFrom' => i::__('de'),
            /* Translators: a uma data. Ex: de 12/12 *a* 13/12 */
            'dateTo' => i::__('a'),
            'CreateDate' => i::__('Data de criação'),
            'name' => i::__('Nome'),
        ]);
    }

    function includeMapAssets() {
        $app = App::i();

        $this->assetManager->publishAsset('css/main.css.map', 'css/main.css.map');
        $this->jsObject['assets']['avatarAgent'] = $this->asset('img/avatar--agent.png', false, false);
        $this->jsObject['assets']['avatarSpace'] = $this->asset('img/avatar--space.png', false, false);
        $this->jsObject['assets']['avatarEvent'] = $this->asset('img/avatar--event.png', false, false);
        $this->jsObject['assets']['avatarProject'] = $this->asset('img/avatar--project.png', false, false);
        $this->jsObject['assets']['avatarOpportunity'] = $this->asset('img/avatar--opportunity.png', false, false);
        $this->jsObject['assets']['avatarSeal'] = $this->asset('img/avatar--seal.png', false, false);

        $this->jsObject['assets']['iconLocation'] = $this->asset('img/icon-localizacao.png', false, false);
        $this->jsObject['assets']['iconFullscreen'] = $this->asset('img/icon-fullscreen.png', false, false);
        $this->jsObject['assets']['iconZoomIn'] = $this->asset('img/icon-zoom-in.png', false, false);
        $this->jsObject['assets']['iconZoomOut'] = $this->asset('img/icon-zoom-out.png', false, false);
        $this->jsObject['assets']['layers'] = $this->asset('img/layers.png', false, false);
        $this->jsObject['assets']['iconCircle'] = $this->asset('img/icon-circulo.png', false, false);

        $this->jsObject['assets']['pinShadow'] = $this->asset('img/pin-sombra.png', false, false);
        $this->jsObject['assets']['pinMarker'] = $this->asset('img/marker-icon.png', false, false);

        $this->jsObject['assets']['pinAgent'] = $this->asset('img/pin-agent.png', false, false);
        $this->jsObject['assets']['pinSpace'] = $this->asset('img/pin-space.png', false, false);
        $this->jsObject['assets']['pinEvent'] = $this->asset('img/pin-event.png', false, false);

        $this->jsObject['assets']['pinAgentGroup'] = $this->asset('img/agrupador-agent.png', false, false);
        $this->jsObject['assets']['pinEventGroup'] = $this->asset('img/agrupador-event.png', false, false);
        $this->jsObject['assets']['pinSpaceGroup'] = $this->asset('img/agrupador-space.png', false, false);
        //$this->jsObject['assets']['pinSealGroup'] = $this->asset('img/agrupador-seal.png', false, false);

        $this->jsObject['assets']['pinAgentEventGroup'] = $this->asset('img/agrupador-combinado-agent-event.png', false, false);
        $this->jsObject['assets']['pinSpaceEventGroup'] = $this->asset('img/agrupador-combinado-space-event.png', false, false);
        $this->jsObject['assets']['pinAgentSpaceGroup'] = $this->asset('img/agrupador-combinado-space-agent.png', false, false);
        //$this->jsObject['assets']['pinSealSpaceGroup'] = $this->asset('img/agrupador-combinado-space-seal.png', false, false);

        $this->jsObject['assets']['pinAgentSpaceEventGroup'] = $this->asset('img/agrupador-combinado.png', false, false);

        $this->jsObject['geoDivisionsHierarchy'] = $app->config['app.geoDivisionsHierarchy'];

        $this->jsObject['defaultCountry'] = $app->config['app.defaultCountry'];
        
        $this->enqueueScript('app', 'map', 'js/map.js');
    }

    function includeAngularEntityAssets($entity) {
        $app = App::i();
        $app->applyHookBoundTo($this, 'view.includeAngularEntityAssets:after');

        $this->jsObject['templateUrl']['editBox'] = $this->asset('js/directives/edit-box.html', false);
        $this->jsObject['templateUrl']['findEntity'] = $this->asset('js/directives/find-entity.html', false);
        $this->jsObject['templateUrl']['MCSelect'] = $this->asset('js/directives/mc-select.html', false);
        $this->jsObject['templateUrl']['multiselect'] = $this->asset('js/directives/multiselect.html', false);
        $this->jsObject['templateUrl']['singleselect'] = $this->asset('js/directives/singleselect.html', false);
        $this->jsObject['templateUrl']['editableMultiselect'] = $this->asset('js/directives/editableMultiselect.html', false);
        $this->jsObject['templateUrl']['editableSingleselect'] = $this->asset('js/directives/editableSingleselect.html', false);

        $this->enqueueScript('app', 'entity.app', 'js/ng.entity.app.js', array(
            'mapasculturais',
            'ng-mapasculturais',
            'mc.directive.multiselect',
            'mc.directive.singleselect',
            'mc.directive.editBox',
            'mc.directive.mcSelect',
            'mc.module.findEntity',
            'entity.module.relatedAgents',
            'entity.module.subsiteAdmins',
            'entity.module.relatedSeals',
            'entity.module.changeOwner',
            'entity.module.subsite',
            'entity.directive.editableMultiselect',
            'entity.directive.editableSingleselect',
        ));
        $this->localizeScript('entityApp', [
            'requestSent' =>  i::__('Sua requisição para enviar um contato pelo Mapas Culturais foi enviada com sucesso.'),
        ]);

        $this->enqueueScript('app', 'mc.directive.multiselect', 'js/ng.mc.directive.multiselect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.directive.singleselect', 'js/ng.mc.directive.singleselect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.directive.editBox', 'js/ng.mc.directive.editBox.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.directive.mcSelect', 'js/ng.mc.directive.mcSelect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.module.findEntity', 'js/ng.mc.module.findEntity.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity.module.changeOwner', 'js/ng.entity.module.changeOwner.js', array('ng-mapasculturais'));
        $this->localizeScript('changeOwner', [
            'ownerChanged' =>  i::__('O proprietário da entidade foi modificado'),
            'cannotChangeOwner' =>  i::__('O proprietário da entidade não pode ser modificado'),
            'requestMessage' =>  i::__('Sua requisição para mudança de propriedade deste {{type}} para o agente {{recipient}} foi enviada.'),
        ]);

        $this->enqueueScript('app', 'entity.module.project', 'js/ng.entity.module.project.js', array('ng-mapasculturais'));
        $this->localizeScript('moduleProject', [
            'selectFieldType' =>  i::__('Selecione o tipo de campo'),
            'fieldCreated' =>  i::__('Campo criado.'),
            'fieldRemoved' =>  i::__('Campo removido.'),
            'changesSaved' =>  i::__('Alterações Salvas.'),
            'attachmentCreated' =>  i::__('Anexo criado.'),
            'attachmentRemoved' =>  i::__('Anexo removido.'),
            'confirmAttachmentRemoved' =>  i::__('Deseja remover este anexo?'),
            'confirmRemoveModel' =>  i::__('Deseja remover este modelo?'),
            'modelRemoved' =>  i::__('Modelo removido.'),
            'statusPublished' =>  i::__('publicado'),
            'statusDraft' =>  i::__('rascunho'),
            'publishing...' =>  i::__('Publicando...'),
            'eventsPublished' =>  i::__('Eventos publicados.'),
            'savingAsDraft' =>  i::__('Tornando rascunho...'),
            'savedAsDraft' =>  i::__('Eventos transformados em rascunho.'),
            'confirmRemoveAttachment' =>  i::__('Deseja remover este anexo?'),
            'registrationOwnerDefault' =>  i::__('Agente responsável pela inscrição'),
            'allStatus' =>  i::__('Todas'),
            'pending' =>  i::__('Pendente'),
            'invalid' =>  i::__('Inválida'),
            'notSelected' =>  i::__('Não selecionada'),
            'suplente' =>  i::__('Suplente'),
            'selected' =>  i::__('Selecionada'),
            'Draft' =>  i::__('Rascunho'),
            'requiredLabel' =>  i::__('Obrigatório'),
            'optionalLabel' =>  i::__('Opcional'),
            'confirmReopen' =>  i::__('Você tem certeza que deseja reabrir este formulário para edição? Ao fazer isso, ele sairá dessa lista.'),
            'defineVacancies' =>  i::__('Você não definiu um número de vagas. Para selecionar essa inscrição, configure um número de vagas na aba Inscrições, em Agentes.'),
            'reachedMax' =>  i::__('Você atingiu o limite máximo de 1 inscrição aprovada'),
            'reachedMaxPlural' =>  i::__('Você atingiu o limite máximo de {{num}} inscrições aprovadas'),
            'limitReached' =>  i::__('O limite de inscrições para o agente informado se esgotou.'),
            'VacanciesOver' =>  i::__('O número de vagas da inscrição no projeto se esgotou.'),
            'needResponsible' =>  i::__('Para se inscrever neste projeto você deve selecionar um agente responsável.'),
            'correctErrors' =>  i::__('Corrija os erros indicados abaixo.'),
            'registrationSent' =>  i::__('Inscrição enviada. Aguarde tela de sumário.'),
            'Todas opções' => i::__('Todas opções'),
        ]);

        $this->jsObject['registrationAutosaveTimeout'] = $app->config['registration.autosaveTimeout'];
        
        $this->enqueueScript('app', 'entity.module.opportunity', 'js/ng.entity.module.opportunity.js', array('ng-mapasculturais'));
        $this->localizeScript('moduleOpportunity', [
            'unexpectedError'    => i::__('Um erro inesperado aconteceu.'),
            'allCategories' => i::__('Todas as categorias'),
            'selectFieldType' =>  i::__('Selecione o tipo de campo'),
            'fieldCreated' =>  i::__('Campo criado.'),
            'fieldRemoved' =>  i::__('Campo removido.'),
            'changesSaved' =>  i::__('Alterações Salvas.'),
            'attachmentCreated' =>  i::__('Anexo criado.'),
            'attachmentRemoved' =>  i::__('Anexo removido.'),
            'confirmAttachmentRemoved' =>  i::__('Deseja remover este anexo?'),
            'confirmRemoveModel' =>  i::__('Deseja remover este modelo?'),
            'modelRemoved' =>  i::__('Modelo removido.'),
            'statusPublished' =>  i::__('publicado'),
            'statusDraft' =>  i::__('rascunho'),
            'publishing...' =>  i::__('Publicando...'),
            'eventsPublished' =>  i::__('Eventos publicados.'),
            'savingAsDraft' =>  i::__('Tornando rascunho...'),
            'savedAsDraft' =>  i::__('Eventos transformados em rascunho.'),
            'confirmRemoveAttachment' =>  i::__('Deseja remover este anexo?'),
            'registrationOwnerDefault' =>  i::__('Agente responsável pela inscrição'),
            'agentRelationIsAlreadyExists' =>  i::__('O Agente selecionado já foi adicionado na comissão de avaliadores'),
            'allStatus' =>  i::__('Todas'),
            'pending' =>  i::__('Pendente'),
            'invalid' =>  i::__('Inválida'),
            'notSelected' =>  i::__('Não selecionada'),
            'suplente' =>  i::__('Suplente'),
            'selected' =>  i::__('Selecionada'),
            'draft' =>  i::__('Rascunho'),
            'requiredLabel' =>  i::__('Obrigatório'),
            'optionalLabel' =>  i::__('Opcional'),
            'confirmReopen' =>  i::__('Você tem certeza que deseja reabrir este formulário para edição? Ao fazer isso, ele sairá dessa lista.'),
            'defineVacancies' =>  i::__('Você não definiu um número de vagas. Para selecionar essa inscrição, configure um número de vagas na aba Inscrições, em Agentes.'),
            'reachedMax' =>  i::__('Você atingiu o limite máximo de 1 inscrição aprovada'),
            'reachedMaxPlural' =>  i::__('Você atingiu o limite máximo de {{num}} inscrições aprovadas'),
            'limitReached' =>  i::__('O limite de inscrições para o agente informado se esgotou.'),
            'VacanciesOver' =>  i::__('O número de vagas da inscrição no oportunidade se esgotou.'),
            'needResponsible' =>  i::__('Para se inscrever nesta oportunidade você deve selecionar um agente responsável.'),
            'correctErrors' =>  i::__('Corrija os erros indicados abaixo.'),
            'registrationSent' =>  i::__('Inscrição enviada. Aguarde tela de sumário.'),
            'confirmRemoveValuer' => i::__('Você tem certeza que deseja excluir o avaliador?'),
            'confirmReopenValuerEvaluations' => i::__('Você tem certeza que deseja reabrir as avaliações para este avaliador?'),
            'evaluated' => i::__('Avaliada'),
            'notEvaluated' => i::__('Não Avaliada'),
            'all' => i::__('Todas'),
            'sent' => i::__('Enviada'),
            'confirmEvaluationLabel' => i::__('Aplicar resultado das avaliações'),
            'applyEvaluations' => i::__('Deseja aplicar o resultado de todas as avaliações como o status das respectivas inscrições?'),

            'Inscrição' => i::__('Inscrição'),
            'Categorias' => i::__('Categorias'),
            'Agentes' => i::__('Agentes'),
            'Anexos' => i::__('Anexos'),
            'Avaliação' => i::__('Avaliação'),
            'Status' => i::__('Status'),
            'spaceRelationRequestSent' =>  i::__('Sua requisição para relacionar o espaço {{space}} foi enviada.')
        ]);

        $this->enqueueScript('app', 'entity.module.subsiteAdmins', 'js/ng.entity.module.subsiteAdmins.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity.module.subsite', 'js/ng.entity.module.subsite.js', array('ng-mapasculturais'));

        $this->enqueueScript('app', 'entity.module.relatedAgents', 'js/ng.entity.module.relatedAgents.js', array('ng-mapasculturais'));
        $this->localizeScript('relatedAgents', [
            'requestSent' =>  i::__('Sua requisição para relacionar o agente {{agent}} foi enviada.'),
        ]);

        $this->enqueueScript('app', 'entity.module.relatedSeals', 'js/ng.entity.module.relatedSeals.js', array('ng-mapasculturais'));
        $this->localizeScript('relatedAgents', [
            'requestSent' =>  i::__('Sua requisição para relacionar o agente {{agent}} foi enviada.'),
            'confirmDeleteGroup' =>  i::__('Tem certeza que deseja remover o grupo %s? A relação com todos os agentes dentro deste grupo será removida.'),
        ]);

        $this->enqueueScript('app', 'entity.directive.editableMultiselect', 'js/ng.entity.directive.editableMultiselect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity.directive.editableSingleselect', 'js/ng.entity.directive.editableSingleselect.js', array('ng-mapasculturais'));

        $roles = [];
        if(!\MapasCulturais\App::i()->user->is('guest')){
            foreach(\MapasCulturais\App::i()->user->roles as $r){
                $roles[] = $r->name;
            }
        }

        $this->jsObject['roles'] = $roles;
        $this->jsObject['request']['id'] = $entity->id;

        $app->applyHookBoundTo($this, 'view.includeAngularEntityAssets:after');
    }

    function includeSealAssets(){
        $this->enqueueScript("app","seal-locked-conf","js/seal-locked-conf.js");
        $this->enqueueStyle("app","seal-locked-conf","css/seal-locked-conf.css");
    }

    protected function _printJsObject($var_name = 'MapasCulturais', $print_script_tag = true) {
        if ($print_script_tag)
            echo "\n<script type=\"text/javascript\">\n";

        echo " var {$var_name} = " . json_encode($this->jsObject) . ';';

        if ($print_script_tag)
            echo "\n</script>\n";
    }

    function ajaxUploader($file_owner, $group_name, $response_action, $response_target, $response_template = '', $response_transform = '', $add_description_input = false, $humanCrop = false, $file_types = '.jpg ou .png') {
        $this->part('ajax-uploader', array(
            'file_owner' => $file_owner,
            'file_group' => $group_name,
            'response_action' => $response_action,
            'response_target' => $response_target,
            'response_template' => $response_template,
            'response_transform' => $response_transform,
            'add_description' => $add_description_input,
            'file_types' => $file_types,
            'human_crop' => $humanCrop
        ));
    }

    function getOccurrenceFrequencies() {
        return array(
            'once' => i::__('uma vez'),
            'daily' => i::__('todos os dias'),
            'weekly' => i::__('semanal'),
            'monthly' => i::__('mensal'),
        );
    }

    protected function _populateJsObject() {
        $app = App::i();
        $this->jsObject['userId'] = $app->user->is('guest') ? null : $app->user->id;
        $this->jsObject['userProfile'] = $app->user->profile; //get standard agent for user
        $this->jsObject['vectorLayersURL'] = $app->baseUrl . 'geojson';

        $this->jsObject['request'] = array(
            'controller' => $this->controller->id,
            'action' => $this->controller->action
        );

        if (!$app->user->is('guest')) {
            $this->jsObject['notifications'] = $app->controller('notification')->apiQuery(array(
                '@select' => 'id,status,isRequest,createTimestamp,message,approveUrl,request.{permissionTo.{approve,reject},requesterUser}',
                'user' => 'EQ(@me)',
                '@ORDER' => 'createTimestamp DESC'
            ));
        }

        if (($this->controller->id === 'site' && $this->controller->action === 'search') ||
            ($this->controller->id === 'panel' && $this->controller->action === 'userManagement')) {
            $skeleton_field = [
                'fieldType' => 'checklist',
                'isInline' => true,
                'isArray' => true,
                'prefix' => '',
                'type' => 'metadata',
                'label' => '',
                'placeholder' => '',
                'filter' => [
                    'param' => '',
                    'value' => 'IN({val})'
                ]
            ];

            $filters = $this->_getFilters();
            $modified_filters = [];

            $sanitize_filter_value = function($val){
                return str_replace(',', '\\,', $val);
            };
            foreach ($filters as $key => $value) {
                $modified_filters[] = $key;
                $modified_filters[$key] = [];
                foreach ($filters[$key] as $field) {
                    $mod_field = array_merge($skeleton_field, $field);

                    if (in_array($mod_field['fieldType'], ['checklist', 'singleselect'])){
                        if(!isset($mod_field['options']))
                          $mod_field['options'] = [];
                        if ($mod_field['fieldType'] == 'singleselect')
                            $mod_field['options'][] = ['value' => null, 'label' => $mod_field['placeholder']];
                        switch ($mod_field['type']) {
                            case 'metadata':
                                $data = App::i()->getRegisteredMetadataByMetakey($field['filter']['param'], "MapasCulturais\Entities\\".ucfirst($key));
                                foreach ($data->config['options'] as $meta_key => $value)
                                    $mod_field['options'][] = ['value' => $sanitize_filter_value($meta_key), 'label' => $value];
                                break;

                            case 'entitytype':

                                $types = App::i()->getRegisteredEntityTypes("MapasCulturais\Entities\\".ucfirst($key));

                                // ordena alfabeticamente
                                uasort($types, function($a, $b) {
                                    if ($a->name == $b->name)
                                        return 0;
                                    if ($a->name < $b->name)
                                        return -1;
                                    if ($a->name > $b->name)
                                        return 1;
                                });
                                foreach ($types as $type_key => $type_val)
                                    $mod_field['options'][] = ['value' => $sanitize_filter_value($type_key), 'label' => $type_val->name];

                                $sort = [];
                                foreach($mod_field['options'] as $k=>$v)
                                    $sort['label'][$k] = $v['label'];
                                array_multisort($sort['label'], SORT_ASC, $mod_field['options']);

                                $this->addEntityTypesToJs("MapasCulturais\Entities\\".ucfirst($key));
                                break;
                            case 'term':
                                $tax = App::i()->getRegisteredTaxonomyBySlug($field['filter']['param']);
                                foreach ($tax->restrictedTerms as $v)
                                    $mod_field['options'][] = ['value' => $sanitize_filter_value($v), 'label' => $v];

                                $this->addTaxonoyTermsToJs($mod_field['filter']['param']);
                                break;
                        }
                    }
                    $modified_filters[$key][] = $mod_field;
                }
            }
            $this->jsObject['filters'] = $modified_filters;
        }

        if($app->user->is('admin')) {
        	$this->jsObject['allowedFields'] = true;
        } else {
        	$this->jsObject['allowedFields'] = false;
        }

        $this->jsObject['notification_type'] = $app->getRegisteredMetadata('MapasCulturais\Entities\Notification');
    }

    protected function _getFilters(){
        $filters = [
            'space' => [
                'area' => [
                    'label'=> $this->dict('taxonomies:area: name', false),
                    'placeholder' => $this->dict('taxonomies:area: select', false),
                    'type' => 'term',
                    'filter' => [
                        'param' => 'area',
                        'value' => 'IN({val})'
                    ]
                ],
                'tipos' => [
                    'label' => i::__('Tipos'),
                    'placeholder' => i::__('Selecione os tipos'),
                    'type' => 'entitytype',
                    'filter' => [
                        'param' => 'type',
                        'value' => 'IN({val})'
                    ]
                ],
                'acessibilidade' => [
                    'label' => i::__('Acessibilidade'),
                    'placeholder' => i::__('Exibir somente resultados com Acessibilidade'),
                    'fieldType' => 'checkbox',
                    'isArray' => false,
                    'filter' => [
                        'param' => 'acessibilidade',
                        'value' => 'EQ(Sim)'
                    ],
                ],
                'verificados' => [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => 'Exibir somente ' . $this->dict('search: verified results', false),
                    'fieldType' => 'checkbox-verified',
                    'addClass' => 'verified-filter',
                    'isArray' => false,
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ],
            'agent' => [
                'area' => [
                    'label'=> i::__('Área de Atuação'),
                    'placeholder' =>i::__( 'Selecione as áreas'),
                    'type' => 'term',
                    'filter' => [
                        'param' => 'area',
                        'value' => 'IN({val})'
                    ],
                ],
                'tipos' => [
                    'label' => i::__('Tipos'),
                    'placeholder' => i::__('Todos'),
                    'fieldType' => 'singleselect',
                    'type' => 'entitytype',
                    // 'isArray' => false,
                    'filter' => [
                        'param' => 'type',
                        'value' => 'EQ({val})'
                    ]
                ],
                'verificados' => [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => $this->dict('search: display only verified results', false),
                    'fieldType' => 'checkbox-verified',
                    'addClass' => 'verified-filter',
                    'isArray' => false,
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ],
            'event' => [
                // TODO: Apply filter FromTo from configuration, removing from template "filter-field.php"
                // [
                //     'label' => ['De', 'a'],
                //     'fieldType' => 'dateFromTo',
                //     'placeholder' => '00/00/0000',
                //     'isArray' => false,
                //     'prefix' => '@',
                //     'filter' => [
                //         'param' => ['from', 'to'],
                //         'value' => ['LTE({val})', 'GTE({val})']
                //     ]
                // ],
                'linguagem' => [
                    'label' => i::__('Linguagem'),
                    'placeholder' => i::__('Selecione as linguagens'),
                    'fieldType' => 'checklist',
                    'type' => 'term',
                    'filter' => [
                        'param' => 'linguagem',
                        'value' => 'IN({val})'
                    ]
                ],
                'classificacao' => [
                    'label' => i::__('Classificação'),
                    'placeholder' => i::__('Selecione a classificação'),
                    'filter' => [
                        'param' => 'classificacaoEtaria',
                        'value' => 'IN({val})'
                    ]
                ],
                'verificados' => [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => $this->dict('search: display only verified results', false),
                    'fieldType' => 'checkbox-verified',
                    'isArray' => false,
                    'addClass' => 'verified-filter',
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ],
            'project' => [
                'tipos' => [
                    'label' => i::__('Tipo'),
                    'placeholder' => i::__('Selecione os tipos'),
                    'type' => 'entitytype',
                    'filter' => [
                        'param' => 'type',
                        'value' => 'IN({val})'
                    ]
                ],
                'inscricoes' => [
                    'label' => i::__('Inscrições Abertas'),
                    'fieldType' => 'custom.project.ropen'
                ],
                'verificados' => [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => $this->dict('search: display only verified results', false),
                    'fieldType' => 'checkbox-verified',
                    'addClass' => 'verified-filter',
                    'isArray' => false,
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ],
            'opportunity' => [
                'tipos' => [
                    'label' => i::__('Tipo'),
                    'placeholder' => i::__('Selecione os tipos'),
                    'type' => 'entitytype',
                    'filter' => [
                        'param' => 'type',
                        'value' => 'IN({val})'
                    ]
                ],
                'inscricoes' => [
                    'label' => i::__('Inscrições Abertas'),
                    'fieldType' => 'custom.opportunity.ropen'
                ],
                'verificados' => [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => $this->dict('search: display only verified results', false),
                    'fieldType' => 'checkbox-verified',
                    'addClass' => 'verified-filter',
                    'isArray' => false,
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ]
        ];

        App::i()->applyHookBoundTo($this, 'search.filters', [&$filters]);

        return $filters;
    }

    function addEntityToJs(MapasCulturais\Entity $entity){
        $this->jsObject['entity'] = [
            'id' => $entity->id,
            'ownerId' => $entity->owner->id, // ? $entity->owner->id : null,
            'ownerUserId' => $entity->ownerUser->id,
            'definition' => $entity->getPropertiesMetadata(),
            'userHasControl' => $entity->canUser('@control'),
            'canUserChangeOwner' => $entity->canUser('changeOwner'),
            'canUserCreateRelatedAgentsWithControl' => $entity->canUser('createAgentRelationWithControl'),
            'status' => $entity->status,
            'object' => json_decode(json_encode($entity))
        ];

        if($entity->usesNested() && $entity->id){
            $this->jsObject['entity']['childrenIds'] = $entity->getChildrenIds();
        }
    }

    function addOccurrenceFrequenciesToJs() {
        $this->jsObject['frequencies'] = $this->getOccurrenceFrequencies();
    }

    function addEntityTypesToJs($entity) {

        $controller = App::i()->getControllerByEntity($entity);
        $types = $controller->types;

        usort($types, function($a, $b) {
            if ($a->name > $b->name)
                return 1;
            elseif ($a->name < $b->name)
                return -1;
            else
                return 0;
        });

        if (!isset($this->jsObject['entityTypes']))
            $this->jsObject['entityTypes'] = array();

        $this->jsObject['entityTypes'][$controller->id] = $types;
    }

    function addTaxonoyTermsToJs($taxonomy_slug) {
        $terms = App::i()->repo('Term')->getTermsAsString($taxonomy_slug);
        if (!isset($this->jsObject['taxonomyTerms']))
            $this->jsObject['taxonomyTerms'] = array();

        $this->jsObject['taxonomyTerms'][$taxonomy_slug] = $terms;
    }

    function addRelatedAgentsToJs($entity) {
        $this->jsObject['entity']['agentRelations'] = $entity->getAgentRelationsGrouped(null, $this->isEditable());
    }

    function addRelatedAdminAgentsToJs($entity) {
        $this->jsObject['entity']['agentAdminRelations'] = $entity->getAgentRelations(true, true);
    }

    function addSubsiteAdminsToJs($subsite) {
    	$app = App::i();
    	if (!$app->user->is('guest')) {
            $admins_roles = $app->repo('Role')->findBy(['name' => 'admin', 'subsiteId' => $subsite->id]);
            $super_admins_roles = $app->repo('Role')->findBy(['name' => 'superAdmin', 'subsiteId' => $subsite->id]);

            $admins = [];
            $super_admins = [];

            foreach($admins_roles as $role) {
                $admins[] = $role->user;

            }
            foreach($super_admins_roles as $role) {
                $super_admins[] = $role->user;

            }

            $this->jsObject['entity']['admins'] = $admins;
            $this->jsObject['entity']['superAdmins'] = $super_admins;
        }
    }

    function addRelatedSealsToJs($entity) {
    	$this->jsObject['entity']['sealRelations'] = $entity->getRelatedSeals(true, $this->isEditable());
    }

    function addSealsToJs($onlyPermited = true,$sealId = array()) {
    	$query = [];
    	$query['@select'] = 'id,name,status,singleUrl,validateDate';

        if($onlyPermited) {
    		$query['@permissions'] = '@control';
    	}

    	$query['@files'] = '(avatar.avatarSmall,avatar.avatarMedium):url';
        $sealId = array_unique($sealId);

    	if(count($sealId) > 0) {
            $sealId = implode(',',$sealId);
            $query['id'] = 'IN(' .$sealId . ')';
    	}

    	$query['@ORDER'] = 'createTimestamp DESC';

    	$app = App::i();
    	if (!$app->user->is('guest')) {
    		$this->jsObject['allowedSeals'] = $app->controller('seal')->apiQuery($query);
        	if($app->user->is('admin') || count($this->jsObject['allowedSeals']) > 0) {
        		$this->jsObject['canRelateSeal'] = true;
        	} else {
        		$this->jsObject['canRelateSeal'] = false;
        	}
        }
    }

    function addProjectEventsToJs(Entities\Project $entity){
        $app = App::i();

        $ids = $entity->getChildrenIds();

        $ids[] = $entity->id;

        $in = implode(',', array_map(function ($e){ return '@Project:' . $e; }, $ids));

        $this->jsObject['entity']['events'] = $app->controller('Event')->apiQuery([
            '@select' => 'id,name,shortDescription,classificacaoEtaria,singleUrl,occurrences.{id,space.{id,name,endereco,singleUrl},rule},terms,status,owner.id,owner.name,owner.singleUrl',
            'project' => 'IN(' . $in . ')',
            'status' => 'GTE(0)', // include drafts
            '@permissions' => 'view',
            '@files' => '(avatar.avatarSmall):url'
        ]);
    }

    function addProjectToJs(Entities\Project $entity){
        $app = App::i();

        $this->jsObject['entity']['published'] = $entity->publishedRegistrations;
    }

    function addOpportunityRegistrationsToJs(Entities\Opportunity $entity){
        if($entity->canUser('@control')){
            $this->jsObject['entity']['registrations'] = $entity->allRegistrations ? $entity->allRegistrations : array();
        } else {
            $this->jsObject['entity']['registrations'] = [];

            foreach($entity->sentRegistrations as $reg){
                if($reg->canUser('viewUserEvaluation')){
                    $this->jsObject['entity']['registrations'][] = $reg;
                }
            }
        }
    }

    function addOpportunitySelectFieldsToJs(Entities\Opportunity $entity){
        $this->jsObject['opportunitySelectFields'] = isset($this->jsObject['opportunitySelectFields']) ? $this->jsObject['opportunitySelectFields'] : [];

        $_opportunity = $entity;
        while($_opportunity !== null){
            foreach($_opportunity->registrationFieldConfigurations as $field){
                if($field->fieldType == 'select'){
                    if(!in_array($field, $this->jsObject['opportunitySelectFields'])){
                        $this->jsObject['opportunitySelectFields'][] = $field;
                    }
                }
            }
            $_opportunity = $_opportunity->parent;
        }
    }

    function addOpportunityToJs(Entities\Opportunity $entity){
        $app = App::i();


        $registrationStatuses = [
            ['value' => 1, 'label' => i::__('Pendente')],
            ['value' => 2, 'label' => i::__('Inválida')],
            ['value' => 3, 'label' => i::__('Não selecionada')],
            ['value' => 8, 'label' => i::__('Suplente')],
            ['value' => 10,'label' => i::__('Selecionada')],
            ['value' => 0, 'label' => i::__('Rascunho')],
        ];

        $app->applyHookBoundTo($entity, 'opportunity.registrationStatuses', [&$registrationStatuses]);

        $this->jsObject['entity']['registrationFileConfigurations'] = (array) $entity->registrationFileConfigurations;
        $this->jsObject['entity']['registrationFieldConfigurations'] = (array) $entity->registrationFieldConfigurations;
        $this->jsObject['entity']['registrationStatuses'] = $registrationStatuses;

        usort($this->jsObject['entity']['registrationFileConfigurations'], function($a,$b){

            if($a->title > $b->title){
                return 1;
            }else if($a->title < $b->title){

            }else{
                return 0;
            }
        });

        $field_types = array_values($app->getRegisteredRegistrationFieldTypes());

        usort($field_types, function ($a,$b){
            return strcmp($a->name, $b->name);
        });

        $this->jsObject['registrationFieldTypes'] = $field_types;

        $this->jsObject['entity']['registrationCategories'] = $entity->registrationCategories;
        $this->jsObject['entity']['published'] = $entity->publishedRegistrations;

        $this->jsObject['entity']['registrationRulesFile'] = $entity->getFile('rules');
        $this->jsObject['entity']['canUserModifyRegistrationFields'] = $entity->canUser('modifyRegistrationFields');

        // add current user registrations evaluations

        if($entity->canUser('viewEvaluations')){
            $this->jsObject['evaluationConfiguration'] = $entity->evaluationMethodConfiguration;
        }
    }

    function getCurrentRegistrationEvaluation(Entities\Registration $entity){
        $evaluation = null;

        if(isset($entity->controller->urlData['uid'])){
            $evaluation = App::i()->repo('RegistrationEvaluation')->findOneBy([
                'registration' => $entity,
                'user' => $entity->controller->urlData['uid']
            ]);
            if($evaluation && !$evaluation->registration->equals($entity)){
                $evaluation = null;
            }
        } else {
            $evaluation = $entity->getUserEvaluation();
        }

        if($evaluation){
            $evaluation->checkPermission('view');
        }

        return $evaluation;
    }

    function addRegistrationToJs(Entities\Registration $entity){
        $this->jsObject['entity']['registrationFileConfigurations'] = (array) $entity->opportunity->registrationFileConfigurations;

        $this->jsObject['entity']['registrationId'] = $entity->id;
        $this->jsObject['entity']['registrationCategories'] = $entity->opportunity->registrationCategories;
        $this->jsObject['entity']['registrationFiles'] = $entity->files;
        $this->jsObject['entity']['registrationAgents'] = array();
        $this->jsObject['entity']['registrationSpace'] = $entity->spaceRelation;
        $this->jsObject['entity']['spaceData'] = $entity->getSpaceData();

        $this->jsObject['entity']['canUserEvaluate'] = $entity->canUser('evaluate');
        $this->jsObject['entity']['canUserModify'] = $entity->canUser('modify');
        $this->jsObject['entity']['canUserSend'] = $entity->canUser('send');
        $this->jsObject['entity']['canUserViewUserEvaluations'] = $entity->canUser('viewUserEvaluations');

        $this->jsObject['registration'] = (object) $entity->jsonSerialize();

        if($entity->opportunity->canUser('viewEvaluations')){
            $this->jsObject['evaluation'] = $this->getCurrentRegistrationEvaluation($entity);
            $this->jsObject['evaluationConfiguration'] = $entity->opportunity->evaluationMethodConfiguration;
        }
        foreach($entity->_getDefinitionsWithAgents() as $def){
            $agent = $def->agent;
            if($agent){
                $def->agent = $agent->simplify('id,name,shortDescription,singleUrl');
                $def->agent->avatarUrl = ($agent->avatar && $agent->avatar->transform('avatarSmall')) ? $agent->avatar->transform('avatarSmall')->url : null;
                if($entity->status > 0){ // is sent
                    if(isset($entity->agentsData[$def->agentRelationGroupName])){
                        foreach($entity->agentsData[$def->agentRelationGroupName] as $prop => $val){
                            $def->agent->$prop = $val;
                        }
                    }
                }
            }
            $this->jsObject['entity']['registrationAgents'][] = $def;
        }
    }

    /**
    * Returns a verified entity
    * @param type $entity_class
    * @return \MapasCulturais\Entity
    */
    function getOneVerifiedEntity($entity_class) {
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $entity_class;

        if($app->cache->contains($cache_id)){
            $entity_id = $app->cache->fetch($cache_id);
            if(!$entity_id)
                return $entity_id;
            return $app->repo($entity_class)->find($entity_id);
        }

        $controller = $app->getControllerByEntity($entity_class);

        if ($entity_class === 'MapasCulturais\Entities\Event') {
            $entities = $controller->apiQueryByLocation(array(
                '@from' => date('Y-m-d'),
                '@to' => date('Y-m-d', time() + 28 * 24 * 3600),
                '@verified' => 'IN(1)',
                '@select' => 'id'
            ));

        }else{

            $entities = $controller->apiQuery([
                '@select' => 'id',
                '@verified' => 'IN(1)'
            ]);
        }

        $ids = array_map(function($item) {
            return $item['id'];
        }, $entities);

        if ($ids) {
            $id = $ids[array_rand($ids)];
            $result = $app->repo($entity_class)->find($id);
            $result->refresh();
        } else {
            $result = null;
        }

        $app->cache->save($cache_id, $result ? $result->id : $result, 120);

        return $result;
    }

    function getEntityFeaturedImageUrl($entity) {
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $entity;

        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        if (key_exists('gallery', $entity->files)) {
            $result = $entity->files['gallery'][array_rand($entity->files['gallery'])]->transform('galleryFull')->url;
        } elseif (key_exists('avatar', $entity->files)) {
            $result = $entity->files['avatar']->transform('galleryFull')->url;
        } else {
            $result = null;
        }

        $app->cache->save($cache_id, $result, 1800);

        return $result;
    }

    function getNumEntities($class, $verified = 'all', $use_cache = true, $cache_lifetime = 300){
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $class . ':' . $verified;

        if($use_cache && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $controller = $app->getControllerByEntity($class);

        $q = ['@count'=>1];

        if($verified === true){
            $q['@verified'] = 'IN(1)';
        }

        $result = $controller->apiQuery($q);

        if($use_cache){
            $app->cache->save($cache_id, $result, $cache_lifetime);
        }

        return $result;
    }

    function getNumEvents($from = null, $to = null){
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $to . ':' . $from;

        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $result = $app->controller('Event')->apiQueryByLocation(array(
            '@count' => 1,
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', time() + 365 * 24 * 3600)
        ));

        $app->cache->save($cache_id, $result, 120);

        return $result;
    }

    function getNumVerifiedEvents($from = null, $to = null){
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $to . ':' . $from;

        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $result = $app->controller('Event')->apiQueryByLocation(array(
            '@count' => 1,
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', time() + 365 * 24 * 3600),
            '@verified' => 'IN(1)'
        ));

        $app->cache->save($cache_id, $result, 120);

        return $result;
    }

    function getRegistrationStatusName($registration){
        switch ($registration->status) {
            case \MapasCulturais\Entities\Registration::STATUS_APPROVED:
                return 'approved';
                break;
            case \MapasCulturais\Entities\Registration::STATUS_REJECTED:
                return 'rejected';
                break;
            case \MapasCulturais\Entities\Registration::STATUS_MAYBE:
                return 'maybe';
                break;
            case \MapasCulturais\Entities\Registration::STATUS_WAITING:
                return 'waiting';
                break;

        }
    }
    /*
     * This methods adds fields to the default query used in the search pages
     *
     * Use this to add fields that are available to build the list results and the infoboxes
     *
     * @param string|array $fields The fields to be added. It can be an array or a string with comma separated list
     *
     */
    function addSearchQueryFields($fields) {
        if (!$fields || empty($fields))
            return false;

        if (is_string($fields))
            $fields = explode(',', $fields);

        $this->searchQueryFields = array_merge($this->searchQueryFields, $fields);
        $this->jsObject['searchQueryFields'] = implode(',', $this->searchQueryFields);

    }

    private function isHome() {
        $app = \MapasCulturais\App::i();
        $view = $app->getView();

        return ( $view->template === "site/index" && $view->getController()->action === "index" );
    }

    public function getLoginLinkAttributes() {
        $app = \MapasCulturais\App::i();
        $loginURL = $app->createUrl('panel');
        $link_attributes = 'data-auth="'. $loginURL .'"';

        if ($this->isHome()) {
            $link_attributes = 'href="'. $loginURL .'"';
        }

        return $link_attributes;
    }

    /* MODAL */

    public function renderModalFor($entity_name, $show_icon = true, $label = "", $classes = "", $use_modal = true) {
        $app = App::i();

        $entity_classname = $app->controller($entity_name)->entityClassName;

        $current_entity_classname = $this->controller->entityClassName;

        $app->applyHook("modal({$entity_name}):before", [$entity_classname]);

        if ("edit" != $this->controller->action || ($entity_classname != $current_entity_classname) ) {
            if($show_icon){
                $classes .= ' add';
            }

            $modal_id = uniqid("add-{$entity_name}-");

            if ($use_modal) {
                $this->part('modal/modal', ['entity_name' => $entity_name, 'entity_classname' => $entity_classname, 'classes' => $classes, 'modal_id' => $modal_id, 'text' => $label]);
            } else {
                $this->part('modal/attached-modal', ['entity_name' => $entity_name, 'entity_classname' => $entity_classname, 'modal_id' => $modal_id]);
            }
        }

        $app->applyHook("modal({$entity_name}):before", [$entity_classname]);
    }

    public function renderModalRequiredMetadata($entity_classname, $entity_name){
        $app = App::i();
        $metadata = $app->getRegisteredMetadata($entity_classname);

        $app->applyHook("modal({$entity_name}).metadata:before", [$entity_classname, &$metadata]);

        foreach($metadata as $meta){

            $show_meta = $meta->is_required;
            $app->applyHook("modal({$entity_name}).metadata({$meta->key})", [$entity_classname, &$meta, &$show_meta]);

            if($show_meta){
                $this->part("modal/required-metadata", ['meta' => $meta, 'class' => "entity-required-field"]);
            }
        }

        $app->applyHook("modal({$entity_name}).metadata:after", [$entity_classname, &$metadata]);
    }

    public function renderModalTaxonomies($entity_classname, $entity_name) {
        $app = App::i();
        $taxonomies = $app->getRegisteredTaxonomies($entity_classname);

        $app->applyHook("modal({$entity_name}).taxonomies:before", [$entity_classname, &$taxonomies]);

        foreach($taxonomies as $taxonomy){
            $show_taxonomy = $taxonomy->required;

            $app->applyHook("modal({$entity_name}).taxonomy({$taxonomy->slug})", [$entity_classname, &$taxonomy, &$show_meta]);

            if($show_taxonomy){
                $_attr = "terms[{$taxonomy->slug}][]";
                $options = array_values($taxonomy->restrictedTerms);
                $title = $app->getView()->dict("taxonomies:{$taxonomy->slug}: name", false);

                $this->part("modal/title", ['title' => $title]);
                $this->part("modal/entity-dropdown", ['attr' => $_attr, 'options' => $options]);
            }
        }

        $app->applyHook("modal({$entity_name}).taxonomies:before", [$entity_classname, &$taxonomies]);
    }

    public function renderModalFields($entity_classname, $entity_name, $modal_id) {
        $app = App::i();

        $properties = $entity_classname::getPropertiesMetadata();
        $properties_validations = $entity_classname::getValidations();

        $not_use_fields = [
            'createTimestamp'

        ];

        $app->applyHook("modal({$entity_name}).fields:before", [$entity_classname, &$properties, &$not_use_fields]);

        foreach ($properties as $field => $definition) {
            if((in_array($field, $not_use_fields))){
                continue;
            }

            $show_field =
                !$definition['isMetadata'] && !$definition['isEntityRelation'] &&
                ($definition['required'] || isset($properties_validations[$field]['required']) );

            $app->applyHook("modal({$entity_name}).field({$field})", [$entity_classname, &$definition, &$show_field]);

            if ($show_field) {
                if($field === '_type' && $entity_classname::usesTypes()){
                    $this->part("modal/field--entity-type", ['entity_classname' => $entity_classname, 'definition' => $definition, 'modal_id' => $modal_id]);

                } else if ($definition['type'] === 'string') {

                    $this->part('modal/field--input-text', ['entity_classname' => $entity_classname, 'field' => $field, 'definition' => $definition, 'modal_id' => $modal_id]);
                    
                } else if ($definition['type'] === 'text'){
                    $this->part("modal/field--textarea", ['entity_classname' => $entity_classname, 'field' => $field, 'definition' => $definition, 'modal_id' => $modal_id]);

                }else if ($definition['type'] === 'datetime'){
                    $this->part("modal/field--input-datetime", ['entity_classname' => $entity_classname, 'field' => $field, 'definition' => $definition, 'modal_id' => $modal_id]);

                }
            }
        }
        $app->applyHook("modal({$entity_name}).fields:after", [$entity_classname, &$fields]);
    }

    public function getModalClasses($use_modal) {
        $base_class = "js-dialog";
        $cancel_class = "close-modal";

        if (!$use_modal) {
            $base_class = "";
            $cancel_class = "close-attached-modal";
        }

        $extra_wrapper_classes = '';
        $app = App::i();
        $app->applyHook('mapasculturais.add_entity_modal.wrapper_class', [&$extra_wrapper_classes]);
        $base_class .= " " . $extra_wrapper_classes;

        return ['classes' => $base_class, 'cancel_class' => $cancel_class];
    }

    private function modalFieldPlaceholder($title, $className) {
        return sprintf(i::__('Informe o %s do seu novo %s'), strtolower($title), $className);
    }

    private function getModalEntityName($entity_id, $entity) {
        $app = App::i();
        $_key = "entities: " . ucwords($entity_id);
        $_name = $app->view->dict($_key, false);

        if (empty($_name)) {
            $_name = $entity->getEntityTypeLabel();
        }

        return $_name;
    }

    public function getValuersCheckedAttribute($valuer_id, $valuers, $inverse = false) {
        $_first = 'checked="checked"';
        $_second = '';

        if ($inverse) {
            $aux = $_second;
            $_second = $_first;
            $_first = $aux;
        }

        return in_array($valuer_id, $valuers) ? $_first : $_second;
    }

}
