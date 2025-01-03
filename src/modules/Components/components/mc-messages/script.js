const useMessages = Pinia.defineStore('messages', {
    state: () => {
        return {
            messages: []
        }
    },
    getters: {
        activeMessages() {
            const messages = this.messages.filter((item)=>{
                if(item.active){
                    return item;
                }
            });
            return messages;
        },
    },
    actions: {
        push(message, timeout) {
            this.messages.push(message);
            
            // caso o timeout não tenha sido definido e
            // caso a quantidade de palavras na mensagem seja maior que 16, 
            // o timeout terá um acrescimo de 1 segundo para cada 5.5 palavras
            const messageWords = message.text?.split(' ') || message.split(' ');
            const minTimeout = 3000;
            const wordPerSecond = 5.5;
            const aditionalTimeout = Math.ceil(messageWords.length / wordPerSecond) * 1000;
            const extendedTimeout = aditionalTimeout > minTimeout ?  aditionalTimeout : minTimeout;

            setTimeout(() => {
                const index = this.messages.indexOf(message);
                this.messages.splice(index, 1);
            }, timeout || extendedTimeout);
        },
        
        success(text, timeout) {
            const type = 'success';
            const message = {active:true, type, text};
            this.push(message, timeout);
        },

        warning(text, timeout) {
            const type = 'warning';
            const message = {active:true, type, text};
            this.push(message, timeout);
        },

        error(text, timeout) {
            const type = 'error';
            const message = {active:true, type, text};
            this.push(message, timeout);
        },
        
    }
});

app.component('mc-messages', {
    template: $TEMPLATES['mc-messages'],
    
    setup() {
        const store = useMessages();
        return {store};
    },

    computed: {
        messages() {
            return this.store.activeMessages;
        },
    },
});
