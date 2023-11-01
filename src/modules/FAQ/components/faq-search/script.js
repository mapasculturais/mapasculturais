app.component('faq-search', {
    template: $TEMPLATES['faq-search'],
    props: {
        section: {
            type: Boolean,
            default: false
        }
    },
    data() {
        const global = useGlobalState();
        return {
            data: $MAPAS.faq,
        }
    },

    methods: {
        search() {
            try {
                const global = useGlobalState();
                let terms = global.faqSearch.toLowerCase().split(" ");
                const updatedData = [];

                for (const section of this.data) {
                    const updatedContexts = [];

                    for (const context of section.contexts) {
                        const updatedQuestions = [];

                        for (const question of context.questions) {
                            const questionText = question.question.toLowerCase();
                            const answerText = question.answer.toLowerCase();
                            const tags = question.tags;

                            if (terms.some(term => (questionText.includes(term) || tags.includes(term) || answerText.includes(term)))) {
                                updatedQuestions.push(question);
                            }
                        }

                        if (updatedQuestions.length > 0) {
                            const updatedContext = { ...context, questions: updatedQuestions };
                            updatedContexts.push(updatedContext);
                        }
                    }

                    if (updatedContexts.length > 0) {
                        const updatedSection = { ...section, contexts: updatedContexts };
                        updatedData.push(updatedSection);
                    }
                }

                global.faqResults = updatedData;

            } catch (error) {
                console.error(error);
            }
        }
    },
});
