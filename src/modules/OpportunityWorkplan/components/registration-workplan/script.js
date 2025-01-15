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

        return {
            opportunity: this.registration.opportunity,
            workplan: entityWorkplan,
            workplanFields: $MAPAS.EntitiesDescription.workplan,
            expandedGoals: [],
        };
    },
    computed: {
        getWorkplanLabelDefault() {
            return this.opportunity.workplanLabelDefault ? this.opportunity.workplanLabelDefault : $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
        getGoalLabelDefault() {
            return this.opportunity.goalLabelDefault ? this.opportunity.goalLabelDefault : $MAPAS.EntitiesDescription.opportunity.goalLabelDefault.default_value;
        },
        getDeliveryLabelDefault() {
            return this.opportunity.deliveryLabelDefault ? this.opportunity.deliveryLabelDefault : $MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value;
        },
    },
    methods: {
        getWorkplan() {
            const api = new API('workplan');
            
            const response = api.GET(`${this.registration.id}`);
            response.then((res) => res.json().then((data) => {
                if (data.workplan != null) {
                    this.workplan = data.workplan;
                }
            }));
        },
        async newGoal() {
            if (!this.validateGoal()) {
                return false;
            }

            const entityGoal = new Entity('goal');
            entityGoal.id = null;
            entityGoal.monthInitial = null;
            entityGoal.monthEnd = null;
            entityGoal.title = null;
            entityGoal.description = null;
            entityGoal.culturalMakingStage = null;
            entityGoal.amount = null;
            entityGoal.deliveries = [];

        
            this.workplan.goals.push(entityGoal);
            this.expandedGoals.push(this.workplan.goals.length - 1);
        },
        async deleteGoal(goalId) {
            const api = new API('workplan');

            const response = api.DELETE('goal', {id: goalId});
            response.then((res) => res.json().then((data) => {
                this.workplan.goals = this.workplan.goals.filter(goal => goal.id !== goalId);
            }));
        },
        async newDelivery(goal) {
            if (!this.validateDelivery(goal)) {
                return false;
            }

            const entityDelivery = new Entity('delivery');
            entityDelivery.id = null;
            entityDelivery.name = null;
            entityDelivery.description = null;
            entityDelivery.type = null
            entityDelivery.segmentDelivery = null;
            entityDelivery.budgetAction = null;
            entityDelivery.expectedNumberPeople = null
            entityDelivery.generaterRevenue = null;
            entityDelivery.renevueQtd = null;
            entityDelivery.unitValueForecast = null;
            entityDelivery.totalValueForecast = null;

            goal.deliveries.push(entityDelivery);
        },
        async deleteDelivery(deliveryId) {
            const api = new API('workplan');

            const response = api.DELETE('delivery', {id: deliveryId});
            response.then((res) => res.json().then((data) => {
                this.workplan.goals = this.workplan.goals.map(goal => {
                    if (goal.deliveries) {
                        goal.deliveries = goal.deliveries.filter(delivery => delivery.id !== deliveryId);
                    }
                    return goal;
                });
            }));
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
                if (this.opportunity.workplan_metaInformTheValueGoals && goal.amount == null || goal.amount === "") emptyFields.push(`Valor da ${this.getGoalLabelDefault} (R$)`);
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
                if ('type' in delivery && !delivery.type) emptyFields.push(`Tipo de ${this.getDeliveryLabelDefault}`);
                if (this.opportunity.workplan_registrationInformCulturalArtisticSegment && 'segmentDelivery' in delivery && !delivery.segmentDelivery) emptyFields.push(`Segmento artístico cultural da ${this.getDeliveryLabelDefault}`);
                if (this.opportunity.workplan_registrationInformActionPAAR && 'budgetAction' in delivery && !delivery.budgetAction) emptyFields.push("Ação orçamentária");
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
                messages.success(this.text('Modificações salvas'));
            }));    
        },
        range(start, end) {
            return Array.from({ length: end - start + 1 }, (_, i) => start + i);
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
    },
})