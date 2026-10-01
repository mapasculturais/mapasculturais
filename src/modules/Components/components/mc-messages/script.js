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
        buildMessage(type, content) {
            const message = {active: true, type};

            if (content && typeof content === 'object' && !Array.isArray(content)) {
                if (typeof content.text === 'string') {
                    message.text = content.text;
                }
                if (typeof content.title === 'string') {
                    message.title = content.title;
                }
                if (Array.isArray(content.messages)) {
                    message.messages = content.messages;
                }
                if (content.persistent === true) {
                    message.persistent = true;
                }
            } else {
                message.text = content;
            }

            return message;
        },

        dismiss(message) {
            const index = this.messages.indexOf(message);
            if (index >= 0) {
                this.messages.splice(index, 1);
            }
        },

        push(message, timeout) {
            this.messages.push(message);

            if (message.persistent) {
                return;
            }
            
            // caso o timeout não tenha sido definido e
            // caso a quantidade de palavras na mensagem seja maior que 16, 
            // o timeout terá um acrescimo de 1 segundo para cada 5.5 palavras
            const content = [
                message.text,
                message.title,
                ...(message.messages || []),
            ].filter((item) => typeof item === 'string').join(' ');
            const messageWords = content.split(' ');
            const minTimeout = 3000;
            const wordPerSecond = 5.5;
            const aditionalTimeout = Math.ceil(messageWords.length / wordPerSecond) * 1000;
            const extendedTimeout = aditionalTimeout > minTimeout ?  aditionalTimeout : minTimeout;

            setTimeout(() => {
                this.dismiss(message);
            }, timeout || extendedTimeout);
        },
        
        success(content, timeout) {
            this.push(this.buildMessage('success', content), timeout);
        },

        warning(content, timeout) {
            this.push(this.buildMessage('warning', content), timeout);
        },

        error(content, timeout) {
            this.push(this.buildMessage('error', content), timeout);
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
