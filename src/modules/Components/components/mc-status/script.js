app.component('mc-status', {
    template: $TEMPLATES['mc-status'],

    props: {
        statusName: {
            type: [Object, String],
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

            const normalize = val => String(val || '').toLowerCase();

            const classesMap = {
                'mc-status--draft': [
                    'rascunho', 'iniciado'
                ],

                'mc-status--success': [
                    // já existentes
                    'selecionado', 'selecionada', 'selecionados', 'selecionadas',
                    'válido', 'válida', 'válidos', 'válidas',
                    'aceito', 'aceita', 'aceitos', 'aceitas',
                    'disponível', 'disponíveis',
                    'pago', 'paga', 'pagos', 'pagas',
                    'enviado', 'enviada', 'enviados', 'enviadas',
                    'habilitado', 'habilitada', 'habilitados', 'habilitadas',
                    'válida','Válida',

                    // adicionais comuns de sucesso
                    'aprovado', 'aprovada', 'aprovados', 'aprovadas',
                    'concluído', 'concluída', 'concluídos', 'concluídas',
                    'completo', 'completa', 'completos', 'completas',
                    'confirmado', 'confirmada', 'confirmados', 'confirmadas',
                    'efetivado', 'efetivada', 'efetivados', 'efetivadas',
                    'realizado', 'realizada', 'realizados', 'realizadas',
                    'liberado', 'liberada', 'liberados', 'liberadas',
                    'finalizado', 'finalizada', 'finalizados', 'finalizadas',
                    'atendido', 'atendida', 'atendidos', 'atendidas',
                    'apto', 'apta', 'aptos', 'aptas',
                    'vencedor', 'vencedora', 'vencedores', 'vencedoras',
                    'classificado', 'classificada', 'classificados', 'classificadas',
                    'emitido', 'emitida', 'emitidos', 'emitidas'
                ],

                'mc-status--invalid': [
                    'inválido', 'inválida', 'inválidos', 'inválidas',
                ],

                'mc-status--error': [
                    // já existentes
                    'não selecionado', 'não selecionada', 'não selecionados', 'não selecionadas',
                    'inabilitado', 'inabilitada', 'inabilitados', 'inabilitadas',
                    'não aceito', 'não aceita', 'não aceitos', 'não aceitas',
                    'falha', 'falhas',
                    'não enviado', 'não enviada', 'não enviados', 'não enviadas',

                    // adicionais de erro / falha
                    'erro', 'erros',
                    'rejeitado', 'rejeitada', 'rejeitados', 'rejeitadas',
                    'negado', 'negada', 'negados', 'negadas',
                    'cancelado', 'cancelada', 'cancelados', 'canceladas',
                    'expirado', 'expirada', 'expirados', 'expiradas',
                    'recusado', 'recusada', 'recusados', 'recusadas',
                    'incompleto', 'incompleta', 'incompletos', 'incompletas',
                    'inativo', 'inativa', 'inativos', 'inativas',
                    'inelegível', 'inelegíveis',
                    'inapto', 'inapta', 'inaptos', 'inaptas',
                    'bloqueado', 'bloqueada', 'bloqueados', 'bloqueadas',
                    'inconsistente', 'inconsistentes',
                    'pendência', 'pendências',
                    'desclassificado', 'desclassificada', 'desclassificados', 'desclassificadas',
                    'devolvido', 'devolvida', 'devolvidos', 'devolvidas',
                    'falhou', 'fracassado', 'fracassada', 'fracassados', 'fracassadas',
                    'retido', 'retida', 'retidos', 'retidas',
                    'não conformidade', 'não conformidades'
                ],

                'mc-status--warning': [
                    'suplente', 'exportado'
                ],

                'mc-status--primary': [
                    'aguardando avaliação', 'concluído',
                    'em análise', 'enviado', 'enviada'
                ],

                'mc-status--evaluation-pending': [
                    'avaliações pendentes', 'avaliação pendente'
                ],

                'mc-status--evaluation-started': [
                    'avaliações iniciadas', 'avaliação iniciada'
                ],

                'mc-status--evaluation-completed': [
                    'avaliações concluídas', 'avaliação concluída'
                ],

                'mc-status--evaluation-sent': [
                    'avaliações enviadas', 'avaliação enviada'
                ]   
            };

            let matched = false;

            if (typeof this.statusName === 'string') {
                const status = normalize(this.statusName);
                for (const [className, list] of Object.entries(classesMap)) {
                    if (list.includes(status)) {
                        classes.push(className);
                        matched = true;
                        break;
                    }
                }
            } else if (typeof this.statusName === 'object' && this.statusName !== null) {
                const statusKey = this.statusName.value || this.statusName.key;

                if(statusKey == 10) {
                    classes.push('mc-status--success');
                    matched = true;
                } else if(statusKey == 8) {
                    console.log('staus =>', this.statusName);
                    classes.push('mc-status--warning');
                    matched = true;
                } else if(statusKey == 3) {
                    classes.push('mc-status--error');
                    matched = true;
                } else if(statusKey == 2) {
                    classes.push('mc-status--invalid');
                    matched = true;
                } else if(statusKey == 0) {
                    classes.push('mc-status--draft');
                    matched = true;
                }
            }

            if (!matched) {
                classes.push('mc-status--default');
            }

            return classes;
        }
    },
});