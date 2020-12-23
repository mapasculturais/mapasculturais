<?php

use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\i;

$header = [
    'Número',
    'Data',
    'Hora',
    'Agente',
    'Email público',
    'Email privado',
    'Email de usuário',
    'Telefone público',
    'Telefone 1',
    'Telefone 2'
];

$data = [];
foreach($registrationsDraftList as $r) {

    $agent = $r->owner;

    if (!empty($agent)) {
        $row = [
            $r->number,
            date_format($r->createTimestamp, "d/m/Y"),
            date_format($r->createTimestamp, "H:i:s"),
            $agent->name,
            $agent->emailPublico,
            $agent->emailPrivado,
            $agent->user->email,
            $agent->telefonePublico,
            $agent->telefone1,
            $agent->telefone2
        ];
    } else {
        $row = [
            $r->number,
            str_repeat('', 7)
        ];
    }

    $data[] = $row;

}

$fh = @fopen('php://output', 'w');
fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($fh, $header);

foreach ($data as $d) {
    fputcsv($fh, $d);
}

fclose($fh);