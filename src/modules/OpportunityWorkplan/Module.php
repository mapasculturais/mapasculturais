<?php
namespace OpportunityWorkplan;

use MapasCulturais\App,
    MapasCulturais\i;
use OpportunityWorkplan\Controllers\Workplan as ControllersWorkplan;
use OpportunityWorkplan\Entities\Workplan;
use OpportunityWorkplan\Entities\Goal;
use MapasCulturais\Definitions\Metadata;
use OpportunityWorkplan\Entities\Delivery;

class Module extends \MapasCulturais\Module{
    function _init(){
        $app = App::i();

        $app->hook('app.init:after', function () use($app) {
            $app->hook("component(opportunity-phase-config-data-collection):bottom", function(){
                $this->part('opportunity-workplan-config');
            });

            $app->hook("component(registration-form):after", function(){
                $this->part('registration-workplan');
            });

            $app->hook("template(registration.view.registration-form-view):after", function(){
                $this->part('registration-details-workplan');
            });

            $app->hook("entity(Registration).sendValidationErrors", function (&$errorsResult) use($app) {
                $registration = $this;

                if ($registration->opportunity->enableWorkplan) {
                    $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);

                    $errors = [];

                    if (!$workplan) {
                        $errors['workplan'] = [i::__('Plano de trabalho obrigatório.')];
                    }

                    if (!$workplan?->projectDuration) {
                        $errors['projectDuration'] = [i::__('Plano de trabalho - Duração do projeto (meses) obrigatório.')];
                    }

                    if (!$workplan?->culturalArtisticSegment) {
                        $errors['culturalArtisticSegment'] = [i::__('Plano de trabalho - Segmento artistico-cultural obrigatório.')];
                    }
                   
                    if ($workplan?->goals->isEmpty()) {
                        $errors['goal'] = [i::__('Meta do plano de trabalho obrigatório.')];
                    }

                    if ($registration->opportunity->workplan_deliveryReportTheDeliveriesLinkedToTheGoals) {
                        if (is_iterable($workplan?->goals)) {
                            foreach ($workplan?->goals as $goal) {
                                if ($goal?->deliveries->isEmpty()) {
                                    $errors['delivery'] = [i::__('Entrega da meta do plano de trabalho obrigatório.')];
                                }
                            }
                        }
                    }                   

                    $errorsResult = [...$errors];
                }               
            });

            $app->hook("template(registration.registrationPrint.section):end", function(){
                $this->part('registration-details-workplan-print');
            });
        });
    }

    function register()
    {
        $app = App::i();

        $app->registerController('workplan', ControllersWorkplan::class);
       
        $this->registerOpportunityMetadata('workplanLabelDefault', [
            'label' => i::__('Plano de trabalho label'),
            'default_value' => 'Plano de trabalho'
        ]);

        $this->registerOpportunityMetadata('goalLabelDefault', [
            'label' => i::__('Meta label'),
            'default_value' => 'Metas'
        ]);

        $this->registerOpportunityMetadata('deliveryLabelDefault', [
            'label' => i::__('Entrega label'),
            'default_value' => 'Entregas '
        ]);

        // metadados opportunity
        $this->registerOpportunityMetadata('enableWorkplan', [
            'label' => i::__('Habilitar plano de trabalho'),
            'type' => 'boolean',
            'default_value' => false
        ]);

         
        $this->registerOpportunityMetadata('workplan_dataProjectlimitMaximumDurationOfProjects', [
            'label' => i::__('Limitar duração máxima dos projetos'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        
        $this->registerOpportunityMetadata('workplan_dataProjectmaximumDurationInMonths', [
            'label' => i::__('Duração máxima em meses'),
            'type' => 'integer',
            'default' => 1
        ]);

        
        $this->registerOpportunityMetadata('workplan_metaInformTheStageOfCulturalMaking', [
            'label' => i::__('Informar a etapa do fazer cultural'),
            'type' => 'boolean',
            'default_value' => false
        ]);
        
        $this->registerOpportunityMetadata('workplan_metaInformTheValueGoals', [
            'label' => i::__('Informar o valor da meta'),
            'type' => 'boolean',
            'default_value' => false
        ]);
        
        
        $this->registerOpportunityMetadata('workplan_metaLimitNumberOfGoals', [
            'label' => i::__('Limitar número de metas'),
            'type' => 'boolean',
            'default_value' => false
        ]);

         
        $this->registerOpportunityMetadata('workplan_metaMaximumNumberOfGoals', [
            'label' => i::__('Número máximo de metas'),
            'type' => 'integer',
            'default' => 1
        ]);

         
        $this->registerOpportunityMetadata('workplan_deliveryReportTheDeliveriesLinkedToTheGoals', [
            'label' => i::__('Informar as entregas vinculadas à meta'),
            'type' => 'boolean',
            'default_value' => false
        ]);

         
        $this->registerOpportunityMetadata('workplan_deliveryLimitNumberOfDeliveries', [
            'label' => i::__('Limitar número de entregas'),
            'type' => 'boolean',
            'default_value' => false
        ]);

         
        $this->registerOpportunityMetadata('workplan_deliveryMaximumNumberOfDeliveries', [
            'label' => i::__('Número máximo de entregas'),
            'type' => 'integer',
            'default' => 1
        ]);

        $this->registerOpportunityMetadata('workplan_monitoringInformDeliveryType', [
            'label' => i::__('Informar tipo de entrega'),
            'type' => 'multiselect',
            'options' => [
                \MapasCulturais\i::__("Ação de comunicação"),
                \MapasCulturais\i::__("Ação de formação"),
                \MapasCulturais\i::__("Acervo"),
                \MapasCulturais\i::__("Adereço"),
                \MapasCulturais\i::__("Agente cultural"),
                \MapasCulturais\i::__("Album"),
                \MapasCulturais\i::__("Aplicativo"),
                \MapasCulturais\i::__("Apresentação"),
                \MapasCulturais\i::__("Arte Gráfica"),
                \MapasCulturais\i::__("Arte Visual"),
                \MapasCulturais\i::__("Artesanato"),
                \MapasCulturais\i::__("Artigo"),
                \MapasCulturais\i::__("Áudio"),
                \MapasCulturais\i::__("Audiodescrição"),
                \MapasCulturais\i::__("Audiolivro"),
                \MapasCulturais\i::__("Aula"),
                \MapasCulturais\i::__("Bem cultural"),
                \MapasCulturais\i::__("Biblioteca"),
                \MapasCulturais\i::__("Blog"),
                \MapasCulturais\i::__("Bolsa"),
                \MapasCulturais\i::__("Cartilha"),
                \MapasCulturais\i::__("Catálogo"),
                \MapasCulturais\i::__("Cenário"),
                \MapasCulturais\i::__("Circulação"),
                \MapasCulturais\i::__("Coleção"),
                \MapasCulturais\i::__("Concurso"),
                \MapasCulturais\i::__("Conferência"),
                \MapasCulturais\i::__("Congresso"),
                \MapasCulturais\i::__("Conteúdo cultural"),
                \MapasCulturais\i::__("Coreografia"),
                \MapasCulturais\i::__("Curadoria"),
                \MapasCulturais\i::__("Curso"),
                \MapasCulturais\i::__("Desenho"),
                \MapasCulturais\i::__("Desfile"),
                \MapasCulturais\i::__("Design"),
                \MapasCulturais\i::__("Direito autoral"),
                \MapasCulturais\i::__("Disco"),
                \MapasCulturais\i::__("Distribuição"),
                \MapasCulturais\i::__("E-Book"),
                \MapasCulturais\i::__("Encontro"),
                \MapasCulturais\i::__("Ensaio"),
                \MapasCulturais\i::__("Ensaio aberto"),
                \MapasCulturais\i::__("Escultura"),
                \MapasCulturais\i::__("Espaço/Equipamento cultural"),
                \MapasCulturais\i::__("Espetáculo"),
                \MapasCulturais\i::__("Evento"),
                \MapasCulturais\i::__("Exibição"),
                \MapasCulturais\i::__("Exposição"),
                \MapasCulturais\i::__("Expressão artístico-cultural"),
                \MapasCulturais\i::__("Fanzine"),
                \MapasCulturais\i::__("Feira"),
                \MapasCulturais\i::__("Festa Popular"),
                \MapasCulturais\i::__("Festival"),
                \MapasCulturais\i::__("Figurino"),
                \MapasCulturais\i::__("Filme"),
                \MapasCulturais\i::__("Fotografia"),
                \MapasCulturais\i::__("Game"),
                \MapasCulturais\i::__("Grafitti"),
                \MapasCulturais\i::__("Gravura"),
                \MapasCulturais\i::__("Grupo artístico-cultural"),
                \MapasCulturais\i::__("Ilustração"),
                \MapasCulturais\i::__("Imóvel cultural"),
                \MapasCulturais\i::__("Ingresso"),
                \MapasCulturais\i::__("Intercâmbio cultural"),
                \MapasCulturais\i::__("Inventário cultural"),
                \MapasCulturais\i::__("Jogo"),
                \MapasCulturais\i::__("Joia"),
                \MapasCulturais\i::__("Jornal"),
                \MapasCulturais\i::__("Livro"),
                \MapasCulturais\i::__("Medida de acessibilidade"),
                \MapasCulturais\i::__("Mentoria"),
                \MapasCulturais\i::__("Monografia"),
                \MapasCulturais\i::__("Mostra"),
                \MapasCulturais\i::__("Mural"),
                \MapasCulturais\i::__("Música"),
                \MapasCulturais\i::__("Obra artístico-cultural"),
                \MapasCulturais\i::__("Oficina"),
                \MapasCulturais\i::__("Palestra"),
                \MapasCulturais\i::__("Parada"),
                \MapasCulturais\i::__("Patrimônio cultural"),
                \MapasCulturais\i::__("Performance"),
                \MapasCulturais\i::__("Periódico"),
                \MapasCulturais\i::__("Pesquisa artístico-cultural"),
                \MapasCulturais\i::__("Pintura"),
                \MapasCulturais\i::__("Plataforma Digital"),
                \MapasCulturais\i::__("Podcast"),
                \MapasCulturais\i::__("Premiação"),
                \MapasCulturais\i::__("Produto artesanal"),
                \MapasCulturais\i::__("Produto artístico-cultural"),
                \MapasCulturais\i::__("Programa de TV"),
                \MapasCulturais\i::__("Programa de Rádio"),
                \MapasCulturais\i::__("Projeto"),
                \MapasCulturais\i::__("Quadrinho"),
                \MapasCulturais\i::__("Residência artístico-cultural"),
                \MapasCulturais\i::__("Revista"),
                \MapasCulturais\i::__("Roda De Capoeira"),
                \MapasCulturais\i::__("Roteiro"),
                \MapasCulturais\i::__("Sarau"),
                \MapasCulturais\i::__("Seleção"),
                \MapasCulturais\i::__("Seminário"),
                \MapasCulturais\i::__("Série"),
                \MapasCulturais\i::__("Show"),
                \MapasCulturais\i::__("Simpósio"),
                \MapasCulturais\i::__("Single"),
                \MapasCulturais\i::__("Site"),
                \MapasCulturais\i::__("Slam"),
                \MapasCulturais\i::__("Tese"),
                \MapasCulturais\i::__("Texto"),
                \MapasCulturais\i::__("Trilha Sonora"),
                \MapasCulturais\i::__("Vestuário"),
                \MapasCulturais\i::__("Vídeo"),
                \MapasCulturais\i::__("Visita Guiada"),
                \MapasCulturais\i::__("Websérie"),
                \MapasCulturais\i::__("Workshop")
            ],
        ]);
         
        $this->registerOpportunityMetadata('workplan_registrationReportTheNumberOfParticipants', [
            'label' => i::__('Informar a quantidade estimada de público'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        $this->registerOpportunityMetadata('workplan_registrationInformCulturalArtisticSegment', [
            'label' => i::__('Informar segmento artístico-cultural'),
            'type' => 'boolean',
            'default_value' => false
        ]);
         
        $this->registerOpportunityMetadata('workplan_registrationReportExpectedRenevue', [
            'label' => i::__('Informar receita prevista'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        $this->registerOpportunityMetadata('workplan_registrationInformActionPAAR', [
            'label' => i::__('Informar a ação orçamentária (PAAR)'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        $this->registerOpportunityMetadata('workplan_monitoringInformTheFormOfAvailability', [
            'label' => i::__('Informar forma de disponibilização'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        $this->registerOpportunityMetadata('workplan_monitoringEnterDeliverySubtype', [
            'label' => i::__('Informar subtipo de entrega'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        $this->registerOpportunityMetadata('workplan_monitoringInformAccessibilityMeasures', [
            'label' => i::__('Informar as medidas de acessibilidade'),
            'type' => 'boolean',
            'default_value' => false
        ]);
        
        $this->registerOpportunityMetadata('workplan_monitoringInformThePriorityTerritories', [
            'label' => i::__('Informar os territórios prioritários'),
            'type' => 'boolean',
            'default_value' => false
        ]);
        
        $this->registerOpportunityMetadata('workplan_monitoringProvideTheProfileOfParticipants', [
            'label' => i::__('Informar o perfil do público'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        $this->registerOpportunityMetadata('workplan_monitoringInformThePriorityAudience', [
            'label' => i::__('Informar o público prioritário'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        $this->registerOpportunityMetadata('workplan_monitoringReportExecutedRevenue', [
            'label' => i::__('Informar receita executada'),
            'type' => 'boolean',
            'default_value' => false
        ]);

        // metadados workplan
        $projectDuration = new Metadata('projectDuration', ['label' => \MapasCulturais\i::__('Duração do projeto (meses)')]);
        $app->registerMetadata($projectDuration, Workplan::class);

        $culturalArtisticSegment = new Metadata('culturalArtisticSegment', [
            'label' => \MapasCulturais\i::__('Segmento artistico-cultural'),
            'type' => 'select',
            'options' => array(
                i::__('Acervos'),
                i::__('Acessibilidade Cultural'),
                i::__('Agrofloresta'),
                i::__('Animação'),
                i::__('Antropologia'),
                i::__('Áreas Técnicas'),
                i::__('Arqueologia'),
                i::__('Arquitetura e Urbanismo'),
                i::__('Arquivos'),
                i::__('Arte de Plásticas'),
                i::__('Arte de Rua'),
                i::__('Arte Digital'),
                i::__('Arte Gráfica'),
                i::__('Arte Santeira'),
                i::__('Artes Circenses'),
                i::__('Artes Visuais'),
                i::__('Artesanato'),
                i::__('Artesanato com Reciclados'),
                i::__('Artesanato em Cerâmica'),
                i::__('Artesanato em Fibra Vegetal'),
                i::__('Artesanato em Fio'),
                i::__('Artesanato em Madeira'),
                i::__('Artesanato em Metal'),
                i::__('Artesanato em Pedra'),
                i::__('Artesanato em Tecido'),
                i::__('Audiolivro'),
                i::__('Audiovisual e Mídias Interativas'),
                i::__('Audiovisual Expandido'),
                i::__('Axé'),
                i::__('Baião'),
                i::__('Baião (dança)'),
                i::__('Ballet'),
                i::__('Banda'),
                i::__('Banda Sinfônica'),
                i::__('Bandas de Congo e Ticumbi'),
                i::__('Biblioteca'),
                i::__('Biblioteca tombada'),
                i::__('Biblioteconomia'),
                i::__('Bioconstrução'),
                i::__('Biografia e Autobiografia'),
                i::__('Bloco de Carnaval'),
                i::__('Boi Bumbá'),
                i::__('Bordado'),
                i::__('Breakdance'),
                i::__('Brega Funk'),
                i::__('Caboclinho'),
                i::__('Caçada da Rainha'),
                i::__('Calypso'),
                i::__('Canto'),
                i::__('Canto Coral'),
                i::__('Capoeira'),
                i::__('Carimbó'),
                i::__('Carimbó (Dança)'),
                i::__('Carnaval'),
                i::__('Cavalhadas'),
                i::__('Cavalo Marinho'),
                i::__('Centro de Memória e patrimônio'),
                i::__('Ciência Política'),
                i::__('Cinema'),
                i::__('Cinemateca'),
                i::__('Ciranda'),
                i::__('Circo'),
                i::__('Circo Contemporâneo'),
                i::__('Circo de Rua'),
                i::__('Circo Itinerante'),
                i::__('Circo Tradicional'),
                i::__('Coco'),
                i::__('Comédia'),
                i::__('Comunicação'),
                i::__('Congada'),
                i::__('Congado'),
                i::__('Contação de histórias'),
                i::__('Conteúdo Audiovisual por Demanda'),
                i::__('Conto'),
                i::__('Cordel'),
                i::__('Criação literária'),
                i::__('Crochê'),
                i::__('Crônica'),
                i::__('Cultivo e extração tradicional'),
                i::__('Cultura Alemã'),
                i::__('Cultura Alimentar'),
                i::__('Cultura Cigana'),
                i::__('Cultura da juventude de povos e comunidades tradicionais'),
                i::__('Cultura das comunidades de fundo e fecho de pasto'),
                i::__('Cultura das comunidades quilombolas'),
                i::__('Cultura das quebradeiras de coco babaçu'),
                i::__('Cultura DEF'),
                i::__('Cultura Digital'),
                i::__('Cultura do Povo Arara'),
                i::__('Cultura do Povo Araweté'),
                i::__('Cultura do Povo Ashaninka'),
                i::__('Cultura do Povo Bororo'),
                i::__('Cultura do Povo Cultura Fulni-ô'),
                i::__('Cultura do Povo Enawenê-Nawê'),
                i::__('Cultura do Povo Gavião'),
                i::__('Cultura do Povo Guarani'),
                i::__('Cultura do Povo Ikpeng'),
                i::__('Cultura do Povo Javari'),
                i::__('Cultura do Povo Kalapalo'),
                i::__('Cultura do Povo Kanamari'),
                i::__('Cultura do Povo Karajá'),
                i::__('Cultura do Povo Karipuna'),
                i::__('Cultura do Povo Kaxinawá (Huni Kuin)'),
                i::__('Cultura do Povo Kayabi'),
                i::__('Cultura do Povo Kayapó'),
                i::__('Cultura do Povo Korubo'),
                i::__('Cultura do Povo Krahô'),
                i::__('Cultura do Povo Maxakali'),
                i::__('Cultura do Povo Munduruku'),
                i::__('Cultura do Povo Ofaié'),
                i::__('Cultura do Povo Panará'),
                i::__('Cultura do Povo Pankararu'),
                i::__('Cultura do Povo Pareci'),
                i::__('Cultura do Povo Paresí'),
                i::__('Cultura do Povo Pataxó'),
                i::__('Cultura do povo pomerano'),
                i::__('Cultura do Povo Suruí'),
                i::__('Cultura do Povo Tembé'),
                i::__('Cultura do Povo Terena'),
                i::__('Cultura do Povo Tikuna'),
                i::__('Cultura do Povo Timbira'),
                i::__('Cultura do Povo Tukano'),
                i::__('Cultura do Povo Tupi'),
                i::__('Cultura do Povo Wai Wai'),
                i::__('Cultura do Povo Waimiri-Atroari'),
                i::__('Cultura do Povo Xavante'),
                i::__('Cultura do Povo Xerente'),
                i::__('Cultura do Povo Xikrin'),
                i::__('Cultura do Povo Yanomami'),
                i::__('Cultura do Povo Yawalapiti'),
                i::__('Cultura do Povo Yawanawá'),
                i::__('Cultura do Povo Zoró'),
                i::__('Cultura dos andirobeiros'),
                i::__('Cultura dos apanhadores de flores sempre vivas'),
                i::__('Cultura dos benzedeiros'),
                i::__('Cultura dos caatingueiros'),
                i::__('Cultura dos caboclos'),
                i::__('Cultura dos caiçaras'),
                i::__('Cultura dos catadores de mangaba'),
                i::__('Cultura dos cipozeiros'),
                i::__('Cultura dos extrativistas'),
                i::__('Cultura dos extrativistas costeiros e marinhos'),
                i::__('Cultura dos faxinalenses'),
                i::__('Cultura dos geraizeiros'),
                i::__('Cultura dos ilhéus'),
                i::__('Cultura dos morroquianos'),
                i::__('Cultura dos pantaneiros'),
                i::__('Cultura dos pescadores artesanais'),
                i::__('Cultura dos povos ciganos'),
                i::__('Cultura dos povos e comunidades de terreiro/povos e comunidades de matriz africana'),
                i::__('Cultura dos Povos Nômades'),
                i::__('Cultura dos Povos Originários'),
                i::__('Cultura dos raizeiros'),
                i::__('Cultura dos retireiros do Araguaia'),
                i::__('Cultura dos ribeirinhos'),
                i::__('Cultura dos vazanteiros'),
                i::__('Cultura dos veredeiros'),
                i::__('Cultura e Bem Viver'),
                i::__('Cultura e Comunicação'),
                i::__('Cultura e decolonialidade'),
                i::__('Cultura e Desenvolvimento Social'),
                i::__('Cultura e Direitos Humanos'),
                i::__('Cultura e Educação'),
                i::__('Cultura e Esporte'),
                i::__('Cultura e Juventudes'),
                i::__('Cultura e Lazer'),
                i::__('Cultura e Política'),
                i::__('Cultura e Saúde'),
                i::__('Cultura e Território'),
                i::__('Cultura e Turismo'),
                i::__('Cultura Estrangeira (imigrantes)'),
                i::__('Cultura Hip Hop'),
                i::__('Cultura Italiana'),
                i::__('Cultura Japonesa'),
                i::__('Cultura LGBTQIAPN+'),
                i::__('Cultura Negra'),
                i::__('Cultura Urbana'),
                i::__('Cultura, Infância e Adolescência'),
                i::__('Cultura, Meio Ambiente e Sustentabilidade'),
                i::__('Culturas Afrobrasileiras'),
                i::__('Culturas dos Povos Originários'),
                i::__('Culturas Populares'),
                i::__('Culturas Tradicionais'),
                i::__('Culturas Tradicionais e Populares'),
                i::__('Dança'),
                i::__('Dança Afro'),
                i::__('Dança Contemporânea'),
                i::__('Dança de Salão'),
                i::__('Dança do Ventre'),
                i::__('Dança Moderna'),
                i::__('Dança Silvestre'),
                i::__('Danças Clássicas'),
                i::__('Danças Contemporâneas'),
                i::__('Danças Estrangeiras'),
                i::__('Danças Populares'),
                i::__('Danças Urbanas'),
                i::__('Declamação'),
                i::__('Desenho Industrial'),
                i::__('Design'),
                i::__('Design de Interiores'),
                i::__('Design de Jóias'),
                i::__('Design de Moda'),
                i::__('Design e Serviços Criativos'),
                i::__('Design Gráfico'),
                i::__('Design Paisagístico'),
                i::__('Design para a Sociobioeconomia'),
                i::__('Diário'),
                i::__('Direito'),
                i::__('Direito Autoral'),
                i::__('Documentário'),
                i::__('Drama'),
                i::__('Economia Criativa e da Cultura'),
                i::__('Economia, Produção e Áreas Técnicas da Cultura'),
                i::__('Ensaios'),
                i::__('Epístola'),
                i::__('Epopeia'),
                i::__('Escola de Samba'),
                i::__('Escultura (Artes Visuais)'),
                i::__('Escultura (Artesanato)'),
                i::__('Espetáculo de Circo'),
                i::__('Fábula'),
                i::__('Fandango'),
                i::__('Fanfarra'),
                i::__('Festa do Divino'),
                i::__('Festas Populares'),
                i::__('Festejos Juninos'),
                i::__('Ficção'),
                i::__('Filme-ensaio'),
                i::__('Filologia'),
                i::__('Filosofia'),
                i::__('Folia de Reis'),
                i::__('Fomento editorial'),
                i::__('Forró'),
                i::__('Forró (Dança)'),
                i::__('Fotografia'),
                i::__('Frevo'),
                i::__('Frevo (dança)'),
                i::__('Funk'),
                i::__('Fuxico'),
                i::__('Gastronomia'),
                i::__('Geografia Humana'),
                i::__('Gestão criativa'),
                i::__('Gestão Cultural'),
                i::__('Grafite'),
                i::__('Gravura'),
                i::__('Guarânia'),
                i::__('História'),
                i::__('Humanidades'),
                i::__('Imóvel tombado'),
                i::__('Internet Podcasting'),
                i::__('Interseccionalidades'),
                i::__('Intervenção Urbana'),
                i::__('Jazz (Dança)'),
                i::__('Jogos Eletrônicos/Games'),
                i::__('Jongo'),
                i::__('Jornais e outros periódicos'),
                i::__('K-pop Dance'),
                i::__('Lambada'),
                i::__('Lapidação'),
                i::__('Leitura'),
                i::__('Lenda'),
                i::__('Letras e Literatura (Humanidades)'),
                i::__('Lidas Campeiras'),
                i::__('Linguística'),
                i::__('Literatura'),
                i::__('Literatura Infantil'),
                i::__('Livro'),
                i::__('Livro, Leitura e Literatura'),
                i::__('Macramê'),
                i::__('Maracatu'),
                i::__('Maracatu (Dança)'),
                i::__('Marujada'),
                i::__('Mediação de Leitura'),
                i::__('Memórias'),
                i::__('Mídias Interativas'),
                i::__('Mídias Sociais'),
                i::__('Mito'),
                i::__('Moda de Viola'),
                i::__('Mosaico'),
                i::__('Movimento Sound System'),
                i::__('Museologia'),
                i::__('Museu (Patrimônio Material)'),
                i::__('Museu tombado'),
                i::__('Música'),
                i::__('Música de Câmara'),
                i::__('Música de Concerto'),
                i::__('Música Eletrônica'),
                i::__('Música Instrumental'),
                i::__('Música Popular'),
                i::__('Musical'),
                i::__('Novela'),
                i::__('Obra Seriada'),
                i::__('Oktober Fest'),
                i::__('Ópera'),
                i::__('Orquestra Sinfônica'),
                i::__('Ourivesaria'),
                i::__('Outras Danças'),
                i::__('Outras Danças Clássicas'),
                i::__('Outras Danças Modernas'),
                i::__('Outras Danças Populares'),
                i::__('Paisagens Culturais'),
                i::__('Patrimônio Cultural'),
                i::__('Patrimônio Cultural Imaterial'),
                i::__('Patrimônio Cultural Material'),
                i::__('Patrimônio Histórico Edificado'),
                i::__('Performance'),
                i::__('Performance Literária'),
                i::__('Permacultura'),
                i::__('Pesquisa'),
                i::__('Pintura'),
                i::__('Poesia'),
                i::__('Políticas e Gestão Culturais'),
                i::__('Procissão do Fogaréu'),
                i::__('Produção Audiovisual'),
                i::__('Produção Cultural'),
                i::__('Produção de Eventos'),
                i::__('Psicologia'),
                i::__('Punk'),
                i::__('Rádio'),
                i::__('Rádio Comunitária'),
                i::__('Rádio e TV'),
                i::__('Rap'),
                i::__('Rasqueado'),
                i::__('Realidade Virtual'),
                i::__('Reggae'),
                i::__('Reisado'),
                i::__('Renda'),
                i::__('Renda de Bilro'),
                i::__('Repente'),
                i::__('Rima e improviso'),
                i::__('Rock'),
                i::__('Romance'),
                i::__('Romaria do Divino Pai Eterno'),
                i::__('Salsa (Dança)'),
                i::__('Salvaguarda do Patrimônio Cultural Imaterial'),
                i::__('Samba'),
                i::__('Samba (Dança)'),
                i::__('Samba de Roda'),
                i::__('Sapateado'),
                i::__('Sátira'),
                i::__('Sertanejo'),
                i::__('Sítio Arqueológico'),
                i::__('Sítios Históricos e Arqueológicos'),
                i::__('Slam'),
                i::__('Sociologia'),
                i::__('Stand-up Comedy'),
                i::__('Street Jazz'),
                i::__('Tambor de Crioula'),
                i::__('Tango (Dança)'),
                i::__('Teatro'),
                i::__('Teatro de Bonecos'),
                i::__('Teatro de Improviso'),
                i::__('Teatro de Máscaras'),
                i::__('Teatro de Rua'),
                i::__('Teatro de Sombras'),
                i::__('Teatro do Absurdo'),
                i::__('Teatro do Oprimido'),
                i::__('Teatro Experimental'),
                i::__('Teatro Infantil'),
                i::__('Tecelagem'),
                i::__('Tecnobrega'),
                i::__('Televisão'),
                i::__('Teologia'),
                i::__('Tradução e Interpretação'),
                i::__('Tragédia'),
                i::__('Trançagem'),
                i::__('Transversalidades'),
                i::__('Vídeo'),
                i::__('Vídeo Experimental'),
                i::__('Vídeo Performance'),
                i::__('Vídeo por demanda'),
                i::__('Video Teatro'),
                i::__('Videoarte'),
                i::__('Videocast'),
                i::__('Videoclipe'),
                i::__('Videodança'),
                i::__('Webdesign'),
                i::__('Websérie'),
                i::__('Xaxado'),
                i::__('Xilogravura'),
                i::__('Outra'),
            ),
        ]);
        $app->registerMetadata($culturalArtisticSegment, Workplan::class);

        // metadados goal
        $monthInitial = new Metadata('monthInitial', ['label' => \MapasCulturais\i::__('Mês inicial')]);
        $app->registerMetadata($monthInitial, Goal::class);

        $monthEnd = new Metadata('monthEnd', ['label' => \MapasCulturais\i::__('Mês final')]);
        $app->registerMetadata($monthEnd, Goal::class);

        $title = new Metadata('title', ['label' => \MapasCulturais\i::__('Título da meta')]);
        $app->registerMetadata($title, Goal::class);

        $description = new Metadata('description', ['label' => \MapasCulturais\i::__('Descrição')]);
        $app->registerMetadata($description, Goal::class);


        $culturalMakingStage = new Metadata('culturalMakingStage', [
            'label' => \MapasCulturais\i::__('Etapa do fazer cultural'),
            'type' => 'select',
            'options' => array(
                \MapasCulturais\i::__('Criação, invenção e inovação'),
                \MapasCulturais\i::__('Difusão, divulgação'),
                \MapasCulturais\i::__('Formação e transmissão'),
                \MapasCulturais\i::__('Intercâmbios, trocas e cooperação'),
                \MapasCulturais\i::__('Análise, crítica, estudo, investigação, pesquisa e reflexão'),
                \MapasCulturais\i::__('Fruição, consumo e circulação'),
                \MapasCulturais\i::__('Conservação, memória e preservação'),
                \MapasCulturais\i::__('Organização, legislação, gestão, produção da cultura'),
            ),
        ]);
        $app->registerMetadata($culturalMakingStage, Goal::class);

        $amount = new Metadata('amount', ['label' => \MapasCulturais\i::__('Valor da meta (R$)')]);
        $app->registerMetadata($amount, Goal::class);
    
        // metadados delivery
        $name = new Metadata('name', ['label' => \MapasCulturais\i::__('Nome da entrega')]);
        $app->registerMetadata($name, Delivery::class);

        $description = new Metadata('description', ['label' => \MapasCulturais\i::__('Descrição')]);
        $app->registerMetadata($description, Delivery::class);

        $type = new Metadata('type', ['label' => \MapasCulturais\i::__('Tipo de entrega')]);
        $app->registerMetadata($type, Delivery::class);

        $segmentDelivery = new Metadata('segmentDelivery', [
            'label' => \MapasCulturais\i::__('Segmento artístico cultural da entrega'),
            'type' => 'select',
            'options' => array(
                \MapasCulturais\i::__('Artes Visuais'),  
                \MapasCulturais\i::__('Artesanato'),  
                \MapasCulturais\i::__('Audiovisual e Mídias Interativas'),  
                \MapasCulturais\i::__('Circo'),  
                \MapasCulturais\i::__('Culturas Tradicionais e Populares'),  
                \MapasCulturais\i::__('Culturas dos Povos Originários'),  
                \MapasCulturais\i::__('Dança'),  
                \MapasCulturais\i::__('Design e Serviços Criativos'),  
                \MapasCulturais\i::__('Economia, Produção e Áreas Técnicas da Cultura'),  
                \MapasCulturais\i::__('Festas Populares'),  
                \MapasCulturais\i::__('Humanidades'),  
                \MapasCulturais\i::__('Livro, Leitura e Literatura'),  
                \MapasCulturais\i::__('Música'),  
                \MapasCulturais\i::__('Patrimônio Cultural Imaterial'),  
                \MapasCulturais\i::__('Patrimônio Cultural Material'),  
                \MapasCulturais\i::__('Performance'),  
                \MapasCulturais\i::__('Produção e Áreas Técnicas da Cultura'),  
                \MapasCulturais\i::__('Teatro'),  
                \MapasCulturais\i::__('Transversalidades')
            ),
        ]);
        $app->registerMetadata($segmentDelivery, Delivery::class);

        $budgetAction = new Metadata('budgetAction', [
            'label' => \MapasCulturais\i::__('Ação orçamentária'),
            'type' => 'select',
            'options' => array(
                \MapasCulturais\i::__('Ação 1'),
                \MapasCulturais\i::__('Ação 2'),
            ),
        ]);
        $app->registerMetadata($budgetAction, Delivery::class);


        $expectedNumberPeople = new Metadata('expectedNumberPeople', ['label' => \MapasCulturais\i::__('Número previsto de pessoas')]);
        $app->registerMetadata($expectedNumberPeople, Delivery::class);

        $generaterRevenue = new Metadata('generaterRevenue', [
            'label' => \MapasCulturais\i::__('A entrega irá gerar receita?'),
            'type' => 'select',
            'options' => array(
                'true' => \MapasCulturais\i::__('Sim'),
                'false' => \MapasCulturais\i::__('Não'),
            ),
        ]);
        $app->registerMetadata($generaterRevenue, Delivery::class);

        $renevueQtd = new Metadata('renevueQtd', ['label' => \MapasCulturais\i::__('Quantidade')]);
        $app->registerMetadata($renevueQtd, Delivery::class);

        $unitValueForecast = new Metadata('unitValueForecast', ['label' => \MapasCulturais\i::__('Previsão de valor unitário')]);
        $app->registerMetadata($unitValueForecast, Delivery::class);

        $totalValueForecast = new Metadata('totalValueForecast', ['label' => \MapasCulturais\i::__('Previsão de valor total')]);
        $app->registerMetadata($totalValueForecast, Delivery::class);
    }
}