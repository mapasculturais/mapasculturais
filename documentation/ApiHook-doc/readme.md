# Documentação da API

A documentação da API do Mapas é feita usando o [APIDocs](http://apidocjs.com).

Para suportar alguns parâmetros específicos que precisamos utilizar, e também para fazer a documentação de hooks, que ainda está em construção, fizemos um fork do APIDocs. Veja abaixo sobre como compilar a documentação no seu ambiente local.

## Documentando os Endpoints

Visite o site do [APIDocs](http://apidocjs.com) para entender as possibilidades de notação.

A documentação é feita em blocos de comentários em cima de cada método que define os endpoints.

Para métodos que são utilizados em várias classes diferentes (como Traits, por exemplo), eles são documentados uma vez só na origem, e são chamados utilizando `@apiUse`. Veja as documentações em `Traits/ControllerAPI.php` e em `Controllers/Agent.php`.

## Compilando a documentação

Faça o clone desses dois repositórios

* https://github.com/vnmedeiros/apidoc-core
* https://github.com/vnmedeiros/apidoc

```
git clone https://github.com/vnmedeiros/apidoc
git clone https://github.com/vnmedeiros/apidoc-core
```

Entre nos diretorios e instale as dependëncias rodando `npm install`.

Para compilar a documentação rode o seguinte comando:

```
./apidoc/bin/apidoc \
    --config /home/leo/devel/mapasculturais-culturagovbr/documentation/ApiHook-doc \
    --template /home/leo/devel/mapasculturais-culturagovbr/documentation/ApiHook-doc/template \
    --input /home/leo/devel/mapasculturais-culturagovbr/src/protected/application/lib/MapasCulturais \
    --debug true
```

Substitua `/caminho-para-repositorio` com o caminho para o repositorio do Mapas Culturais onde está seu código fonte.

Por padrão, a documentação é compilada em uma pasta `doc` dentro de onde. Se quiser mudar o destino, passe também o atributo `output`.