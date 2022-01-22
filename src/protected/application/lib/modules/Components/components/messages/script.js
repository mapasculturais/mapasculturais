const useMessages = Pinia.defineStore('messages', {
    state: () => {
        return {
            messages: []
        }
    },

    actions: {
        push(message, timeout) {
            const messages = this.messages;
            messages.push(message);
            
            setTimeout(() => {
                let index = messages.indexOf(message);
                messages.splice(index - 1, 1);
            }, timeout || 1000);
        },
        
        alert(text, timeout) {
            const type = 'alert';
            const message = {type, text};
            this.push(message);
        },

        error(text, timeout) {
            const type = 'error';
            const message = {type, text};
            this.push(message, timeout);
        },
    }
});
window.messages = useMessages();
app.component('messages', {
    setup() {
        const store = useMessages();
        return {store};
    },
    template: $TEMPLATES['messages']
});
