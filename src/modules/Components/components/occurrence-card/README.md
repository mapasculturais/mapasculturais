# Componente `<occurrence-card>`
O componente `occurrence-card` exibe informações detalhadas sobre uma ocorrência de evento, incluindo detalhes do evento relacionado e localização, opções de classificação etária, preço, tags associadas, linguagens e selos de certificação.

## Propriedades
- *Object **occurrence*** - Objeto retornado pelo endpoint /api/event/occurrences
- *Boolean **hideSpace*** - Indica se o espaço da ocorrência deve ser ocultado na exibição.

## Slots
- **labels**: Slot para personalização de elementos adicionais no cabeçalho do cartão de ocorrência.

### Importando componente
```PHP
<?php 
$this->import('occurrence-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<occurrence-card :occurrence="occurrence"></occurrence-card>

<!-- Ocultando Informações do Espaço -->
 <occurrence-card :occurrence="occurrence" :hide-space="true"></occurrence-card>
```