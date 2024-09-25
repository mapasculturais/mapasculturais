# Componente `<mc-datepicker>`
Adiciona um campo de input para seleção de data ou horário.

## Propriedades

- **String `fieldType` (obrigatório)** - Define o tipo de entrada, aceitando os valores `date` ou `time`.
- **String `locale`** - Define a localidade para o formato de data e hora, padrão é `pt-BR`.

## Eventos Emitidos

- **`update:modelDate`** - Emite o valor da data selecionada no formato `Date`.
- **`update:modelTime`** - Emite o valor do horário selecionado no formato `{ hours: number, minutes: number, seconds: number }`.

## Métodos

- **`handleBlur(type)`** - Valida a entrada de data ou hora quando o campo perde o foco. Atualiza o valor do campo se a entrada estiver completa.
- **`inputValue(type)`** - Atualiza os valores de `modelDate` ou `modelTime` com base na entrada do usuário.
- **`onDateChange(date)`** - Atualiza o modelo da data e o campo de input ao selecionar uma data no datepicker.
- **`onTimeChange(time)`** - Atualiza o modelo do horário e o campo de input ao selecionar uma hora no datepicker.

## Computed Properties

- **`isDateType`** - Retorna `true` se o tipo de campo for `date`.
- **`isTimeType`** - Retorna `true` se o tipo de campo for `time`.

## Importando componente
```PHP
<?php 
$this->import('mc-datepicker');
?>
```

## Exemplo de Uso

```html
<mc-datepicker 
    v-model:modelDate="selectedDate"
    fieldType="date"
    locale="locale"
    @update:modelDate="validateDate">
</mc-datepicker>

<mc-datepicker
    v-model:modelTime="selectedTime"
    fieldType="time"
    locale="locale"
    @update:modelTime="validateTime">
</mc-datepicker>

```