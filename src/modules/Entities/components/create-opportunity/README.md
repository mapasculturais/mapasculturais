# Componente `<create-opportunity>`

O componente `<create-opportunity>` permite a criação de oportunidades associadas a diferentes entidades como projetos, eventos, espaços e agentes. Ele fornece uma interface para preenchimento de informações básicas e a opção de vincular a oportunidade a uma entidade existente.

## Propriedades
- **editable** (`Boolean`): Define se os campos do formulário são editáveis. Padrão: `true`.

## Eventos
- **create**: Disparado quando uma nova oportunidade é criada e salva com sucesso.

### Importando o Componente
```php
<?php 
$this->import('create-opportunity');
?>
```

### Exemplos de uso
```HTML
<!-- Utilização básica -->
<create-opportunity></create-opportunity>

<!-- Utilizando o evento on-create para realizar ações adicionais -->
<create-opportunity @create="handleCreate"></create-opportunity>

<!-- Customização com slots -->
<create-opportunity #default="props">
    <div v-if="props.modalTitle">Título da Modal: {{props.modalTitle}}</div>
</create-opportunity>
```
