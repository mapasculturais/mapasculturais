<?php
namespace Blumenau;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    protected static function _getTexts(){
        return array(
            'site: in the region' => 'na Cidade de Blumenau',
            'site: of the region' => 'da Cidade de Blumenau',
            'site: owner' => 'Fundação Cultural de Blumenau',
            'site: by the site owner' => 'pela Fundação Cultural de Blumenau',

            'home: title' => "Bem-vind@ plataforma " . App::i()->siteName,
            'home: abbreviation' => "FCBlu",
//            'home: colabore' => "Colabore com o Mapas Culturais",
            'home: welcome' => "
                <p>Esta plataforma é o elo entre os agentes culturais, espaços, projetos, eventos e as pessoas interessadas em Cultura. A cidade de Blumenau, em consonância com as diretrizes do Ministério da Cultura (MinC), disponibiliza a partir de agora, informações sobre a Cultura da cidade a toda a comunidade.</p>
                <p>Blumenau Mais Cultura é uma plataforma livre, gratuita e colaborativa de mapeamento do cenário cultural blumenauense. Com ela a comunidade pode inteirar-se dos acontecimentos culturais sejam eles oficiais desenvolvidos pelo poder público ou desenvolvidos pela iniciativa privada. Para que toda a agenda cultural encontre-se no Blumenau Mais Cultura é necessário que os agentes culturais se cadastrem e mantenham atualizadas suas ações culturais.</p>
                <p>Por fim, o Blumenau Mais Cultura torna-se um repositório de informações culturais da cidade, ou seja, ele permite que sejam contabilizadas as ações culturais realizadas na cidade servindo para monitoramento da evolução da Cultura no município.</p>
            ",
//            'home: events' => "Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
//            'home: agents' => "Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural paulistana. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
//            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.",
//            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
//            'home: home_devs' => 'Existem algumas maneiras de desenvolvedores interagirem com o Mapas Culturais. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Mapas Culturais é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.',
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
        if(in_array($this->controller->action, ['single', 'edit'])){
            return;
        }
        $app = App::i();
        foreach ($this->documentMeta as $key => $meta){
            if(isset($meta['property']) && ($meta['property'] === 'og:image' || $meta['property'] === 'og:image:url')){
                $this->documentMeta[$key] = array('property' => $meta['property'] , 'content' => $app->view->asset('img/share-bc.png', false));
            }
        }
    }
}
