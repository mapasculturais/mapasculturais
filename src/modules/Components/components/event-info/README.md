# Componente `<event-info>`

## Descrição
 O componente event-info é responsável por exibir e editar informações de acessibilidade e outras informações adicionais sobre um evento. Ele utiliza uma combinação de propriedades, métodos e a reatividade do Vue.js para gerenciar e exibir os dados.

# Propriedades

Entity:
- Tipo: Entity
- Obrigatório: Sim
- Descrição: Representa a entidade do evento contendo todas as informações necessárias.

Editable:
- Tipo: Boolean
- Obrigatório: Não
- Valor Padrão: false
- Descrição: Define se as informações do evento podem ser editadas.

Classes:
- Tipo: [String, Array, Object]
- Obrigatório: Não
- Descrição: Classes CSS adicionais a serem aplicadas ao componente.

## Métodos
AccessibilityResources:
- Descrição: Método que retorna uma lista de recursos de acessibilidade física do evento, separados por ponto e vírgula.
- Retorna: Array de recursos de acessibilidade física.

# Importando o componente
```PHP
<?php 
$this->import('event-info');
?>
```

# Exemplo de Uso 
```HTML

Para utilizar o componente event-info, é necessário incluir o template no arquivo correspondente e fornecer os dados do evento através das propriedades do componente.

<!-- utilizaçao básica -->
<event-info :entity="eventEntity" :editable="isEditable" :classes="customClasses"><event-info>

No exemplo acima, eventEntity é um objeto contendo os dados do evento, isEditable é um booleano que define se as informações podem ser editadas, e customClasses são classes CSS adicionais para estilização.

```