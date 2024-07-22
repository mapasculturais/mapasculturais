# Componente `<create-space>`

O componente `create-space` é utilizado para criar um novo espaço com informações básicas e de forma rápida. Ele permite a criação de espaços tanto como rascunhos quanto como publicações.

## Propriedades

- **editable** (opcional): Define se o formulário de criação é editável. O valor padrão é `true`.

## Eventos

- **create**: Emitido após a criação do espaço, passando a entidade criada como argumento.

## Slots
- **default** `{modal}`: Conteúdo padrão da modal, mostrado quando a entidade é nula ou quando já existe uma entidade.
- **button** `{modal}`: Conteúdo para o botão da modal.
- **actions** `{modal}`: Botões de ação na modal, variando conforme o estado da entidade.

### Importando componente
```php
<?php
$this->import('create-space'); 
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<create-space :editable="true" @create="onSpaceCreated"></create-space>
```