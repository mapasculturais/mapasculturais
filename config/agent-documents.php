<?php

/*
Exige documento no agente conforme o tipo (issue #32):

- Tipo 1 (Individual, pessoa física ou MEI): CPF obrigatório;
- Tipo 2 (Coletivo, pessoa jurídica): CNPJ obrigatório.

A exigência vale em todo salvamento (criação e edição) e é aplicada junto ao
funil de completude de perfil existente (app.redirect_profile_validate).
Desativado (false, padrão) mantém o comportamento atual: campos opcionais,
com o formato validado quando preenchidos.
*/
return [
    'agents.requiredDocumentsByType' => env('AGENTS_REQUIRED_DOCUMENTS_BY_TYPE', false),
];
