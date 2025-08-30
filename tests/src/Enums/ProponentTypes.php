<?php 
namespace Tests\Enums;

enum ProponentTypes: string
{
    case DEFAULT = 'default';
    case PESSOA_FISICA = 'Pessoa Física';
    case MEI = 'MEI';
    case COLETIVO = 'Coletivo';
    case PESSOA_JURIDICA = 'Pessoa Jurídica';
}