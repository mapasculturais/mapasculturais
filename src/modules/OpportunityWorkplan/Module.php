<?php
namespace OpportunityWorkplan;

use MapasCulturais\App,
    MapasCulturais\i;
use OpportunityWorkplan\Controllers\Delivery as ControllersDelivery;
use OpportunityWorkplan\Controllers\Workplan as ControllersWorkplan;
use OpportunityWorkplan\Entities\Workplan;
use OpportunityWorkplan\Entities\Goal;
use MapasCulturais\Definitions\Metadata;
use OpportunityWorkplan\Entities\Delivery;

class Module extends \MapasCulturais\Module{
    function _init(){
        $app = App::i();

        $app->hook('app.init:after', function () use($app) {
            $app->hook("template(opportunity.edit.opportunity-data-collection-config-form):after", function(){
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
                        $errors['workplan'] = [i::__('Plano de metas obrigatório.')];
                    }

                    if (!$workplan?->projectDuration) {
                        $errors['projectDuration'] = [i::__('Plano de metas - Duração do projeto (meses) obrigatório.')];
                    }

                    if (!$workplan?->culturalArtisticSegment) {
                        $errors['culturalArtisticSegment'] = [i::__('Plano de metas - Segmento artistico-cultural obrigatório.')];
                    }
                   
                    if ($workplan?->goals->isEmpty()) {
                        $errors['goal'] = [i::__('Meta do plano de metas obrigatório.')];
                    }

                    if ($registration->opportunity->workplan_deliveryReportTheDeliveriesLinkedToTheGoals) {
                        if (is_iterable($workplan?->goals)) {
                            foreach ($workplan?->goals as $goal) {
                                if ($goal?->deliveries->isEmpty()) {
                                    $errors['delivery'] = [i::__('Entrega da meta do plano de metas obrigatório.')];
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
        $app->registerController('delivery', ControllersDelivery::class);
        
        $this->registerOpportunityMetadata('workplanLabelDefault', [
            'label' => i::__('Plano de metas label'),
            'default_value' => 'Plano de metas'
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
            'label' => i::__('Habilitar plano de metas'),
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

        $app->registerFileGroup('delivery', new \MapasCulturais\Definitions\FileGroup('evidences', unique:true));

        // metadados workplan
        $projectDuration = new Metadata('projectDuration', ['label' => \MapasCulturais\i::__('Duração do projeto (meses)')]);
        $app->registerMetadata($projectDuration, Workplan::class);

        $culturalArtisticSegment = new Metadata('culturalArtisticSegment', [
            'label' => \MapasCulturais\i::__('Segmento artistico-cultural'),
            'type' => 'select',
            'options' => array(
                \MapasCulturais\i::__('Artes Visuais'),
                \MapasCulturais\i::__('Artesanato'),
                \MapasCulturais\i::__('Audiovisual e Mídias Interativas'),
                \MapasCulturais\i::__('Circo'),
                \MapasCulturais\i::__('Culturas dos Povos Originários'),
                \MapasCulturais\i::__('Culturas Tradicionais e Populares'),
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
                \MapasCulturais\i::__('Teatro'),
                \MapasCulturais\i::__('Transversalidades'),
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
    
        // metadados delivery
        $name = new Metadata('name', ['label' => \MapasCulturais\i::__('Nome da entrega')]);
        $app->registerMetadata($name, Delivery::class);

        $description = new Metadata('description', ['label' => \MapasCulturais\i::__('Descrição')]);
        $app->registerMetadata($description, Delivery::class);

        $type = new Metadata('type', ['label' => \MapasCulturais\i::__('Tipo de entrega')]);
        $app->registerMetadata($type, Delivery::class);

        
        $typeDelivery = new Metadata('typeDelivery', [
            'label' => \MapasCulturais\i::__('Tipo entrega'),
            'type' => 'select',
            'options' => array(
                \MapasCulturais\i::__("Acervo cultural adquirido"),
                \MapasCulturais\i::__("Acervo cultural criado"),
                \MapasCulturais\i::__("Acervo cultural mantido"),
                \MapasCulturais\i::__("Ação de formação realizada"),
                \MapasCulturais\i::__("Adereço criado"),
                \MapasCulturais\i::__("Agente cultural fomentado"),
                \MapasCulturais\i::__("Album musical criado"),
                \MapasCulturais\i::__("Aplicativo criado"),
                \MapasCulturais\i::__("Apresentação realizada"),
                \MapasCulturais\i::__("Arte Gráfica criada"),
                \MapasCulturais\i::__("Arte Visual comercializada"),
                \MapasCulturais\i::__("Arte Visual criada"),
                \MapasCulturais\i::__("Artesanato comercializado"),
                \MapasCulturais\i::__("Artesanato criado"),
                \MapasCulturais\i::__("Assemblage criada"),
                \MapasCulturais\i::__("Aula realizada"),
                \MapasCulturais\i::__("Áudio gravado"),
                \MapasCulturais\i::__("Audiodescrição criada"),
                \MapasCulturais\i::__("Audiolivro criado"),
                \MapasCulturais\i::__("Audiolivro reproduzido"),
                \MapasCulturais\i::__("Bem cultural adquirido"),
                \MapasCulturais\i::__("Bem cultural conservado"),
                \MapasCulturais\i::__("Bem cultural registrado"),
                \MapasCulturais\i::__("Bem cultural restaurado"),
                \MapasCulturais\i::__("Bem cultural tombado"),
                \MapasCulturais\i::__("Biblioteca construída"),
                \MapasCulturais\i::__("Biblioteca mantida"),
                \MapasCulturais\i::__("Blog criado"),
                \MapasCulturais\i::__("Bolsa concedida"),
                \MapasCulturais\i::__("Capacitação realizada"),
                \MapasCulturais\i::__("Caricatura criada"),
                \MapasCulturais\i::__("Cartilha distribuída"),
                \MapasCulturais\i::__("Cartum criado"),
                \MapasCulturais\i::__("Catálogo distribuído"),
                \MapasCulturais\i::__("Cerâmica criada"),
                \MapasCulturais\i::__("Circulação realizada"),
                \MapasCulturais\i::__("Concertado realizado"),
                \MapasCulturais\i::__("Concurso cultural realizado"),
                \MapasCulturais\i::__("Conferência realizada"),
                \MapasCulturais\i::__("Congresso realizado"),
                \MapasCulturais\i::__("Conteúdo cultural digital criado"),
                \MapasCulturais\i::__("Coreografia criada"),
                \MapasCulturais\i::__("Curta-metragem criado"),
                \MapasCulturais\i::__("Curso realizado"),
                \MapasCulturais\i::__("Desenho criado"),
                \MapasCulturais\i::__("Design criado"),
                \MapasCulturais\i::__("Design Gráfico criado"),
                \MapasCulturais\i::__("Desfile realizado"),
                \MapasCulturais\i::__("Direito autoral remunerado"),
                \MapasCulturais\i::__("Disco criado"),
                \MapasCulturais\i::__("Disco distribuído"),
                \MapasCulturais\i::__("Documentário criado"),
                \MapasCulturais\i::__("Dramaturgia criada"),
                \MapasCulturais\i::__("E-Book criado"),
                \MapasCulturais\i::__("E-Book disponibilizado"),
                \MapasCulturais\i::__("Encontro cultural realizado"),
                \MapasCulturais\i::__("Ensaio aberto realizado"),
                \MapasCulturais\i::__("Equipamento cultural construído"),
                \MapasCulturais\i::__("Equipamento cultural mantido"),
                \MapasCulturais\i::__("Equipamento cultural modernizado"),
                \MapasCulturais\i::__("Escultura comercializada"),
                \MapasCulturais\i::__("Escultura criada"),
                \MapasCulturais\i::__("Espaço cultural construído"),
                \MapasCulturais\i::__("Espaço cultural mantido"),
                \MapasCulturais\i::__("Espaço e/ou equipamento cultural construído"),
                \MapasCulturais\i::__("Espaço e/ou equipamento cultural mantido"),
                \MapasCulturais\i::__("Espaço e/ou equipamento cultural reformado"),
                \MapasCulturais\i::__("Espetáculo realizado"),
                \MapasCulturais\i::__("Evento Cultural realizado"),
                \MapasCulturais\i::__("Exibição realizada"),
                \MapasCulturais\i::__("Exposição realizada"),
                \MapasCulturais\i::__("Fanzine criado"),
                \MapasCulturais\i::__("Festa popular realizada"),
                \MapasCulturais\i::__("Feira realizada"),
                \MapasCulturais\i::__("Ficção criada"),
                \MapasCulturais\i::__("Figurino criado"),
                \MapasCulturais\i::__("Filme distribuído"),
                \MapasCulturais\i::__("Fomento cultural concedido"),
                \MapasCulturais\i::__("Fotografia criada"),
                \MapasCulturais\i::__("Game criado"),
                \MapasCulturais\i::__("Grafitti criado"),
                \MapasCulturais\i::__("Gravura criada"),
                \MapasCulturais\i::__("Grupo artístico-cultural fomentado"),
                \MapasCulturais\i::__("Grupo artístico-cultural mantido"),
                \MapasCulturais\i::__("História em Quadrinhos criada"),
                \MapasCulturais\i::__("Ilustração criada"),
                \MapasCulturais\i::__("Imóvel cultural adquirido"),
                \MapasCulturais\i::__("Imóvel cultural conservado"),
                \MapasCulturais\i::__("Imóvel cultural tombado"),
                \MapasCulturais\i::__("Ingresso comercializado"),
                \MapasCulturais\i::__("Instalação criada"),
                \MapasCulturais\i::__("Intercâmbio realizado"),
                \MapasCulturais\i::__("Inventário cultural criado"),
                \MapasCulturais\i::__("Inventário cultural mantido"),
                \MapasCulturais\i::__("Investigações realizada"),
                \MapasCulturais\i::__("Joia de valor cultural comercializada"),
                \MapasCulturais\i::__("Joia de valor cultural criada"),
                \MapasCulturais\i::__("Jornal criado"),
                \MapasCulturais\i::__("Jornal distribuído"),
                \MapasCulturais\i::__("Livro criado"),
                \MapasCulturais\i::__("Livro distribuído"),
                \MapasCulturais\i::__("Longa-metragem criado"),
                \MapasCulturais\i::__("Mentoria realizada"),
                \MapasCulturais\i::__("Mostra realizada"),
                \MapasCulturais\i::__("Movcéu adquirido"),
                \MapasCulturais\i::__("Mural criado"),
                \MapasCulturais\i::__("Música criada"),
                \MapasCulturais\i::__("Objeto cultural criado"),
                \MapasCulturais\i::__("Obra audiovisual criada"),
                \MapasCulturais\i::__("Obra circense criada"),
                \MapasCulturais\i::__("Obra de dança criada"),
                \MapasCulturais\i::__("Obra e/ou conteúdo cultural distribuído"),
                \MapasCulturais\i::__("Obra e/ou conteúdo cultural reproduzido"),
                \MapasCulturais\i::__("Obra e/ou produto cultural comercializado"),
                \MapasCulturais\i::__("Obra literária criada"),
                \MapasCulturais\i::__("Obra musical criada"),
                \MapasCulturais\i::__("Obra teatral criada"),
                \MapasCulturais\i::__("Ocupação Criativa realizada"),
                \MapasCulturais\i::__("Oficina realizada"),
                \MapasCulturais\i::__("Outra Obra e/ou Conteúdo Cultural Criado"),
                \MapasCulturais\i::__("Outra Performance e/ou Apresentação Realizada"),
                \MapasCulturais\i::__("Outra ação de investigação e/ou pesquisa realizada"),
                \MapasCulturais\i::__("Outra ação de salvaguarda do patrimônio cultural realizada"),
                \MapasCulturais\i::__("Outra Obra e/ou Conteúdo Cultural Distribuído e/ou Reproduzido"),
                \MapasCulturais\i::__("Outro Espaço e Equipamento Cultural Criado e/ou Mantido"),
                \MapasCulturais\i::__("Outro Evento, Festa e/ou Exibição Realizada"),
                \MapasCulturais\i::__("Outro Programa Educativo e/ou de Formação Realizado"),
                \MapasCulturais\i::__("Outro fomento e/ou incentivo cultural concedido"),
                \MapasCulturais\i::__("Parada realizada"),
                \MapasCulturais\i::__("Patrimônio cultural conservado"),
                \MapasCulturais\i::__("Patrimônio cultural registrado"),
                \MapasCulturais\i::__("Patrimônio cultural restaurado"),
                \MapasCulturais\i::__("Patrimônio cultural tombado"),
                \MapasCulturais\i::__("Performance realizada"),
                \MapasCulturais\i::__("Periódico criado"),
                \MapasCulturais\i::__("Periódico distribuído"),
                \MapasCulturais\i::__("Pesquisa realizada"),
                \MapasCulturais\i::__("Pintura criada"),
                \MapasCulturais\i::__("Plataforma digital criada"),
                \MapasCulturais\i::__("Podcast criado"),
                \MapasCulturais\i::__("Podcast reproduzido"),
                \MapasCulturais\i::__("Poesia criada"),
                \MapasCulturais\i::__("Premiação cultural concedida"),
                \MapasCulturais\i::__("Projeto de salvaguarda do patrimônio cultural criado"),
                \MapasCulturais\i::__("Projeto elaborado"),
                \MapasCulturais\i::__("Programa de Rádio criado"),
                \MapasCulturais\i::__("Programa de Rádio reproduzido"),
                \MapasCulturais\i::__("Programa de TV criado"),
                \MapasCulturais\i::__("Programa de TV reproduzido"),
                \MapasCulturais\i::__("Programa educativo realizado"),
                \MapasCulturais\i::__("Recital realizado"),
                \MapasCulturais\i::__("Residência artístico-cultural realizada"),
                \MapasCulturais\i::__("Revista criada"),
                \MapasCulturais\i::__("Roda de Leitura realizados"),
                \MapasCulturais\i::__("Romance criado"),
                \MapasCulturais\i::__("Roteiro criado"),
                \MapasCulturais\i::__("Sarau realizado"),
                \MapasCulturais\i::__("Seminário realizado"),
                \MapasCulturais\i::__("Série criada"),
                \MapasCulturais\i::__("Show realizado"),
                \MapasCulturais\i::__("Simpósio realizado"),
                \MapasCulturais\i::__("Single criado"),
                \MapasCulturais\i::__("Sítio histórico preservado"),
                \MapasCulturais\i::__("Site criado"),
                \MapasCulturais\i::__("Slam realizado"),
                \MapasCulturais\i::__("Software criado"),
                \MapasCulturais\i::__("Texto acadêmico elaborado"),
                \MapasCulturais\i::__("Texto acadêmico publicado"),
                \MapasCulturais\i::__("Texto cultural criado"),
                \MapasCulturais\i::__("Trilha Sonora criada"),
                \MapasCulturais\i::__("Vestuário criado"),
                \MapasCulturais\i::__("Vídeo criado"),
                \MapasCulturais\i::__("Videoarte criada"),
                \MapasCulturais\i::__("Visita Guiada realizada"),
                \MapasCulturais\i::__("Websérie criada"),
                \MapasCulturais\i::__("Websérie reproduzida"),
                \MapasCulturais\i::__("Workshop realizado"),
            ),
        ]);
        $app->registerMetadata($typeDelivery, Delivery::class);

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