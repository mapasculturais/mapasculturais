<?php
/**
 * Config do módulo CompliantSuggestion (denúncia e contato/sugestão).
 * Este arquivo é carregado por src/conf/config.php (CONFIG_PATH = config/ na raiz do projeto).
 * A chave no array DEVE ser exatamente 'module.CompliantSuggestion' (mesmo nome da pasta do módulo).
 *
 * Valores podem ser definidos via variáveis de ambiente (env) no próprio config.
 *
 * Denúncia:
 * - complaint.to: lista no To; se preenchida = lista + saasSuperAdmins; vazia = todos os admins (atual).
 * - complaint.bcc: lista no BCC; se preenchida = só essa lista; vazia = sem BCC (atual).
 *
 * Contato/Sugestão:
 * - suggestion.to: lista no To; se preenchida = lista + responsável (se válido); vazia = só responsável ou saasSuperAdmin.
 * - suggestion.bcc: 'off' ou false = BCC desligado; vazio/null = atual (todos admins); lista = e-mails separados por vírgula.
 */
return [
    'module.CompliantSuggestion' => [
        'compliant' => env('COMPLIANT_ENABLED', true),
        'suggestion' => env('SUGGESTION_ENABLED', true),
        'complaint.to'   => env('COMPLAINT_TO', ''),
        'complaint.bcc'  => env('COMPLAINT_BCC', ''),
        'suggestion.to'  => env('SUGGESTION_TO', ''),
        'suggestion.bcc' => env('SUGGESTION_BCC'),
    ],
];
