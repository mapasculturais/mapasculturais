Documentacao da API
===================

Para tornar mais fácil a leitura dos exemplos será utilizada a função getJSON do jQuery.

Action findEdit
---------------

Parâmetros
O Método find aceita os seguintes parâmetros:

* **@select** - usado para selecionar o que será retornado pela api. _ex:( @select: id,name)_
* **@order** - usado para definir em que ordem o resultado será retornado. _ex:( @order: name ASC, id DESC)_
* **@limit** - usado para definir o número máximo de entidades que serão retornadas. _ex:( @limit: 10)_
* **@page** - usado em paginações em conjunto com o @limit. _ex:( @limit:10, @page: 2)_
* **@or** - se usado a api usará o operador lógico OR para criar a query. _ex:( @or:1)_
* **@type** - usado para definir o tipo de documento a ser gerado com o resultado da busca. _ex:( @type: html; ou @type: json; ou @type: xml)_
* **@files** - indica que é para retornar os arquivos anexos. _ex:( @files=(avatar.avatarSmall,header):name,url - retorna o nome e url do thumbnail de tamanho avatarSmall da imagem avatar e a imagem header original)_

Operadores
----------

Para filtrar os resultados o método find aceita os seguintes operadores em qualquer das propriedades e metadados das entidades:

* **EQ** (igual) _ex:( id: EQ (10) - seleciona a entidade de id igual a 10)_
* **GT** (maior que) _ex:( id: GT (10) - seleciona todas as entidades com id maior a 10)_
* **GTE** (maior ou igual) _ex:( id: GTE (10) - seleciona todas as entidades com id maior ou igual a 10)_
* **LT** (menor que) _ex:( id: LT (10) - seleciona todas as entidades com id menor a 10)_
* **LTE** (menor ou igual) _ex:( id: LTE (10) - seleciona todas as entidades com id menor ou igual a 10)_
* **NULL** (nao definido) _ex:( age: null() - seleciona todas as entidades com idade não definida)_
* **IN** (en) _ex:( id: IN (10,18,33) - seleciona as entidades de id 10, 18 e 33)_
* **BET** (entre) _ex:( id: BET (100,200) - seleciona as entidades de id entre 100 e 200)_
* **LIKE** _ex:( name: LIKE (fael) - seleciona as entidades com nome LIKE '*fael*' (ver operador LIKE do sql))_
* **ILIKE** (LIKE ignorando maiúsculas e minúsculas) _ex:( name: ILIKE (rafael*) seleciona as entidades com o nome começando com Rafael, rafael, RAFAEL, etc.)_
* **OR** (operador lógico OU) _ex:( id: OR (BET (100,200), BET (300,400), IN (10,19,33)) - seleciona as entidades com id entre 100 e 200, entre 300 e 400 ou de id 10,19 ou 33)_
* **AND** (operador lógico AND) _ex:( name: AND (ILIKE ('Rafael%'), ILIKE ('*Freitas')) - seleciona as entidades com nome começando com Rafael e terminando com Freitas (por exemplo: Rafael Freitas, Rafael Chaves Freitas, RafaelFreitas))_
* **GEONEAR** _ex:( _geoLocation: GEONEAR (-46.6475415229797, -23.5413271705055, 700) - seleciona as entidades que estão no máximo há 700 metros do ponto de latitude -23.5413271705055 e longitude -46.6475415229797)_

Exemplos de uso.
----------------

* **retornando o nome do espaço de id 10**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/space/find',
  {
    '@select': 'name', 
    'id': 'eq(10)'
  },
  function (response){ console.log(response); });
```
* **retornando o id, nome e email dos agentes com email do google**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name, email', 
    'email': 'like(*gmail.com)'
  },
  function (response){ console.log(response); });
```
* **retornando o id e nome dos agentes com email do google ou do yahoo**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name', 
    'emailPublico': 'OR(like(*gmail.com), like(*yahoo.com))'
  }, 
  function (response){ console.log(response); });
```
* **retornando o id, nome dos agentes com com id entre 100 e 200, exceto o de id 150**


```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name', 
    'id': 'AND(BET(100,200), !EQ(150))'
  }, 
  function (response){ console.log(response); });
```
* **retornando o id e nome dos agentes com com id entre 100 e 200, exceto o de id 150 e que tenham email do google ordenado pela data de criação do agente**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name',
    '@order': 'createTimestamp ASC',
    'id': 'AND(BET(100,200), !EQ(150))',
    'emailPublico': 'OR(like(*gmail.com), like(*yahoo.com))'
  },
  function (response){ console.log(response); });
```
* **retornando o id e nome dos agentes com com id entre 100 e 200, exceto o de id 150 OU que tenham email do google ordenado pelo nome descendentemente**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name',
    '@order': 'name DESC',
    '@or': 1,
    'id': 'AND(BET(100,200), !EQ(150))',
    'emailPublico': 'OR(like(*gmail.com), like(*yahoo.com))'
  },
  function (response){ console.log(response); });
```
* **retornando a segunda página de 10 agentes ordenado pelo nome**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name, files',
    '@order': 'name ASC',
    '@limit': 10,
    '@page': 2
  },
  function (response){ console.log(response); });
```
* **retornando a segunda página de 10 agentes ordenado pelo nome com a url do avatar, url do thumbnail do avatar e todos os downloads**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name',
    '@order': 'name ASC',
    '@limit': 10,
    '@page': 2,
    '@files': '(avatar,avatar.avatarSmall,downloads):url'
  },
  function (response){ console.log(response); });
```
* **retornando as entidades próximas a latitude -23.5413271705055 e longitude -46.6475415229797**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name, location',
    '@order': 'name ASC',
    '_geoLocation': 'GEONEAR(-46.6475415229797,-23.5413271705055,700)'
  },
  function (response){ console.log(response); });
```

### Filtrando por relacionamentos

* **Selecionando todos os espaços do agente de id 1**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/agent/find',
  {
    '@select': 'id, name, location',
    '@order': 'name ASC',
    'owner': 'EQ(@Agent:1)'
  },
  function (response){ console.log(response); });
```

* **Selecionando todos os eventos do projeto de id 4**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/event/find',
  {
    '@select': 'id, name, location',
    '@order': 'name ASC',
    'project': 'EQ(@Project:4)'
  },
  function (response){ console.log(response); });
```