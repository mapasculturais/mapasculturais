# Componente `<mc-currency-input>`
O componente `mc-currency-input` é um campo de entrada de texto especializado para a inserção de valores monetários, formatando-os de acordo com as configurações de moeda e localidade especificadas.

### Propriedades
- *ModelValue **String, Number***: Valor do modelo vinculado ao campo de entrada.
- *Options **Object***: Opções adicionais para configurar o comportamento do campo de entrada.

### Eventos
- **change**: Emitido quando o valor do campo de entrada é alterado.
- **input**: Emitido quando há entrada de dados no campo.
- **keydown**: Emitido quando uma tecla é pressionada no campo.
- **keyup**: Emitido quando uma tecla é liberada no campo.
- **focus**: Emitido quando o campo recebe foco.
- **blur**: Emitido quando o campo perde o foco.
- **update**: Emitido quando o valor do modelo é atualizado.

### Importando componente
```PHP
<?php 
$this->import('mc-currency-input');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-currency-input v-model="amount"></mc-currency-input>

<!-- Com Opções Personalizadas -->
<mc-currency-input v-model="amount" :options="{ currency: 'USD', locale: 'en-US' }"></mc-currency-input>

<!-- Capturando Eventos-->
<mc-currency-input 
    v-model="amount" 
    @change="handleChange" 
    @input="handleInput"
    @keydown="handleKeydown"
    @keyup="handleKeyup"
    @focus="handleFocus"
    @blur="handleBlur">
</mc-currency-input>
```
