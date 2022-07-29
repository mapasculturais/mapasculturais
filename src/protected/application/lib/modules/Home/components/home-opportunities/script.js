app.component('home-opportunities', {
    template: $TEMPLATES['home-opportunities'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('home-opportunities')
        return { text }
    },

    mounted() { 
        window.onload = function () {
            this.lista = document.querySelector('.home-opportunities__content--cards-list');
            this.btnLeft = document.querySelector('.actions').firstElementChild;
            this.btnRight = document.querySelector('.actions').lastElementChild;

            /*  card = 328;
                gap/lastGap = 40;
                scrollMax = (((card+gap) * (totalElementos - 3)) - lastGap);  */
            this.scrollMax = ((368 * (this.lista.childElementCount - 3)) - 40); 
            this.scrollMin = 0;
            
            // Verifica se existem mais cartões que a largura máxima da lista
            this.tooMuchCards = ((368 * this.lista.childElementCount) >= this.lista.offsetWidth) ? true : false;

            if (this.tooMuchCards) {
                // desabilida apenas o left
                this.btnLeft.disabled = true;
            } else {
                // desabilida ambos
                this.btnLeft.disabled = true;
                this.btnRight.disabled = true;
            }
        }
    },

    props: {
        select: {
            type: String,
            default: '<id,name,shortDescription,terms,seals>'
        },
        query: {
            type: Object,
            default: {"@permissions": ""}
        }
    },

    data() {
        return {
            scroll: 0,
            lista: '',
            btnLeft: '',
            btnRight: '',
            scrollMax: 0,
            scrollMin: 0,
            tooMuchCards: false
        }
    },

    computed: {
        showActions() {
            
        }
    },
    
    methods: {

        right() {    
            if (this.tooMuchCards) {                
                if (this.btnLeft.disabled == true) this.btnLeft.disabled = false;
                
                if (this.scroll >= this.scrollMax) {
                    this.scroll = this.scrollMax;
                    this.btnRight.disabled = true;
                } else {
                    this.scroll = this.scroll + 368;
                    this.btnRight.disabled = false;
                }

                this.lista.scroll({ left: this.scroll, behavior: 'smooth' });
            }
        },

        left() {
            if (this.tooMuchCards) {
                if (this.btnRight.disabled == true) this.btnRight.disabled = false;

                if (this.scroll <= this.scrollMin) {
                    this.scroll = this.scrollMin
                    this.btnLeft.disabled = true;
                } else {
                    this.scroll = this.scroll - 368;
                    this.btnLeft.disabled = false;
                }

                this.lista.scroll({ left: this.scroll, behavior: 'smooth' });
            }
        }
    },
});
