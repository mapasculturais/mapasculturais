# Componente `<mc-container>`
O componente `mc-container` serve como um contêiner genérico para encapsular conteúdo. Ele utiliza um slot padrão para permitir a inserção de conteúdo personalizado.

## Slots
- **default**: Slot padrão para o conteúdo do contêiner.

### Importando componente
```PHP
<?php 
$this->import('mc-container');
?>
```
### Exemplos de uso
```HTML
<!-- utilização básica -->
 <mc-container>
    <div>Conteúdo dentro do contêiner</div>
</mc-container>

<!-- Utilizando Conteúdo Personalizado -->
 <mc-container>
    <template #default>
        <p>Este é um parágrafo dentro do contêiner.</p>
        <button>Um botão dentro do contêiner</button>
    </template>
</mc-container>
```