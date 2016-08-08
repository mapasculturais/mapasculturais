As consultas abaixo não são SQL puro, são DQLs ([Doctrine Query Language](http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html))
# Busca por palavra-chave
Por padrão, a busca por palavra-chave da API do Mapas Culturais retorna os objetos encontrados na query abaixo, ou seja, uma busca baseada somente no nome do objeto:
```SQL
SELECT 
    DISTINCT e.id 
FROM 
    {$entity_class} e
WHERE 
    unaccent(lower(e.name)) LIKE unaccent(lower(:keyword))
```

## Hooks para modificar a consulta por palavra-chave
O Mapas Culturais oferece dois _hooks_ para modificar a query acima: o primeiro permite adicionar _joins_ à consulta e o segundo permite adicionar operações dentro da cláusula _WHERE_:
- **repo($entity).getIdsByKeywordDQL.join** - utilizado para adicionar novos _joins_ à consulta, representado na consulta abaixo pela variável **$HOOK_JOINS**; 
- **repo($entity).getIdsByKeywordDQL.where** - utilizado para adicionar operações dentro da cláusula _WHERE_, reprensetado na consulta abaixo pela variável **$HOOK_WHERE**.

```SQL
SELECT 
    DISTINCT e.id 
FROM 
    {$entity_class} e 
    $HOOK_JOINS
WHERE 
    unaccent(lower(e.name)) LIKE unaccent(lower(:keyword)) 
    $HOOK_WHERE
```

## Exemplos práticos:
### Utilizando um metadado na busca por palavra-chave
Vamos usar como exemplo um metadado chamado __num_entidade__, que seria um código que representa a entidade no sistema e que, quando alguém fizesse uma busca por palavra-chave informando tal código, o sistema deveria retornar este objeto. Vamos supor ainda que este código só é aplicado a agentes e espaços, porém para aplicar a todas as entidades que _usam_ busca por palavra-chave (repositórios que usam o _trait_ **MapasCulturais\Traits\RepositoryKeyword**) basta substituir a parte do hook _<\<Agent|Space\>>_ por _<<*>>_
```PHP
$app = \MapasCulturais\App::i(); // instância da aplicação

// adiciona o join do metadado
$app->hook('repo(<<Agent|Space>>).getIdsByKeywordDQL.join', function(&$joins, $keyword){
    $joins .= "
        LEFT JOIN 
            e.__metadata num_entitdade 
        WITH 
            num_entidade.key = 'num_entidade'";
});

// filtra pelo valor do keyword
$app->hook('repo(<<Agent|Space>>).getIdsByKeywordDQL.where', function(&$where, $keyword){
    // a variável $keyword está disponível aqui porém não é recomendado que esta seja utilizada diretamente na consulta
    // pois ela já estará disponível dentro da consulta pelo parâmetro :keyword.
    $where .= " OR lower(num_entidade.value) LIKE lower(:keyword)";
});
```
### Utilizando uma taxonomia na busca por palavra-chave
Agora vamos utilizar como exemplo uma taxonomia chamada "categoria"
```PHP
$app->hook('repo(<<*>>).getIdsByKeywordDQL.join', function(&$joins, $keyword) {
    $taxonomy = App::i()->getRegisteredTaxonomyBySlug('categoria');
    $joins .= "LEFT JOIN e.__termRelations categoria_tr
        LEFT JOIN
                categoria_tr.term
                    categoria
                WITH
                    categoria.taxonomy = '{$taxonomy->id}'";
});

$app->hook('repo(<<*>>).getIdsByKeywordDQL.where', function(&$where, $keyword) {
    $where .= " OR unaccent(lower(categoria.term)) LIKE unaccent(lower(:keyword)) ";
});
```
