# ProjectCuradores
Plugin para habilitar o role Curadores para as convocatorias.

Estes usuarios podem ver as inscrições, mas não podem editar o projeto.

Para dar permissão de Curador a um agente, edite o projeto e crie um grupo de Agentes relacionados chamado "Curadores".

Se quiser utilizar um grupo com um nome diferente, modifique este nome na ativação do plugin, conforme exemplo abaixo.

## Ativação

Para ativar este plugin, adicione a seu config.php

```PHP

'plugins' => [

    //... other plugin you may have...
    'ProjectCuradores' => [
        'namespace' => 'ProjectCuradores',
        'config' => [
            'group_name' => 'Curadores'
        ]
    ],
],

```
