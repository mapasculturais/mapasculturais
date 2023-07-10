app.component('opportunity-enable-claim', {
    template: $TEMPLATES['opportunity-enable-claim'],

    setup() {
        const text = Utils.getTexts('opportunity-enable-claim');
        return { text };
    },
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    data() {
        let isActiveClaim = this.entity.claimDisabled === "0" ? true : false;
        return {
            isActiveClaim,
            timeOut: null,
        }
    },
    watch: {
        'isActiveClaim'(_new,_old){
            if(_new != _old){
                this.isActive(_new);
            }
        },
    },
    methods: {
        isActive(active) {
            this.entity.claimDisabled = active ? 0 : 1;
        },

        autoSave(){
            clearTimeout(this.timeout);
                this.timeout = setTimeout(()=>{
                    this.entity.save();
            },1500);
        }
    },
    computed: {

    }
})