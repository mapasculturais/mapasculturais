# Configuração de Filtros

Corrige a configuração da chave `options` de um filtro na busca, sem que o filtro precise estar relacionado a Metadata, EntityType ou Term.
Ex:
```
'label' => i::__('Selos'),
'placeholder' => i::__('Selecione os Selos'),
'fieldType' => 'checklist',
'type' => 'custom',
'isArray' => true,
'isInline' => false,
'filter' => [
    'param' => '@seals',
    'value' => '{val}'
],
'options' => [
    ['value' => '1', 'label' => 'Selo 1'],
    ['value' => '2', 'label' => 'Selo 2'],
    ['value' => '3', 'label' => 'Selo 3'],
]
```
