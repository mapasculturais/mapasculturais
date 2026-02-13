app.component('registration-workplan', {
    template: $TEMPLATES['registration-workplan'],
    setup() {
        const text = Utils.getTexts('registration-workplan');
        return { text };
    },
    props: {
        registration: {
            type: Entity,
            required: true
        },
    },
    data() {
        this.getWorkplan();

        const entityWorkplan = new Entity('workplan');
        entityWorkplan.id = null;
        entityWorkplan.registrationId = this.registration.id;
        entityWorkplan.projectDuration = null;
        entityWorkplan.culturalArtisticSegment = null;
        entityWorkplan.goals = [];

        const enableWorkplanInStep = this.registration.opportunity.registrationSteps.length > 1 ? false : true;


        return {
            enableWorkplanInStep: enableWorkplanInStep,
            opportunity: this.registration.opportunity,
            workplan: entityWorkplan,
            workplanFields: $MAPAS.EntitiesDescription.workplan,
            expandedGoals: [],
            enableButtonNewGoal: false
        };
    },
    mounted() {
        this.handleHashChange();
        window.addEventListener('hashchange', this.handleHashChange);
    },
    computed: {
        getWorkplanLabelDefault() {
            return this.opportunity.workplanLabelDefault ? this.opportunity.workplanLabelDefault : $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
        getGoalLabelDefault() {
            const label = this.opportunity.goalLabelDefault ? this.opportunity.goalLabelDefault : $MAPAS.EntitiesDescription.opportunity.goalLabelDefault.default_value;
            return this.pluralParaSingular(label);
        },
        getDeliveryLabelDefault() {
            const label = this.opportunity.deliveryLabelDefault ? this.opportunity.deliveryLabelDefault : $MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value;
            return this.pluralParaSingular(label);
        },
        
    },
    methods: {
        handleHashChange() {
            const hash = window.location.hash;
            const stepMatch = hash.match(/#etapa_(\d+)/);

            if (this.registration.opportunity.registrationSteps.length > 1) {
                if (stepMatch && stepMatch[1]) {
                    const stepNumber = parseInt(stepMatch[1], 10); 
                    this.enableWorkplanInStep = stepNumber === this.registration.opportunity.registrationSteps.length;
    
                } else {
                    this.enableWorkplanInStep = false;
                }
            } else {
                this.enableWorkplanInStep = true;
            }

            if (this.enableWorkplanInStep) {
                this.startTutorialWorkplan();
            }
        },
        getWorkplan() {
            const api = new API('workplan');
            
            const response = api.GET(`${this.registration.id}`);
            response.then((res) => res.json().then((data) => {
                if (data.workplan != null) {
                    this.workplan = data.workplan;
                    this.updateEnableButtonNewGoal();
                }
            }));
        },
        
        async newGoal() {
            if (!this.validateGoal()) {
                return false;
            }

            this.startTutorialGoal();

            const entityGoal = new Entity('goal');
            entityGoal.id = null;
            entityGoal.monthInitial = null;
            entityGoal.monthEnd = null;
            entityGoal.title = null;
            entityGoal.description = null;
            entityGoal.culturalMakingStage = null;
            entityGoal.deliveries = [];

        
            this.workplan.goals.push(entityGoal);
            this.expandedGoals.push(this.workplan.goals.length - 1);
        },
        async deleteGoal(goal) {
            const api = new API('workplan');
            
            if (goal.id) {
                const response = api.DELETE('goal', {id: goal.id});
                response.then((res) => res.json().then((data) => {
                    this.workplan.goals = this.workplan.goals.filter(g => g.id !== goal.id);
                    this.updateEnableButtonNewGoal();
                }));
            } else {
                const index = this.workplan.goals.indexOf(goal);
                if (index !== -1) {
                    this.workplan.goals.splice(index, 1);Zs
                    this.expandedGoals = this.expandedGoals
                        .filter(i => i !== index)
                        .map(i => i > index ? i - 1 : i);
                    
                        this.updateEnableButtonNewGoal();
                }
            }
        },
        async newDelivery(goal) {
            if (!this.validateDelivery(goal)) {
                return false;
            }

            this.startTutorialDelivery();

            const entityDelivery = new Entity('delivery');
            entityDelivery.id = null;
            entityDelivery.name = null;
            entityDelivery.description = null;
            entityDelivery.typeDelivery = null
            entityDelivery.segmentDelivery = null;
            entityDelivery.expectedNumberPeople = null
            entityDelivery.generaterRevenue = null;
            entityDelivery.renevueQtd = null;
            entityDelivery.unitValueForecast = null;
            entityDelivery.totalValueForecast = null;
            
            // Novos campos de planejamento
            entityDelivery.artChainLink = null;
            entityDelivery.totalBudget = null;
            entityDelivery.numberOfCities = null;
            entityDelivery.numberOfNeighborhoods = null;
            entityDelivery.mediationActions = null;
            entityDelivery.paidStaffByRole = [];
            entityDelivery.teamCompositionGender = {
                masculine: 0,
                feminine: 0,
                nonBinary: 0,
                notDeclared: 0
            };
            entityDelivery.teamCompositionRace = {
                white: 0,
                black: 0,
                brown: 0,
                indigenous: 0,
                asian: 0,
                notDeclared: 0
            };
            entityDelivery.revenueType = [];
            entityDelivery.commercialUnits = null;
            entityDelivery.unitPrice = null;
            entityDelivery.hasCommunityCoauthors = null;
            entityDelivery.hasTransInclusionStrategy = null;
            entityDelivery.transInclusionActions = null;
            entityDelivery.hasAccessibilityPlan = null;
            entityDelivery.expectedAccessibilityMeasures = [];
            entityDelivery.hasEnvironmentalPractices = null;
            entityDelivery.environmentalPracticesDescription = null;
            entityDelivery.hasPressStrategy = null;
            entityDelivery.communicationChannels = [];
            entityDelivery.hasInnovationAction = null;
            entityDelivery.innovationTypes = [];
            entityDelivery.documentationTypes = [];

            goal.deliveries.push(entityDelivery);
        },
        async deleteDelivery(delivery) {
            const api = new API('workplan');

            if (delivery.id) {
                const response = api.DELETE('delivery', {id: delivery.id});
                response.then((res) => res.json().then((data) => {
                    this.workplan.goals = this.workplan.goals.map(goal => {
                        if (goal.deliveries) {
                            goal.deliveries = goal.deliveries.filter(delivery_ => delivery_.id !== delivery.id);
                        }
                        this.updateEnableButtonNewGoal();
                        return goal;
                    });
                }));
            } else {
                this.workplan.goals = this.workplan.goals.map(goal => {
                    if (goal.deliveries) {
                        goal.deliveries = goal.deliveries.filter(d => d !== delivery);
                    }
                    this.updateEnableButtonNewGoal();
                    return goal;
                });
            }
           
        },
        validateGoal() {
            const messages = useMessages();

            let validationMessages = [];

            this.workplan.goals.forEach((goal, index) => {
                let emptyFields = [];
                let position = index+1;

                // Verificar cada campo do objeto `goal`
                if (!goal.monthInitial) emptyFields.push("Mês inicial");
                if (!goal.monthEnd) emptyFields.push("Mês final");
                if (!goal.title) emptyFields.push(`Título da ${this.getGoalLabelDefault}`);
                if (!goal.description) emptyFields.push("Descrição");
                if (this.opportunity.workplan_metaInformTheStageOfCulturalMaking && !goal.culturalMakingStage) emptyFields.push("Etapa do fazer cultural");
                if (this.opportunity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals && goal.deliveries.length === 0) emptyFields.push(`${this.getDeliveryLabelDefault}`);

                const validateDelivery = this.validateDelivery(goal);
                if (validateDelivery.length > 0) {
                    emptyFields.push(`${this.getDeliveryLabelDefault}`);
                    emptyFields.push(validateDelivery);
                }
        
                // Adicionar mensagem ao array se houver campos vazios
                if (emptyFields.length > 0) {

                    const emptyFieldsList = `<ul>${emptyFields.map(item => `<li>${item}</li>`).join('')}</ul>`;

                    validationMessages.push(
                        `<br>A ${this.getGoalLabelDefault} ${position} possui os seguintes campos vazios:<br> ${emptyFieldsList}`
                    );
                }
            });
        
            if (validationMessages.length > 0) {
                messages.error(validationMessages);
                return false;
            }
            
            return true;
        },
        validateDelivery(goal) {
            const messages = useMessages();

            let validationMessages = [];

            goal.deliveries.forEach((delivery, index) => {
                let emptyFields = [];
                let position = index+1;
        
                if ('name' in delivery && !delivery.name) emptyFields.push(`Nome da ${this.getDeliveryLabelDefault}`);
                if ('description' in delivery && !delivery.description) emptyFields.push("Descrição");
                if ('typeDelivery' in delivery && !delivery.typeDelivery) emptyFields.push(`Tipo de ${this.getDeliveryLabelDefault}`);
                if (this.opportunity.workplan_registrationInformCulturalArtisticSegment && 'segmentDelivery' in delivery && !delivery.segmentDelivery) emptyFields.push(`Segmento artístico-cultural da ${this.getDeliveryLabelDefault}`);
                if (this.opportunity.workplan_registrationReportTheNumberOfParticipants && 'expectedNumberPeople' in delivery && !delivery.expectedNumberPeople) emptyFields.push("Número previsto de pessoas");
                if (this.opportunity.workplan_registrationReportExpectedRenevue && 'generaterRevenue' in delivery && !delivery.generaterRevenue) emptyFields.push(`A ${this.getDeliveryLabelDefault} irá gerar receita?`);
                if (delivery.generaterRevenue == 'true' && 'renevueQtd' in delivery && !delivery.renevueQtd) emptyFields.push("Quantidade");
                if (delivery.generaterRevenue == 'true' && 'unitValueForecast' in delivery && !delivery.unitValueForecast) emptyFields.push("Previsão de valor unitário");
                if (delivery.generaterRevenue == 'true' && 'totalValueForecast' in delivery && !delivery.totalValueForecast) emptyFields.push("Previsão de valor total");
                
                if (emptyFields.length > 0) {
                    const emptyFieldsList = `<ul>${emptyFields.map(item => `<li>${item}</li>`).join('')}</ul>`;

                    validationMessages.push(
                        `A ${this.getDeliveryLabelDefault} ${position} possui os seguintes campos vazios:<br> ${emptyFieldsList}<br>`
                    );
                }
            });
        
            return validationMessages;
        },
        async save_(enableValidations = true) {    
            if (enableValidations && !this.validateGoal()) {
                return false;
            }
            const messages = useMessages();        
            const api = new API('workplan');

            let data = {
                registrationId: this.registration.id,
                workplan: this.workplan,
            };

            const response = api.POST(`save`, data);
            response.then((res) => res.json().then((data) => {                
                this.getWorkplan();
                this.updateEnableButtonNewGoal();
                messages.success(this.text('Modificações salvas'));
            }));    
        },
        range(start, end) {
            return Array.from({ length: end - start + 1 }, (_, i) => start + i);
        },
        updateEnableButtonNewGoal() {
            this.enableButtonNewGoal = this.enableNewGoal(this.workplan);
        },
        enableNewGoal(workplan) {
            if (workplan.projectDuration == null || workplan.culturalArtisticSegment == null) {
                return false;
            }

            if (!this.opportunity.workplan_metaLimitNumberOfGoals) {
                return true;
            }
            
            return this.opportunity.workplan_metaMaximumNumberOfGoals > workplan.goals.length;
        },
        enableNewDelivery(goal) {
            if (this.opportunity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals) {
                if (this.opportunity.workplan_deliveryLimitNumberOfDeliveries) {
                    return this.opportunity.workplan_deliveryMaximumNumberOfDeliveries > goal.deliveries.length;
                }
                return true;
            }
            return false;
        },
        totalValueForecastToCurrency(delivery, renevueQtd, unitValueForecast) {
            let value = renevueQtd * unitValueForecast;
            delivery.totalValueForecast = value;
            return new Intl.NumberFormat("pt-BR", {
                style: "currency",
                currency: "BRL"
              }).format(value);
        },
        optionsProjectDurationData() {
            if (this.opportunity.workplan_dataProjectlimitMaximumDurationOfProjects) {
                return this.opportunity.workplan_dataProjectmaximumDurationInMonths;
            } else {
                return 60;
            }
        },
        toggle(index) {
            if (this.expandedGoals.includes(index)) {
              this.expandedGoals = this.expandedGoals.filter((i) => i !== index);
            } else {
              this.expandedGoals.push(index);
            }
        },
        isExpanded(index) {
            return this.expandedGoals.includes(index); 
        },
        pluralParaSingular(texto) {
            const palavras = texto.split(' ');
        
            const palavrasNoSingular = palavras.map(palavra => {
                if (palavra.endsWith('s')) {
                    palavra = palavra.slice(0, -1);
        
                    if (palavra.endsWith('e')) {
                        palavra = palavra.slice(0, -1);
                    }
    
                    if (palavra.endsWith('ã')) {
                        palavra = palavra.slice(0, -1) + 'ão';
                    } else if (palavra.endsWith('õ')) {
                        palavra = palavra.slice(0, -1) + 'ão';
                    } else if (palavra.endsWith('is')) {
                        palavra = palavra.slice(0, -2) + 'il';
                    } else if (palavra.endsWith('ns')) {
                        palavra = palavra.slice(0, -2) + 'm';
                    } else if (palavra.endsWith('ões')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    } else if (palavra.endsWith('ães')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    } else if (palavra.endsWith('ais')) {
                        palavra = palavra.slice(0, -2) + 'al';
                    } else if (palavra.endsWith('éis')) {
                        palavra = palavra.slice(0, -2) + 'el';
                    } else if (palavra.endsWith('óis')) {
                        palavra = palavra.slice(0, -2) + 'ol';
                    } else if (palavra.endsWith('uis')) {
                        palavra = palavra.slice(0, -2) + 'ul';
                    } else if (palavra.endsWith('ões')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    } else if (palavra.endsWith('ães')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    }
                }
        
                return palavra.toLowerCase();
            });
    
            return palavrasNoSingular.join(' ');
        },
        tutorialButtonsDisabled() {
            return [
                {
                    text: 'Desativar assistente de configuração',
                    action: () => {
                        this.disableTutorial();
                        this.tour.complete(); // Fecha o tutorial imediatamente
                    },
                    classes: 'button button--secondary button--sm'
                },
                {
                    text: 'Avançar',
                    action: this.tour.next,
                    classes: 'button button--primary button--sm'
                }
          ];
        },
        tutorialButtonsDefault() {
            return [
                {
                  text: 'Voltar',
                  action: this.tour.back,
                  classes: 'button button--solid-dark button--sm'
                },
                {
                  text: 'Avançar',
                  action: this.tour.next,
                  classes: 'button button--primary button--sm'
                }
              ];
        },
        titleTutorial() {
            return "Assistente de Configuração - Plano de metas";
        },
        startTutorialWorkplan() {
            if (this.isTutorialDisabled()) {
                return;
            }

            if (this.tour) {
                this.tour.complete(); 
                this.tour = null;
            }

            this.tour = new Shepherd.Tour({
              useModalOverlay: true, // Escurece a tela
              defaultStepOptions: {
                cancelIcon: {
                    enabled: true
                },
                classes: 'shadow-md bg-white p-4 rounded-lg', // Estilização
                scrollTo: true
              }
            });
      
            this.tour.addStep({
              id: 'registration-workplan',
              title: this.titleTutorial(),
              text: 'Bem-vindo ao tutorial do Plano de Metas! Aqui você aprenderá a usá-lo de forma fácil e eficiente.',
              attachTo: {
                element: '#registration-workplan',
                on: 'bottom'
              },
              buttons: this.tutorialButtonsDisabled()
            });
      
            this.tour.addStep({
              id: 'projectDuration',
              title: this.titleTutorial(),
              text: 'Este campo exibe a duração do projeto em meses.',
              attachTo: {
                element: '#projectDuration',
                on: 'bottom'
              },
              buttons: this.tutorialButtonsDefault()
            });

            this.tour.addStep({
                id: 'culturalArtisticSegment',
                title: this.titleTutorial(),
                text: 'Este campo exibe o segmento artístico-cultural. Após o preenchimento, um botão para cadastro de metas será habilitado.',
                attachTo: {
                  element: '#culturalArtisticSegment',
                  on: 'bottom'
                },
                buttons: this.tutorialButtonsDefault()
            });
      
            this.tour.start();
        },
        startTutorialGoal() {
            if (this.isTutorialDisabled()) {
                return;
            }

            if (this.tour) {
                this.tour.complete(); 
                this.tour = null;
            }

            this.tour = new Shepherd.Tour({
              useModalOverlay: true, // Escurece a tela
              defaultStepOptions: {
                classes: 'shadow-md bg-white p-4 rounded-lg', // Estilização
                scrollTo: true
              }
            });
      
            this.tour.addStep({
              id: 'container_goals',
              title: this.titleTutorial(),
              text: 'Preencha as metas do projeto.',
              attachTo: {
                element: '#container_goals',
                on: 'bottom'
              },
              buttons: this.tutorialButtonsDisabled()
            });
      
            this.tour.addStep({
              id: 'registration-workplan__delete-goal',
              title: this.titleTutorial(),
              text: 'O botão "Excluir meta" permite remover uma meta cadastrada ou em processo de cadastro.',
              attachTo: {
                element: '#registration-workplan__delete-goal',
                on: 'bottom'
              },
              buttons: this.tutorialButtonsDefault()
            });

            this.tour.addStep({
                id: 'button-registration-workplan__new-delivery',
                title: this.titleTutorial(),
                text: 'O botão "+ Entrega" permite adicionar uma nova entrega à sua meta.',
                attachTo: {
                  element: '#button-registration-workplan__new-delivery',
                  on: 'bottom'
                },
                buttons: this.tutorialButtonsDefault()
            });

            this.tour.addStep({
                id: 'button-registration-workplan__save-goal',
                title: this.titleTutorial(),
                text: 'Última etapa! Clique no botão "Salvar metas" para garantir que suas metas e entregas sejam salvas.',
                attachTo: {
                  element: '#button-registration-workplan__save-goal',
                  on: 'bottom'
                },
                buttons: this.tutorialButtonsDefault()
            });
            
            this.tour.start();
        },
        startTutorialDelivery() {
            if (this.isTutorialDisabled()) {
                return;
            }

            if (this.tour) {
                this.tour.complete(); 
                this.tour = null;
            }

            this.tour = new Shepherd.Tour({
              useModalOverlay: true, // Escurece a tela
              defaultStepOptions: {
                classes: 'shadow-md bg-white p-4 rounded-lg', // Estilização
                scrollTo: true
              }
            });
      
            this.tour.addStep({
              id: 'container_deliveries',
              title: this.titleTutorial(),
              text: 'Preencha as informações das suas entregas.',
              attachTo: {
                element: '#container_deliveries',
                on: 'bottom'
              },
              buttons: this.tutorialButtonsDisabled()
            });
      
            this.tour.addStep({
              id: 'registration-workplan__delete-delivery',
              title: this.titleTutorial(),
              text: 'Botão "Excluir entrega" para remover a entrega cadastrada ou em processo de cadastro.',
              attachTo: {
                element: '#registration-workplan__delete-delivery',
                on: 'bottom'
              },
              buttons: this.tutorialButtonsDefault()
            });

            this.tour.addStep({
                id: 'button-registration-workplan__save-goal',
                title: this.titleTutorial(),
                text: 'Última etapa! Para garantir que suas metas e entregas sejam salvas, clique no botão "Salvar metas".',
                attachTo: {
                  element: '#button-registration-workplan__save-goal',
                  on: 'bottom'
                },
                buttons: this.tutorialButtonsDefault()
            });

            this.disableTutorial();
            
            this.tour.start();
        },
        isTutorialDisabled() {
            return localStorage.getItem('tutorialDisabled') === 'true';
        },
        disableTutorial() {
            localStorage.setItem('tutorialDisabled', 'true');
        },
        enableTutorial() {
            localStorage.setItem('tutorialDisabled', 'false');
        },
        
        // ============================================
        // MÉTODOS PARA NOVOS CAMPOS ESTRUTURADOS
        // ============================================
        
        // Pessoas remuneradas por função
        addPaidStaffRole(delivery) {
            if (!delivery.paidStaffByRole) {
                delivery.paidStaffByRole = [];
            }
            delivery.paidStaffByRole.push({ role: '', count: 0 });
        },
        removePaidStaffRole(delivery, index) {
            delivery.paidStaffByRole.splice(index, 1);
        },
        
        // Calcular total de composição por gênero
        calculateGenderTotal(composition) {
            if (!composition) return 0;
            const total = (composition.masculine || 0) + 
                         (composition.feminine || 0) + 
                         (composition.nonBinary || 0) + 
                         (composition.notDeclared || 0);
            return total;
        },
        
        // Calcular total de composição por raça/cor
        calculateRaceTotal(composition) {
            if (!composition) return 0;
            const total = (composition.white || 0) + 
                         (composition.black || 0) + 
                         (composition.brown || 0) + 
                         (composition.indigenous || 0) + 
                         (composition.asian || 0) + 
                         (composition.notDeclared || 0);
            return total;
        },
    },
})