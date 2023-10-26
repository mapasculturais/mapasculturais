# Componente `<mc-link>`
Adiciona um link para uma entidade ou rota

## Propriedades
- *Entity **entity** = null* - Entidade
- *Bollean **icon** = false* - Se deve exibir o ícone da entidade antes do label
- *String **route** = 'single'* - Rota da entidade (single|edit|etc)
- *String **params** = []* - Parâmetros para incluir na url.
- *String **getParams** = {}* - Parâmetros GET para incluir na url.
- *String **class** = ''* - classes para incluir na tag a do link

## Slots
- **default** *opctional se enviado uma entidade* - Texto do link. Se omitido, imprime o nome da entidade

### Importando componente
```PHP
<?php 
$this->import('mc-link');
?>
```
### Exemplos de uso
```HTML
<!-- uso mais simple -->
<mc-link :entity="entity"></mc-link>

<!-- exibindo ícone da entidade -->
<mc-link :entity="entity" icon></mc-link>

<!-- definindo o label do link -->
<mc-link :entity="entity">Acessar</mc-link>

<!-- link para página de edição da entidade -->
<mc-link :entity="entity" route='edit'>Editar</mc-link>

<!-- link para o painel -->
<mc-link route="panel/index">Painel</mc-link>

<!-- link para a home do site com ícone -->
<mc-link route="site/index" icon="home">Home</mc-link>

<!-- incluindo uma classe na tag do link -->
<mc-link route="panel/index" class="panel">Painel</mc-link>

<!-- passando parâmetros GET: /controller/action?param1=valor1&param2=valor2-->
<mc-link route="controller/action" get-params="{param1: 'valor1', param2: 'valor2'}">Link</mc-link>

<!-- passando parâmetros GET: /controller/action/param1:valor1/param2:valor2/-->
<mc-link route="controller/action" params="{param1: 'valor1', param2: 'valor2'}">Link</mc-link>

```