<?php
use MapasCulturais\i;

return [
    'mailer.templates' => [
        'welcome' => [
            'title' => i::__("Bem-vindo(a) ao Mapa da Cultura"),
            'template' => 'welcome.html'
        ],
        'last_login' => [
            'title' => i::__("Acesse o Mapa da Cultura"),
            'template' => 'last_login.html'
        ],
        'new' => [
            'title' => i::__("Novo registro"),
            'template' => 'new.html'
        ],
        'update_required' => [
            'title' => i::__("Acesse o Mapa da Cultura"),
            'template' => 'update_required.html'
        ],
        'compliant' => [
            'title' => i::__("Denúncia - Mapa da Cultura"),
            'template' => 'compliant.html'
        ],
        'suggestion' => [
            'title' => i::__("Mensagem - Mapa da Cultura"),
            'template' => 'suggestion.html'
        ],
        'seal_toexpire' => [
            'title' => i::__("Selo Certificador Expirando"),
            'template' => 'seal_toexpire.html'
        ],
        'seal_expired' => [
            'title' => i::__("Selo Certificador Expirado"),
            'template' => 'seal_expired.html'
        ],
        'opportunity_claim' => [
            'title' => i::__("Solicitação de Recurso de Oportunidade"),
            'template' => 'opportunity_claim.html'
        ],
        'request_relation' => [
            'title' => i::__("Solicitação de requisição"),
            'template' => 'request_relation.html'
        ],
        'start_registration' => [
            'title' => i::__("Inscrição iniciada"),
            'template' => 'start_registration.html'
        ],
        'start_data_collection_phase' => [
            'title' => i::__("Sua inscrição avaçou de fase"),
            'template' => 'start_data_collection_phase.html'
        ],
        'export_spreadsheet' => [
            'title' => i::__("Planilha disponível"),
            'template' => 'export_spreadsheet.html'
        ],
        'export_spreadsheet_error' => [
            'title' => i::__("Houve um erro com o arquivo"),
            'template' => 'export_spreadsheet_error.html'
        ],
        'send_registration' => [
            'title' => i::__("Inscrição enviada"),
            'template' => 'send_registration.html'
        ],
        'claim_form' => [
            'title' => i::__("Solicitação de recurso"),
            'template' => 'claim_form.html'
        ],
        'claim_certificate' => [
            'title' => i::__("Certificado de solicitação de recurso"),
            'template' => 'claim_certificate.html'
        ],

    ]
];