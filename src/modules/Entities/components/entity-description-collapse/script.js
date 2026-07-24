app.component('entity-description-collapse', {
    template: $TEMPLATES['entity-description-collapse'],

    props: {
        text: {
            type: String,
            default: ''
        },
        label: {
            type: String,
            default: ''
        },
        maxLength: {
            type: Number,
            default: 240
        },
    },

    data() {
        return {
            expanded: false,
        };
    },

    computed: {
        plainText() {
            if (!this.text) {
                return '';
            }

            const tmp = document.createElement('div');
            tmp.innerHTML = this.text;
            return (tmp.textContent || tmp.innerText || '').trim();
        },

        needsToggle() {
            return this.plainText.length > this.maxLength;
        },

        displayHtml() {
            if (!this.needsToggle || this.expanded) {
                return this.text;
            }

            const truncated = this.plainText.substring(0, this.maxLength).trim();
            return truncated + '…';
        },
    },
});
