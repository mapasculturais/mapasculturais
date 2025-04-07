# Componente `<space-card>`
O componente `space-card` é projetado para exibir informações sobre um espaço em um formato de cartão estilizado.

## Propriedades
- *Name **String*** - O nome do espaço a ser exibido.

## Slots
- **profile**: Slot para renderizar a seção de perfil do cartão, geralmente contendo um ícone ou avatar.
- **title**: Slot para o título do cartão, exibindo o nome e descrição do espaço.
- **content**: Slot para conteúdo adicional a ser adicionado ao cartão.

### Importando componente
```PHP
<?php 
$this->import('space-card');
?>
```
### Exemplos de uso
```HTML
<space-card name="Nome do Espaço"></space-card>
```