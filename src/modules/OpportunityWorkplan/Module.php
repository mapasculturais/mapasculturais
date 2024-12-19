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
            'type' => 'integer'
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
            'type' => 'integer'
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
            'type' => 'integer'
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
            'label' => i::__('Informar segmento artístico cultural'),
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
            'label' => \MapasCulturais\i::__('Segmento artistico cultural'),
            'type' => 'select',
            'options' => array(
                \MapasCulturais\i::__('Segmento 1'),
                \MapasCulturais\i::__('Segmento 12'),
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