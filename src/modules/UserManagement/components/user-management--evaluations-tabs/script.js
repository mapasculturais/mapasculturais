app.component('user-management--evaluations-tabs', {
    template: $TEMPLATES['user-management--evaluations-tabs'],

    setup() {
        const text = Utils.getTexts('user-management--evaluations-tabs');
        return { text };
    },

    props: {
        user: {
            type: Entity,
            required: true
        },

        limit: {
            type: Number,
            default: 20
        },
    },

    data() {
        const baseQuery = {
            user: `EQ(${this.user.id})`,
            '@order': 'updateTimestamp DESC',
            '@permissions': 'view',
        };

        return {
            select: 'id,status,result,createTimestamp,sentTimestamp,updateTimestamp,committee,registration.{id,number,opportunity.{id,name,files.avatar}}',
            phasesSelect: 'id,name,parent.{name,status},status,registrationFrom,registrationTo,isContinuousFlow,hasEndDate,isReportingPhase',
            phasesQuery: {
                '@user': this.user.id,
                '@permissions': 'evaluateRegistrations',
                '@permissionsUser': this.user.id,
                status: 'IN(1,-1,-20)',
                isReportingPhase: 'OR(EQ(0),NULL())',
                '@order': 'createTimestamp DESC',
            },
            queries: {
                started: { status: 'EQ(0)', ...baseQuery },
                completed: { status: 'EQ(1)', ...baseQuery },
                sent: { status: 'EQ(2)', ...baseQuery },
            },
        };
    },

    methods: {
        evaluationUrl(entity) {
            const registrationId = entity.registration?.id;
            if (!registrationId) {
                return '#';
            }

            return Utils.createUrl('registration', 'evaluation', {
                id: registrationId,
                user: this.user.id,
            });
        },
    },
});
