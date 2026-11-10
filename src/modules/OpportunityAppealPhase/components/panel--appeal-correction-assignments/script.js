app.component('panel--appeal-correction-assignments', {
    template: $TEMPLATES['panel--appeal-correction-assignments'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation,
    },

    setup() {
        const text = Utils.getTexts('panel--appeal-correction-assignments');

        return { text };
    },

    props: {
        /**
         * Quando informado, lista só designações da oportunidade (fase
         * avaliativa ou 1ª fase do edital). Usado em #evaluations.
         */
        opportunityId: {
            type: [Number, String],
            default: null,
        },
        /**
         * Só designações disponíveis (ativas e no prazo). No carrossel do
         * painel isso é forçado: enviadas não aparecem.
         */
        availableOnly: {
            type: Boolean,
            default: false,
        },
        /**
         * Não renderiza o bloco quando a lista filtrada fica vazia.
         */
        hideWhenEmpty: {
            type: Boolean,
            default: false,
        },
        /**
         * Título opcional acima dos cards.
         */
        title: {
            type: String,
            default: '',
        },
        /**
         * Link "Ver todas" (lista completa; não usado no carrossel).
         */
        seeAllUrl: {
            type: String,
            default: '',
        },
        /**
         * Painel de controle: carrossel no padrão de Avaliações disponíveis.
         */
        carousel: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            loading: true,
            error: false,
            assignments: [],
            settings: {
                itemsToShow: 1.2,
                snapAlign: 'center',
            },
            breakpoints: {
                1300: {
                    itemsToScrool: 2.95,
                    itemsToShow: 2.3,
                    snapAlign: 'start',
                },
                1200: {
                    itemsToScrool: 2.8,
                    itemsToShow: 2.2,
                    snapAlign: 'start',
                },
                1100: {
                    itemsToScrool: 2.6,
                    itemsToShow: 2,
                    snapAlign: 'start',
                },
                1000: {
                    itemsToShow: 1.8,
                    snapAlign: 'start',
                },
                900: {
                    itemsToShow: 1.4,
                    snapAlign: 'start',
                },
                800: {
                    itemsToShow: 1.2,
                    snapAlign: 'start',
                },
                700: {
                    itemsToShow: 1.8,
                    snapAlign: 'start',
                },
                600: {
                    itemsToScrool: 1.8,
                    itemsToShow: 1.9,
                    snapAlign: 'start',
                },
                500: {
                    itemsToScrool: 1.5,
                    itemsToShow: 1.25,
                    snapAlign: 'start',
                },
                400: {
                    itemsToScrool: 1.4,
                    itemsToShow: 1.2,
                    snapAlign: 'start',
                },
                360: {
                    itemsToScrool: 1,
                    itemsToShow: 1,
                    snapAlign: 'start',
                },
                340: {
                    itemsToScrool: 1,
                    itemsToShow: 1,
                    snapAlign: 'start',
                },
            },
        };
    },

    computed: {
        filteredAssignments() {
            let list = this.assignments;

            if (this.opportunityId != null && this.opportunityId !== '') {
                const id = Number(this.opportunityId);
                list = list.filter((item) =>
                    Number(item.opportunityId) === id || Number(item.firstPhaseId) === id
                );
            }

            // Carrossel do painel: só pendentes (designada/rascunho/reaberta no prazo).
            // Enviadas somem daqui; histórico fica em Minhas avaliações.
            if (this.carousel || this.availableOnly) {
                list = list.filter((item) => item.available);
            }

            return list;
        },

        showBlock() {
            // Painel/carrossel: só aparece depois de carregar e se houver pendentes
            if (this.hideWhenEmpty || this.carousel) {
                if (this.loading || this.error) {
                    return false;
                }

                return this.filteredAssignments.length > 0;
            }

            return true;
        },
    },

    mounted() {
        this.fetchAssignments();
    },

    methods: {
        async fetchAssignments() {
            this.loading = true;
            this.error = false;

            try {
                const api = new API();
                const res = await api.GET(Utils.createUrl('appealCorrector', 'assignments'));

                if (res.ok) {
                    this.assignments = await res.json();
                } else {
                    this.error = true;
                }
            } catch (e) {
                this.error = true;
            } finally {
                this.loading = false;
            }
        },

        formatDate(value) {
            return value ? new Date(value).toLocaleString('pt-BR') : '';
        },

        statusLabel(status) {
            return this.text(`status-${status}`) || status;
        },

        unavailableLabel(assignment) {
            if (assignment.startsAt && new Date(assignment.startsAt) > new Date()) {
                return this.text('not-started');
            }

            return this.text('expired');
        },

        resizeSlides() {
            this.$refs.carousel?.updateSlideWidth();
        },
    },
});
