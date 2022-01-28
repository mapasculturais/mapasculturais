<?php

namespace MapasCulturais\Themes\BaseV2;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Exceptions;

class Theme extends \MapasCulturais\Theme
{
    static function getThemeFolder()
    {
        return __DIR__;
    }

    function _init()
    {
    }

    function register()
    {
    }

    /**
     * Retorna o título da página atual
     */
    function getTitle($entity = null): string
    {
        $title = parent::getTitle($entity);

        $site_name = $this->dict('site: name', false);

        if ($title != $site_name) {
            $title = "{$site_name} - {$title}";
        }

        return $title;
    }



    protected static function _getTexts()
    {
        $app = App::i();

        return array_map(function ($e) {
            return $e['text'];
        }, self::_dict());
    }

    static function _dict()
    {
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
}
