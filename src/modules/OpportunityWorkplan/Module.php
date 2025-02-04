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
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Adereço criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Album musical criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Aplicativo criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Arte Gráfica criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Arte Visual criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Artesanato criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Assemblage criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Áudio gravado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Audiodescrição criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Audiolivro criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Blog criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Caricatura criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Cartum criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Cerâmica criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Conteúdo cultural digital criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Coreografia criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Curta-metragem criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Desenho criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Design criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Design Gráfico criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Disco criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Documentário criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Dramaturgia criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - E-Book criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Escultura criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Fanzine criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Ficção criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Figurino criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Fotografia criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Game criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Grafitti criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Gravura criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - História em Quadrinhos criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Ilustração criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Instalação criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Joia de valor cultural criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Jornal criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Livro criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Longa-metragem criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Mural criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Música criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Objeto cultural criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Obra audiovisual criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Obra circense criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Obra de dança criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Obra literária criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Obra musical criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Obra teatral criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Periódico criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Pintura criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Plataforma digital criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Podcast criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Poesia criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Programa de Rádio criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Programa de TV criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Revista criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Romance criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Roteiro criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Série criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Single criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Site criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Software criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Texto cultural criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Trilha Sonora criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Vestuário criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Vídeo criado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Videoarte criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Websérie criada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Criados - Outra Obra e/ou Conteúdo Cultural Criado"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Apresentação realizada"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Circulação realizada"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Concerto realizado"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Ensaio aberto realizado"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Espetáculo realizado"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Performance realizada"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Recital realizado"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Show realizado"),
                \MapasCulturais\i::__("Performances e Apresentações Realizadas - Outra Performance e/ou Apresentação Realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Evento Cultural realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Exposição realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Exibição realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Festa popular realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Conferência realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Congresso realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Encontro cultural realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Feira realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Roda de Leitura realizados"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Sarau realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Festival realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Ocupação Criativa realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Seminário realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Simpósio realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Slam realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Visita Guiada realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Mostra realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Parada realizada"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Desfile realizado"),
                \MapasCulturais\i::__("Eventos, Festas e Exibições Realizadas - Outro Evento, Festa e/ou Exibição Realizada"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Ação de formação realizada"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Aula realizada"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Capacitação realizada"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Curso realizado"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Mentoria realizada"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Oficina realizada"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Palestra realizada"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Programa educativo realizado"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Workshop realizado"),
                \MapasCulturais\i::__("Programas Educativos e/ou Ações de Formação Realizados - Outro Programa Educativo e/ou de Formação Realizado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Arte Visual comercializada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Artesanato comercializado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Audiolivro reproduzido"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Cartilha distribuída"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Catálogo distribuído"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Disco distribuído"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - E-Book disponibilizado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Escultura comercializada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Filme distribuído"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Ingresso comercializado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Joia de valor cultural comercializada"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Jornal distribuído"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Livro distribuído"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Obra e/ou conteúdo cultural distribuído"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Obra e/ou conteúdo cultural reproduzido"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Obra e/ou produto cultural comercializado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Periódico distribuído"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Podcast reproduzido"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Produto artesanal comercializado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Programa de Rádio reproduzido"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Programa de TV reproduzido"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Projeto elaborado"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Websérie reproduzida"),
                \MapasCulturais\i::__("Obras e Conteúdos Culturais Distribuídos e/ou Reproduzidos - Outra Obra e/ou Conteúdo Cultural Distribuído e/ou Reproduzido"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Acervo cultural adquirido"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Acervo cultural criado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Acervo cultural mantido"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Bem cultural adquirido"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Bem cultural conservado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Bem cultural registrado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Bem cultural restaurado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Bem cultural tombado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Imóvel cultural adquirido"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Imóvel cultural conservado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Imóvel cultural tombado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Inventário cultural criado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Inventário cultural mantido"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Patrimônio cultural conservado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Patrimônio cultural registrado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Patrimônio cultural restaurado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Patrimônio cultural tombado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Sítio histórico preservado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Projeto de salvaguarda do patrimônio cultural criado"),
                \MapasCulturais\i::__("Ações de salvaguarda do patrimônio cultural realizadas - Outra ação de salvaguarda do patrimônio cultural realizada"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Biblioteca construída"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Biblioteca mantida"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Equipamento cultural construído"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Equipamento cultural mantido"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Equipamento cultural modernizado"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Espaço cultural construído"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Espaço cultural mantido"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Espaço e/ou equipamento cultural construído"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Espaço e/ou equipamento cultural mantido"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Espaço e/ou equipamento cultural reformado"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Movcéu adquirido"),
                \MapasCulturais\i::__("Espaços e Equipamentos Culturais Construídos, Reformados e/ou Mantidos - Outro Espaço e Equipamento Cultural Criado e/ou Mantido"),
                \MapasCulturais\i::__("Investigações e/ou Pesquisas Realizadas - Investigações realizada"),
                \MapasCulturais\i::__("Investigações e/ou Pesquisas Realizadas - Intercâmbio realizado"),
                \MapasCulturais\i::__("Investigações e/ou Pesquisas Realizadas - Pesquisa realizada"),
                \MapasCulturais\i::__("Investigações e/ou Pesquisas Realizadas - Residência artístico-cultural realizada"),
                \MapasCulturais\i::__("Investigações e/ou Pesquisas Realizadas - Texto acadêmico elaborado"),
                \MapasCulturais\i::__("Investigações e/ou Pesquisas Realizadas - Texto acadêmico publicado"),
                \MapasCulturais\i::__("Investigações e/ou Pesquisas Realizadas - Outra ação de investigação e/ou pesquisa realizada"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Fomento cultural concedido"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Agente cultural fomentado"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Bolsa concedida"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Direito autoral remunerado"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Grupo artístico-cultural fomentado"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Grupo artístico-cultural mantido"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Concurso cultural realizado"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Premiação cultural concedida"),
                \MapasCulturais\i::__("Fomentos e Incentivos Culturais Concedidos - Outro fomento e/ou incentivo cultural concedido"),
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