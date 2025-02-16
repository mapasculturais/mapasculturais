app.component('mc-status', {
    template: $TEMPLATES['mc-status'],

    props: {
        statusName: {
            type: String,
            required: true,
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-status')
        return { text, hasSlot }
    },

    computed: {
        statusClass() {
            switch (this.statusName.toLowerCase()) {
                case 'rascunho':
                case 'iniciado':
                    return 'mc-status--draft';

                case 'selecionado':
                case 'selecionada':
                case 'válido':
                case 'aceito':
                case 'disponível':
                case 'pago':
                case 'enviado':
                case 'enviada':
                    return 'mc-status--success';

                case 'não selecionado':
                case 'não selecionada':
                case 'não aceito':
                case 'inválido':
                case 'inválida':
                case 'falha':
                case 'não enviado':
                case 'não enviada':
                    return 'mc-status--error';

                case 'suplente':
                case 'exportado':
                    return 'mc-status--warning';

                case 'aguardando avaliação':
                case 'concluído':
                case 'em análise':
                    return 'mc-status--primary';

                case 'avaliação pendente':
                case 'avaliação pendente':
                    return 'mc-status--evaluation-pending';

                case 'avaliações iniciadas':
                case 'avaliação iniciada':
                    return 'mc-status--evaluation-started';

                case 'avaliações concluídas':
                case 'avaliação concluída':
                    return 'mc-status--evaluation-completed';

                case 'avaliações enviadas':
                case 'avaliação enviada':
                    return 'mc-status--evaluation-sent';

                case 'pendente':
                default:
                    return 'mc-status--default';
            }
        }
    },
});