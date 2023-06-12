#mc- Componente `<mc-entities>`
Serve para implementar listagens de entidades baseadas em consultas na API.

## Propriedades
- *String **type*** - Tipo da entidade que será listada (`agent`, `space`, `event`, `project`, `opportunity` etc)
- *String **select** = undefined* - dados que devem ser buscados. ex: `"id,name,files.avatar,terms"`
- *Object **query** = {}* - objeto para definir a consulta que será feita no banco de dados. ex: `{name: 'ilike(%fulano%)', createTimestamp: 'GT(2022-09-27)'}`
- *Array **ids** = 'undefined'* - lista de ids das entidades que devem ser buscadas na API. Comporá o objeto query. ex: `[33,44]` adicinará na query: `{id: 'IN(33,44)'}`
- *Number **limit** = undefined* - número máximo de entidades por página. Se houver mais entidades do que o número definido aqui, um botão de 'carregar mais' será exibido.
- *String **order** = 'id ASC'* - ordenação do resultado. exs: `name ASC`, `name DESC`, `createTimestamp ASC`, `updateTimestamp DESC`
- *Boolean **watchQuery** = false* - o componente deve observar modificações na query e se auto atualizar?
- *Number **watchDebounce** = 500* (em milisegundos) - adiamento da nova consulta após a modificação. Se diversas modificações forem feitas num intervalo menor o definido nesta propriedade, haverá somente uma consulta à API.
- *String **endpoint** = 'find'* - nome do endpoint que deve ser consultado. para o `type = 'agent'`, o 'find' se tornará `/api/agent/find`.
- *Function **rawProcessor** = undefined* - Se não deseja instanciar objetos do tipo Entity, pode passar um processador no resultado da API.
- *String **name** = undefined* - Esta propriedade serve para nomear a lista de objetos da instância desse coponente. Esta lista poderá ser acessada de outro componente através desse nome. É útil para possibilitar a manipulação da lista a partir de outro componente (remover ou adicionar itens)
- *String **scope** = undefined* - Utilizado para isolar o escopo das instâncias da classe Entity


## Slots
- **header** `{Entity[]* entities, Object query, Function loadMore, Function refresh}` - espaço antes da listagem, ideal para posicionar um formulário de busca ou filtro. Vazio por padrão.
- **loading** `{Entity[]* entities}` _(renderizado somente enquanto a consulta na API está sendo feita)_ - espaço para personalizar a mensagem de _loading_. Por padrão é exibido um componente `<mc-loading>`. 
- **empty** - Exibido no lugar da lista quando nenhum resultado foi encontrado. Por padrão exibe a mensagem 'Nenhuma entidade encontrada'.
- **default** `{Entity[]* entities, Object query, Function loadMore, Function refresh}` _(não renderizado enquanto a consulta na API está sendo feita)_ - Lugar para implementar a listagem do resultado.

## entities
A variável `entities` é um array de instâncias da classe Entity (ou do objeto retornado pelo rawProcessor) e possui algumas propriedades extas:
- *Object **query** = {}* - objeto que representa a query feita na api. Ex: `{'@select': 'id,name', '@order': 'createTimestamp ASC', '@keyword': 'busca'}`
- *Object **metadata** = {count, page, limit, numPages, keyword, order}* - metadados da requisição/resultado da consulta à API.
- *Boolean **loading** = false* - indica se a consulta ao banco de dados está sendo feita.
- *Boolean **loadingMore** = false* - indica se a consulta ao banco de dados está sendo feita para obter mais resultados, quando há um limite definido.
- *Function **refresh(debounce = 0)*** - refaz a consulta ao banco de dados.
- *Function **loadMore(debounce)*** - carrega mais resultados em caso de utilização de limite

Utilizando as propriedades acima, para fazer uma busca o procedimento é o seguinte:
```Javascript
entities.query['@keyword'] = 'palavra chave';
entities.query['@order'] = 'name ASC'; // ordem alfabética
entities.refresh();
```

## Importando o componente
```PHP
<?php 
$this->import('entities');
?>
```
## Exemplos de uso
```HTML
<!-- obtendo entidades pelos ids -->
<mc-entities type="agent" select="id,name" :ids="[33,44,55,66]" #default='{entities}'>
    <article v-for="entity in entities" :key="entity.__objectId">
        <strong>{{entity.id}}</strong> - {{entity.name}}
    </article>
</mc-entities>

<!-- definindo paginação. somente 10 elementos por vez e botào carregar mais -->
<mc-entities type="agent" select="id,name" :limit='10' #default='{entities}'>
    <article v-for="entity in entities" :key="entity.__objectId">
        <strong>{{entity.id}}</strong> - {{entity.name}}
    </article>
</mc-entities>

<!-- definindo ordenação -->
<mc-entities type="agent" select="id,name" order="name ASC" :limit='10'>
    <template #default='{entities}'>
        <article v-for="entity in entities" :key="entity.__objectId">
            <strong>{{entity.id}}</strong> - {{entity.name}}
        </article>
    </template>
</mc-entities>

<!-- busca por palavra-chave -->
<mc-entities type="agent" select="id,name" order="name ASC" :limit='10'>
    <template #header='{query, refresh}'>
        <input v-model="query['@keyword']" placeholder="palavra-chave">
        <button @click="refresh()">buscar</button>
    </template>
    <template #default='{entities}'>
        <article v-for="entity in entities" :key="entity.__objectId">
            <strong>{{entity.id}}</strong> - {{entity.name}}
        </article>
    </template>
</mc-entities>

<!-- busca por palavra-chave com ordenação-->
<mc-entities type="agent" select="id,name" order="name ASC" :limit='10'>
    <template #header='{query, refresh}'>
        <input v-model="query['@keyword']" placeholder="palavra-chave">
        <select v-model="query['@order']">
            <option value="name ASC">alfabética</option>
            <option value="createTimestamp ASC">mais antigas primeiro</option>
            <option value="createTimestamp DESC">mais recentes primeiro</option>
        </select>
        <button @click="refresh()">buscar</button>
    </template>
    <template #default='{entities}'>
        <article v-for="entity in entities" :key="entity.__objectId">
            <strong>{{entity.id}}</strong> - {{entity.name}}
        </article>
    </template>
</mc-entities>

<!-- busca por palavra-chave com ordenação e recarregamento automático-->
<mc-entities type="agent" select="id,name" order="name ASC" :limit='10' watch-query>
    <template #header='{query, refresh}'>
        <input v-model="query['@keyword']" placeholder="palavra-chave">
        <select v-model="query['@order']">
            <option value="name ASC">alfabética</option>
            <option value="createTimestamp ASC">mais antigas primeiro</option>
            <option value="createTimestamp DESC">mais recentes primeiro</option>
        </select>
    </template>
    <template #default='{entities}'>
        <article v-for="entity in entities" :key="entity.__objectId">
            <strong>{{entity.id}}</strong> - {{entity.name}}
        </article>
    </template>
</mc-entities>
```