<?php
use MapasCulturais\i;

/**
 * Manter os espaços em branco presentes 
 * nas traduções da descrição automática
 */

return [
    'gratuito' => i::__('Gratuito'),

    /* descrição automática */
    'meses' => [i::__('Janeiro'), i::__('Fevereiro'), i::__('Março'), i::__('Abril'), i::__('Maio'), i::__('Junho'), i::__('Julho'), i::__('Agosto'), i::__('Setembro'), i::__('Outubro'), i::__('Novembro'), i::__('Dezembro')],
    'dias' => [i::__('domingo'), i::__('segunda'), i::__('terça'), i::__('quarta'), i::__('quinta'), i::__('sexta'), i::__('sábado')],
    'uma vez' => 'Dia {dia} de {mes} de {ano}',
    'diariamente' => 'Diariamente',
    'todo' => 'Todo ',
    'toda' => 'Toda ',
    'meses diferentes' => ' de {diaIni} de {mesIni} a {diaFim} de {mesFim}',
    'meses iguais' => ' de {diaIni} a {diaFim} de {mesFim} de {anoFim}',
    'anos diferentes' => ' de {diaIni} de {mesIni} de {anoIni} a {diaFim} de {mesFim} de {anoFim}',
    'e' => ' e ',
    'à' => ' à ',
    'às' => ' às ',
    'das' => ' das ',

    /* validações */
    'Gratuito' => 'Gratuito',
    'corrija os erros' => 'Por favor, corrija os erros indicados antes de criar a ocorrência.',
];