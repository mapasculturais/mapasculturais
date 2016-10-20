Documentacao da API
===================

_Para tornar mais fácil a leitura dos exemplos será utilizada a função getJSON do jQuery._

Action find
---------------

Parâmetros
O Método find aceita os seguintes parâmetros:

* **@select** - usado para selecionar as propriedades da entidade que serão retornadas pela api. Você pode retornar propriedades de entidades relacionadas. _ex:( @select: id,name,owner.name,owner.singleUrl)_
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
* **retornando o id e nome do espaços e nomes do agentes que publicaram os espaços **

```javascript
$.getJSON(
  'http://mapasculturais.local/api/space/find',
  {
    '@select': 'id, name, owner.name',
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

Action describe
---------------
Retorna a descrição de entidade.

```javascript
$.getJSON(
  'http://mapasculturais.local/api/event/describe',
  function (response){ console.log(response); });

// output:
{
   "id":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":true,
      "type":"integer",
      "length":null
   },
   "location":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":true,
      "type":"point",
      "length":null
   },
   "name":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":true,
      "type":"string",
      "length":255
   },
   "shortDescription":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":false,
      "type":"text",
      "length":null
   },
   "longDescription":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":false,
      "type":"text",
      "length":null
   },
   "certificateText":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":false,
      "type":"text",
      "length":null
   },
   "createTimestamp":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":true,
      "type":"datetime",
      "length":null
   },
   "status":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":true,
      "type":"smallint",
      "length":null
   },
   "_type":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":true,
      "type":"smallint",
      "length":null,
      "@select":"type"
   },
   "isVerified":{
      "isMetadata":false,
      "isEntityRelation":false,
      "required":true,
      "type":"boolean",
      "length":null
   },
   "parent":{
      "isMetadata":false,
      "isEntityRelation":true,
      "targetEntity":"Space",
      "isOwningSide":true
   },
   "children":{
      "isMetadata":false,
      "isEntityRelation":true,
      "targetEntity":"Space",
      "isOwningSide":false
   },
   "owner":{
      "isMetadata":false,
      "isEntityRelation":true,
      "targetEntity":"Agent",
      "isOwningSide":true
   },
   "emailPublico":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Email Público",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "emailPrivado":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Email Privado",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "telefonePublico":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Telefone Público",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "telefone1":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Telefone 1",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "telefone2":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Telefone 2",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "acessibilidade":{
      "required":false,
      "type":"select",
      "length":null,
      "private":false,
      "options":{
         "":"Não Informado",
         "Sim":"Sim",
         "Não":"Não"
      },
      "label":"Acessibilidade",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "capacidade":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Capacidade",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "endereco":{
      "required":false,
      "type":"text",
      "length":null,
      "private":false,
      "label":"Endereço",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "horario":{
      "required":false,
      "type":"text",
      "length":null,
      "private":false,
      "label":"Horário de funcionamento",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "criterios":{
      "required":false,
      "type":"text",
      "length":null,
      "private":false,
      "label":"Critérios de uso do espaço",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "site":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Site",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "facebook":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Facebook",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "twitter":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Twitter",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "googleplus":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Google+",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "sp_regiao":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Região",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "sp_subprefeitura":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Subprefeitura",
      "isMetadata":true,
      "isEntityRelation":false
   },
   "sp_distrito":{
      "required":false,
      "type":"string",
      "length":null,
      "private":false,
      "label":"Distrito",
      "isMetadata":true,
      "isEntityRelation":false
   }
}
```