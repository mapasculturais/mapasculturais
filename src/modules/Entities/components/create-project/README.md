## Componente `<create-project>`

O componente `create-project` é responsável por criar novos projetos. Ele permite que o usuário preencha informações básicas sobre o projeto, como nome, tipo e descrição. O componente fornece uma modal para facilitar a criação de projetos, permitindo ao usuário criar o projeto como rascunho ou publicá-lo diretamente.

### Props
- **editable** (`Boolean`): Define se o projeto é editável. Padrão é `true`.

### Eventos
- **create**: Evento emitido após a criação da entidade.

### Slots
- **default** `{modal}`: Conteúdo padrão da modal, mostrado quando a entidade é nula ou quando já existe uma entidade.
- **button** `{modal}`: Conteúdo para o botão da modal.
- **actions** `{modal}`: Botões de ação na modal, variando conforme o estado da entidade.

### Importando componente
```php
<?php
$this->import('create-project'); 
?>
```

### Exemplo de Uso
```HTML
<!-- utilizaçao básica -->
<create-project @create="handleCreate" :editable="true"></create-project>
```