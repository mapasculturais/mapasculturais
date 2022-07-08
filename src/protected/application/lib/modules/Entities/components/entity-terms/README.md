# Componente `<entity-terms>`
Componente para exibição e edição dos termos das taxonomias de uma entidade. 
  
## Propriedades
- *Boolean **editable** = false* - Habilita o modo de edição do componente;
- *Entity **entity*** - Entidade com a propriedade `terms` carregada. Para saber como se obter o objeto entity ver a documentação dos componentes `<entity>` e `<entities>`;
- *String **taxonomy*** - Slug da taxonomia dos termos;
- *String **title*** (opcional) - Label do elemento. Se não informado, utilizará o nome da taxonomia registrada na aplicação;

### Importando componente
```PHP
<?php 
$this->import('entity-terms');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem das tags -->
<entity-terms :entity="entity" taxonomy="tag" title="Tags" ></entity-terms>

<!-- utilizaçao básica para listagem das áreas de atuação -->
<entity-terms :entity="entity" taxonomy="area" ></entity-terms>

<!-- utilizaçao básica para edição das áreas de atuação -->
<entity-terms :entity="entity" taxonomy="area" :editable="true"></entity-terms>

```
