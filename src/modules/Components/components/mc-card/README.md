# Componente `<mc-card>`
O componente mc-card` é um contêiner flexível e estilizado que pode ser usado para agrupar diferentes tipos de conteúdo, como texto, imagens e outros componentes. Ele utiliza slots para permitir a inserção de conteúdo personalizado em várias partes do cartão.

### Propriedades
- *Tag **String*** : Define a tag HTML do elemento raiz do componente. Pode ser alterado para qualquer tag HTML válida.
- *Classes **String*** : Classes CSS adicionais para estilizar o componente.

## Slots
- **default**: Slot padrão para o conteúdo principal do cartão.
- **title**: Slot opcional para o título do cartão.
- **content**: Slot adicional para o conteúdo do cartão.

### Importando componente
```PHP
<?php 
$this->import('mc-card');
?>
```
### Exemplos de uso
```HTML

<mc-card>
    <div>Conteúdo principal do cartão</div>
</mc-card>

<!-- Utilizando o Slot de Título -->
 <mc-card>
    <template #title>
        <h2>Título do Cartão</h2>
    </template>
    <div>Conteúdo principal do cartão</div>
</mc-card>

<!-- Utilizando Classes Personalizadas -->
 <mc-card :classes="['custom-class']">
    <div>Conteúdo principal do cartão</div>
</mc-card>

```

