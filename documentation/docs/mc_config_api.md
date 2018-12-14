Documentacao da API
===================

Esta página reúne alguns exemplos de uso da API, em especial dos métodos de busca por entidades.

A documentação completa de todos os endpoints está sendo construída aqui: [http://docs.mapasculturais.org/apidoc/index.html?doctype=api](http://docs.mapasculturais.org/apidoc/index.html?doctype=api)

Exemplos de uso.
----------------

_Para tornar mais fácil a leitura dos exemplos será utilizada a função getJSON do jQuery._

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
* **retornando o id e nome do espaços e nomes do agentes que publicaram os espaços**

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
* **retornando o id, nome dos eventos e sua lista de ocorrências contendo id, nome do local e o objeto rule das mesmas. No objeto rules, há o campo description, que contém uma versão legível para humanos da data, horários e frequência da ocorrência. A busca filtra eventos de id entre 100 e 200.**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/event/find',
  {
    '@select': 'id, name,occurrences.{id,space.{name},rule}',
    'id': 'BET(100,200)'
  },
  function (response){ console.log(response); });
```
* **retornando o id, nome dos eventos e o objeto terms, que agrupa taxonomias, neste caso linguagens (cada entidade tem as suas taxonomias). A busca filtra eventos que tenham a linguagem LIKE 'Cinema' e possuam id entre 100 e 200.**

```javascript
$.getJSON(
  'http://mapasculturais.local/api/event/find',
  {
    '@select': 'id, name,terms' ,
    'term:linguagem': 'LIKE(Cinema)',
    'id': 'BET(100,200)'    
  },
  function (response){ console.log(response); });
```

* **retornando o id, nome e a descrição curta dos eventos, junto com os endereços (URL) para os arquivos do header e avatar. A busca filtra eventos de id entre 100 e 200.**
```javascript
$.getJSON(
  'http://mapasculturais.local/api/event/find',
  {
    '@files': 'header.header, avatar.avatarBig',
    '@select': 'id, name, shortDescription',
    'id': 'BET(100,200)'
  },
  function (response){ console.log(response); });
```

* **retornando a versão atual da instalação do mapasculturais.**
```javascript
$.getJSON(
  'http://mapasculturais.local/api/site/version',
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


# API de Escrita
Para conseguir criar, atualizar ou apagar entidades via API, primeiramente é necessário cadastrar o seu App indo no painel de usuário do Mapas Culturais e acessando "Meus Apps". Após criar um novo App, copie as duas chaves que são fornecidas (Pública e Prvada).
A API de escrita do Mapas utiliza o padrão de JSON Web Tokens - [JWT](https://jwt.io) para identificar de maneira segura dois sistemas que queiram se comunicar.

## Criando o JWT
Um JWT é formado de 3 partes - Header, Payload e Assinatura - encodadas com a função _base64UrlEncode_(1) concatenadas utilizando um ponto (.) como aglutinador.

_**1**_ - é um base64encode fazendo as seguintes subsituições na string:
```
  '=' -> '' // remove os caracteres '='
  '+ -> '-'
  '/' -> '_'
```

### Header
O Header do JWT geralmente consiste em informar o tipo do token (JWT!) e o algorítimo de hash utilizado. Para a API do Mapas Culturais o header deve seguir o formato abaixo, utilizando um dos seguintes algorítmos de hash: _HS512, HS384, HS256, RS256_.
```
{
  "typ": "JWT",
  "alg": "HS256"
}

resultado do base64encode: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9
```

### Payload
O Payload deve conter sua chave pública, identificada como "pk" e um timestamp(1) "tm" que é um timestamp Unix em microsegundos do momento em que a requisição foi enviada.
```
{
  "tm": "1493823078.9774",
  "pk": "chave publica"
}

resultado do base64encode: eyJ0bSI6IjE0OTM4MjMwNzguOTc3NCIsInBrIjoiY2hhdmUgcHVibGljYSJ9
```
_**1** - O timestamp é opcional, mas serve como um salt para empedir que o token gerado seja sempre o mesmo.

### Assinatura
A assinatura do JWT é o base64UrlEncode do resultado da concatenação do header e do payload (ambos encodados com o base64UrlEncode e utilizando um ponto como aglutinador) criptografado utilizando o hash informado no header com sua chave privada.

```
HMACSHA256(
  base64UrlEncode(header) + "." + base64UrlEncode(payload),
  'chave privada'
)

resultado do base64UrlEncode: 3UAdCFaqi1GkVMebr1a0WdOLc1QUUKPNlwlEXjb2peg
```

### Resultado
O Token gerado para um momento de timestamp **1493823078.9774** e chaves: **chave publica** e **chave privada** é `eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0bSI6IjE0OTM4MjMwNzguOTc3NCIsInBrIjoiY2hhdmUgcHVibGljYSJ9.3UAdCFaqi1GkVMebr1a0WdOLc1QUUKPNlwlEXjb2peg`


## Fazendo requisições

Toda requisição feita deve conter duas informações no seu _header_: o valor do JWT no _header_ "authorization" e um _header_ "MapasSDK-REQUEST" com valor "true"

```javascript
"authorization": eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0bSI6IjE0OTM4MjMwNzguOTc3NCIsInBrIjoiY2hhdmUgcHVibGljYSJ9.3UAdCFaqi1GkVMebr1a0WdOLc1QUUKPNlwlEXjb2peg,
"MapasSDK-REQUEST": true
```

Visite a [documentação da API](http://docs.mapasculturais.org/apidoc/index.html?doctype=api) para a lista completa para os endpoints para criar, atualizar e apagar entidades.

## SDK

Se você estiver desenvolvendo uma aplicação em PHP, há uma SDK que pode deixar as coisas bem mais fáceis.

Acesse o [projeto do Github da SDK](https://github.com/centroculturalsp/MapasSDK) e veja arquivo de exemplo sobre como a utilizar.


## Exemplos

* criando um agente

```PHP
$data = [
    'type' => '2',
    'name' => 'Fulano ' . date('Y/m/d H:i:s'),
    'shortDescription' => 'Oi',
    'terms' => [
        'area' => [
            'Arqueologia'
        ]
    ],
    'location' => [
        '-46.685684400000014',
        '-23.5404024'
    ],
    'endereco' => 'Rua Capital Federal'
];

$curl->setHeader('authorization', $jwt);
$curl->setHeader('MapasSDK-REQUEST', true);

$curl->post('http://mapas.cultura.gov.br/agent/index', $data);
$curl->close();
```

* alterando um agente
```PHP
$data = [
    shortDescription => 'Description'
];

$curl->setHeader('authorization', $jwt);
$curl->setHeader('MapasSDK-REQUEST', true);

$curl->patch('http://mapas.cultura.gov.br/agent/single/35', $data);
$curl->close();
```

* apagando um espaço
```PHP
$curl->setHeader('authorization', $jwt);
$curl->setHeader('MapasSDK-REQUEST', true);

$curl->delete('http://mapas.cultura.gov.br/space/single/8');
$curl->close();
```
