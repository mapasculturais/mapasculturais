<?php
use MapasCulturais\i;

return [
    'mailer.templates' => [
        'welcome' => [
            'title' => i::__("Bem-vindo(a) ao Mapas Culturais"),
            'template' => 'welcome.html'
        ],
        'last_login' => [
            'title' => i::__("Acesse a Mapas Culturais"),
            'template' => 'last_login.html'
        ],
        'new' => [
            'title' => i::__("Novo registro"),
            'template' => 'new.html'
        ],
        'update_required' => [
            'title' => i::__("Acesse a Mapas Culturais"),
            'template' => 'update_required.html'
        ],
        'compliant' => [
            'title' => i::__("Denúncia - Mapas Culturais"),
            'template' => 'compliant.html'
        ],
        'suggestion' => [
            'title' => i::__("Mensagem - Mapas Culturais"),
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