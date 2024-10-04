# Componente `<mc-datepicker>`
Adiciona um campo de input para seleção de data ou horário.

## Propriedades

- **String `fieldType` (obrigatório)** - Define o tipo de entrada, aceitando os valores `date`, `time` ou `datetime`.
- **String `propId`** - Identificador do input, utilizado para associar a entrada ao nome do campo.
- **String `locale`** - Define a localidade para o formato de data e hora, padrão é `pt-BR`.

## Eventos

- **update:modelValue**: Emitido quando a data ou hora é alterada. O valor emitido é o novo valor da data ou hora, dependendo do `fieldType`.

## Importando componente
```PHP
<?php 
$this->import('mc-datepicker');
?>
```

## Exemplo de Uso

<!-- Exemplo de uso para entrada de data -->
<mc-datepicker fieldType="date" @update:modelValue="onDateUpdated"></mc-datepicker>

<!-- Exemplo de uso para entrada de hora -->
<mc-datepicker fieldType="time" @update:modelValue="onTimeUpdated"></mc-datepicker>

<!-- Exemplo de uso para entrada de data e hora -->
<mc-datepicker fieldType="datetime" @update:modelValue="onDateTimeUpdated"></mc-datepicker>

```