# Personalizando as listagens da busca

Nesta página veremos como personalizar a listagem dos resultados da busca, tanto na visualização em lista como na visualização em mapas, na caixa que se abre quando se clica em um pin, que chamamos de infobox.

## Adicionando um novo campo ao resultado da busca

Se o que você quer é colocar uma nova informação sobre a entidade, é preciso que você acrescente este campo a consulta que é feita a API quando uma busca é realizada.

Por padrão, apenas alguns campos são retornados:

* ID
* URL
* Nome
* Subtítulo
* Tipo
* Descrição curta
* Áreas de atuação e tags (terms)
* URL e nome do projeto relacionado (quando é um evento)

Se você quiser exibir alguma outra informação, é preciso informar qual campo deseja que também seja retornado na busca. Para isso usamos uma função disponível na classe do tema. 

Por exemplo para adicionar o campo município, faça:

```PHP
$this->addSearchQueryFields('En_Municipio');
```

Note que $this neste caso é a classe do tema. Se for fazer isso a partir de um plugin, terá que invocar a classe do tema.

Veja o exemplo mais completo adicionando a informação de município a busca:

```PHP

<?php
namespace MeuTema;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    function _init() {
        $app = App::i();
        
        parent::_init();

        // ... outras coisas que você pode fazer aqui...
        
        $this->addSearchQueryFields('En_Municipio');
        
        
    }

}
```

## Personalizando os templates

Há duas maneiras de personalizar os templates da infobox e da listagem. A primeira, e mais indicada, é através dos hooks disponíveis nestes templates. A segunda é sobrescrevendo os templates completamente no seu tema ou plugin.

Os templates estão localizados na pasta BaseV1/layouts/parts/search e são:

Para as infoboxes do mapa:

* infobox-agent.php
* infobox-space.php
* infobox-event.php

Para o resultado em lista:
* list-agent-item.php
* list-space-item.php
* list-project-item.php
* list-event-item.php

Estes templates são apenas HTML, e os loops e conteúdos dinâmicos são feitos pelo AngularJS.

Ao abrir esses templates você vai perceber que eles possuem hooks que você pode usar para inserir código novo, sem precisar sobrescrever o template. Por exemplo, na listagem de espaços, há os hooks list.space.meta:begin e list.space.meta:end, que te permite adicionar novas informações no começo ou no final de onde aparecem os metadados das entidades.

### Utilizando hooks

Veja um exemplo completo adicionando o campo de município no infobox e na listagem de espaços:

```PHP

<?php
namespace MeuTema;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    function _init() {
        $app = App::i();
        
        parent::_init();

        
        // ... outras coisas que você pode fazer aqui...
        
        $this->addSearchQueryFields('En_Municipio');
        
        $app->hook('template(site.search.space-infobox-new-fields-before):begin', function() {
            echo '<div><span class="label">Município:</span> {{openEntity.space.En_Municipio}}</div>';
        });
        
        $app->hook('template(site.search.list.space.meta):begin', function() {
            echo '<div><span class="label">Município:</span> {{space.En_Municipio}}</div>';
        });
        
    }

}
```

Se quiséssemos adicionar também aos resultados de agentes, poderíamos modificar a chamada pelo hook, e fazer assim:

```PHP

<?php
        
$app->hook('template(site.search.<<space|agent>>-infobox-new-fields-before):begin', function() {
    echo '<div><span class="label">Município:</span> {{openEntity.space.En_Municipio}}</div>';
});

$app->hook('template(site.search.list.<<space|agent>>.meta):begin', function() {
    echo '<div><span class="label">Município:</span> {{space.En_Municipio}}</div>';
});

```

### Sobrescrevendo os templates

Se preferir, você pode simplesmente sobrescrever os templates que quer modificar fazendo uma cópia do arquivo no seu tema ou plugin, mantendo a mesma estrutura de pastas.

