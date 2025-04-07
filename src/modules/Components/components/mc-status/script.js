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
            let classes = ['mc-status'];

            switch (this.statusName.toLowerCase()) {
                case 'rascunho':
                case 'iniciado':
                    classes.push('mc-status--draft');
                    break;
                
                case 'selecionado':
                case 'selecionada':
                case 'válido':
                case 'aceito':
                case 'disponível':
                case 'pago':
                case 'enviado':
                case 'enviada':
                case 'habilitado':
                    classes.push('mc-status--success');
                    break;
                
                case 'não selecionado':
                case 'não selecionada':
                case 'inabilitado':
                case 'não aceito':
                case 'inválido':
                case 'inválida':
                case 'falha':
                case 'não enviado':
                case 'não enviada':
                    classes.push('mc-status--error');
                    break;
                
                case 'suplente':
                case 'exportado':
                    classes.push('mc-status--warnign');
                    break;
                
                case 'aguardando avaliação':
                case 'concluído':
                case 'em análise':
                case 'enviado':
                case 'enviada':
                    classes.push('mc-status--primary');
                    break;
                case 'avaliações pendentes':
                case 'avaliação pendente':
                        classes.push('mc-status--evaluation-pending');
                        break;
                case 'avaliações iniciadas':
                case 'avaliação iniciada':
                    classes.push('mc-status--evaluation-started');
                    break;
                case 'avaliações concluídas':
                case 'avaliação concluída':
                    classes.push('mc-status--evaluation-completed');
                    break;
                case 'avaliações enviadas':
                case 'avaliação enviada':
                    classes.push('mc-status--evaluation-sent');
                    break;
                case 'pendente':
                case 'enviado':
                default:
                    classes.push('mc-status--default');
                    break;
            }

            return classes;
        }
    },
});