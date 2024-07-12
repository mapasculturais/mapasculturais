# Componente `<event-info>`
O componente `event-info ` é utilizado para exibir informações detalhadas de um evento, incluindo recursos de acessibilidade. Este componente também permite a edição dessas informações se a propriedade editable for definida como verdadeira.

### Eventos
- **update-accessibility** - Disparado quando um campo de acessibilidade é alterado. O evento fornece um objeto com o campo alterado e seu novo valor.

### Propriedades
- *Entity **entity*** - Entidade que representa o evento
- *Boolean **editable*** - Define se as informações do evento podem ser editadas
- *String **classes*** = '' - Classes CSS adicionais para estilização do componente

### Importando componente
```PHP
<?php 
$this->import('event-info');
?>
```
### Exemplos de uso
```HTML
<!-- Utilização básica -->
<event-info :entity="event"></event-info>

<!-- Utilização com edição habilitada -->
<event-info :entity="event" :editable="true"></event-info>

<!-- Utilização com classes adicionais -->
<event-info :entity="event" :classes="['custom-class']"></event-info>
```