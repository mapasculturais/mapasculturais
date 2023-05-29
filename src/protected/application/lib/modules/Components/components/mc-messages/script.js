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
            
            setTimeout(() => {
                const index = this.messages.indexOf(message);
                this.messages.splice(index, 1);
            }, timeout || 3000);
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
