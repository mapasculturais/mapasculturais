# Componente `<entity-table>`
Tabela de listagem de entidades

### Eventos
- **clear-filters** - disparado ao clicar no botão `limpar filtros`
  
## Propriedades
- *String* **type** - Tipo da entidade a ser listada
- *String* **controller** - Controller a ser utilizado na busca
- *Number* **limit** - Limite de resultados listados
- *String* **order** - Ordem de listagem dos resultados
- *Object/String* **query** - Query para consulta
- *Number* **watchDebounce** - Frequência de atualização das consultas
- *Array* **headers** - Cabeçalhos da tabela
- *String/Array* **required** - Cabeçalhos obrigatórios da tabela
- *String/Array* **visible** - Cabeçalhos visiveis por padrão na tabela
- *String* **endpoint** - Endpoint onde será realizada a busca
- *Array* **sortOptions** - Opções de ordenação da listagem na tabela
- *String* **identifier** - Identificador da tabela
- *String* **select** - Colunas a serem selecionados na busca
- *Boolean* **showIndex** - Exibe ou não a seleção de colunas da tabela
- *Boolean* **hideFilters** - Esconde os filtros da tabela
- *Boolean* **hideSort** - Esconde a ordenação da tabela
- *Boolean* **hideActions** - Esconde a parte de ações da tabela
- *Boolean* **hideHeader** - Esconde toda a parte superior da tabela (Actions e Filters)
- *Function* **rawProcessor** - Função para processamento dos dados brutos

## Slots
- **title** - título da tabela
- **actions** - `{entities, filters}` : slot para botões de ação
- **advanced-actions** - `{entities, filters}` : slot para ações avançadas (inicia colapsado)
- **filters** - `{entities, filters}` : slot para campos de filtragem
- **advanced-filters** - `{entities, filters}` : slot para campos de filtragem avançada (inicia colapsado)
- **__generic__** - `{entity}` : slots adicionados na tabela com o slug da coluna
- **default** `{fullname, displayName, compareDisplayName, compareFullname}`: informações dos nomes e comparação com o nome gerado

título
ações / ações avançadas
filtro / filtros avançadosR

### Importando componente
```PHP
<?php 
$this->import('entity-table');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao -->
<entity-table type="payment" :select="select" :query="query" :headers="headers" endpint required="registration,options" visible="registration,paymentDate,amount,status,options">
    <template #title>
        título
    </template>

    <template #actions="{entities}">
        ações
    </template>

    <template #advanced-actions="{entities}">
        ações avançadas
    </template>

    <template #filters="{entities}">
        filtros
    </template>
    
    <template #advanced-filters="{entities}">
        filtros avançados
    </template>

   <template #{header.slug}="{entity}">
        slot em célula pertencente a coluna de nome = header.slug
    </template>
</entity-table>

```