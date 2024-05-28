# Componente `<entity-table>`
Tabela de listagem de entidades

### Eventos
- **clear-filters** - disparado ao clicar no botão `limpar filtros`
  
## Propriedades
- *Entity **entity*** - Entidade
- *Object/String **query*** - query de busca das entidades
- *String **select*** - select da query
- *String **endpoint*** - endpoint onde a query será executada
- *Number **limit*** - limite de itens carregados por vez
- *Number **watch-debounce*** 
- *Array **headers*** - colunas da tabela
- *Array/String **required*** - colunas obrigatórias da tabela (não podem ser ocultadas nos filtros)
- *Array/String **visible*** - colunas exibidas na tabela por padrão (podem ser ocultadas nos filtros)

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