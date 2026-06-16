<?php
use MapasCulturais\i;

// Textos i18n do componente opportunity-appeal-phase-notify-config.
// São consumidos via Utils.getTexts('opportunity-appeal-phase-notify-config')
// no setup() do script.js e expostos como this.text(key).
return [
    // Cabeçalho do accordion pai
    'Notificações por e-mail' => i::__('Notificações por e-mail'),
    'Tipo' => i::__('Tipo'),
    'Configuração de comunicação' => i::__('Configuração de comunicação'),
    'de' => i::__('de'),
    'ativas' => i::__('ativas'),

    // Rótulos dos 5 fluxos (labels dos cards)
    'Novo recurso aberto' => i::__('Novo recurso aberto'),
    'Recurso pronto para avaliar' => i::__('Recurso pronto para avaliar'),
    'Recurso negado' => i::__('Recurso negado'),
    'Recurso aceito' => i::__('Recurso aceito'),
    'Recurso invalidado' => i::__('Recurso invalidado'),

    // Rótulos de destinatários (subtítulo de cada card)
    'Proponente e Gestores' => i::__('Proponente e Gestores'),
    'Avaliadores' => i::__('Avaliadores'),
    'Proponente' => i::__('Proponente'),

    // Botões e ações dos cards
    'Ativar notificação' => i::__('Ativar notificação'),
    'Personalizar' => i::__('Personalizar'),
    'Personalizar notificação' => i::__('Personalizar notificação'),

    // Selo "Personalizado"
    'Personalizado' => i::__('Personalizado'),
    'Texto padrão' => i::__('Texto padrão'),

    // Alerta de onboarding
    'Nenhuma notificação ativa' => i::__('Nenhuma notificação ativa'),
    'Nenhuma notificação está ativa. Os participantes não receberão avisos por e-mail sobre recursos. Ative pelo menos uma notificação para que os envios passem a ocorrer.' => i::__('Nenhuma notificação está ativa. Os participantes não receberão avisos por e-mail sobre recursos. Ative pelo menos uma notificação para que os envios passem a ocorrer.'),

    // Modal de personalização
    'Assunto' => i::__('Assunto'),
    'Assunto do e-mail' => i::__('Assunto do e-mail'),
    'Mensagem' => i::__('Mensagem'),
    'Mensagem do e-mail e da notificação' => i::__('Mensagem do e-mail e da notificação'),
    'Variáveis disponíveis' => i::__('Variáveis disponíveis'),
    'Clique em uma variável para inseri-la no campo focado' => i::__('Clique em uma variável para inseri-la no campo focado'),
    'Inserir variável' => i::__('Inserir variável'),
    'Restaurar padrão' => i::__('Restaurar padrão'),
    'Salvar' => i::__('Salvar'),
    'Cancelar' => i::__('Cancelar'),
    'Ativo' => i::__('Ativo'),
    'Inativo' => i::__('Inativo'),
    'Estado atual' => i::__('Estado atual'),

    // Mensagens de toast / feedback
    'Notificação ativada' => i::__('Notificação ativada'),
    'Notificação desativada' => i::__('Notificação desativada'),
    'Configuração de notificação salva' => i::__('Configuração de notificação salva'),
    'Texto restaurado para o padrão' => i::__('Texto restaurado para o padrão'),
    'Erro ao salvar a configuração' => i::__('Erro ao salvar a configuração'),
    'Nenhuma alteração a salvar' => i::__('Nenhuma alteração a salvar'),
    'Não foi possível abrir o formulário' => i::__('Não foi possível abrir o formulário'),
];
