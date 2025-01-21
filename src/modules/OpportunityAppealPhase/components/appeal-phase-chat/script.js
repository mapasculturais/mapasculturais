/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted
 */

app.component('appeal-phase-chat', {
    template: $TEMPLATES['appeal-phase-chat'],

    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('appeal-phase-chat')
        return { text, hasSlot }
    },

    mounted() { 
        const threadData = $MAPAS.config.appealPhaseChat?.[this.registration.id];
        if (!threadData) {
            return;
        }

        const apiThread = new API('chatthread');
        const thread = apiThread.getEntityInstance(threadData.id);
        thread.populate(threadData);
        this.thread = thread;
    },

    data() {
        return {
            thread: null,
        }
    },

    computed: {
        statusList() {
            return $MAPAS.config.evaluationMethodAppealPhase.statuses;
        },
    },

    methods: {
        initMessage(message) {
            if ((typeof message.payload) !== 'object') {
                message.payload = {};
            } 
            
        },

        verifyState(status) {
            status = parseInt(status);
            switch (status) {
                case 10:
                    return 'success__color';
				case 3 : 
                    return 'danger__color';
				case 8 : 
                case 1 :
                    return 'warning__color';

                case null:
                default:
                    return '';
            }
        },
    },
});
