# Componente `<create-occurrence>`

O componente `<create-occurrence>` permite a criação de uma nova ocorrência de evento, incluindo a definição de frequência, datas, horários, espaço vinculado e informações sobre a entrada. Ele também fornece uma visualização em etapas para facilitar a navegação e o preenchimento das informações necessárias.

### Eventos
- **create** - Emitido após a criação bem-sucedida de uma nova ocorrência, passando a nova ocorrência como argumento.

## Propriedades
- *Entity **entity*** - Entidade
- *Entity **occurrence*** - Instância da classe Entity representando uma ocorrência existente, caso esteja sendo editada. Caso contrário, uma nova instância será criada.

### Importando componente
```PHP
<?php 
$this->import('create-occurrence');
?>
```

### Exemplos de uso
```HTML
<create-occurrence
    :entity="evento"
    :occurrence="ocorrenciaExistente"
    @create="handleCreateOccurrence"
></create-occurrence>

```