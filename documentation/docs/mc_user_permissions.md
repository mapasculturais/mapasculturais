# Lógica de Permissões
    (Explicar aqui todos os níveis de permissões do mapas)
# Permissões a nível de código

Após compreender o esquema de permissões do Mapas Culturais, vejamos como utilizá-los quando estamos programando.

#### - requireAuthentication
O método `$controller->requireAuthentication()` é definido na classe abstrata \MapasCulturais\Controller, e, portanto,
é aproveitado e pode ser sobrescrito por qualquer controller que a estenda.

Geralmente é utilizado logo no início das actions do controller cuja execução exija autenticação.
O usuário será redirecionado para a página de login caso esteja deslogado, e, posteriormente, continuará o fluxo do método onde foi invocado.

#### - accessControl
Os métodos `$app->disableAccessControl()`, `$app->enableAccessControl()` e `$app->isAccessControlEnabled()` manipulam e verificam o estado
de `_accessControlEnabled`, respectivamente.

Esta propriedade é verificada logo no início do método `canUser()`. 
Assim, se o controle de acesso estiver *desabilitado*, o método já retornará **'true'**, ignorando todas as demais verificações e permitindo a execução da condição que invocou `canUser()`.
 

Geralmente os métodos `$app->disableAccessControl()` e `$app->enableAccessControl()`são utilizados em conjunto
"antes" e "depois" de alguma lógica que necessita temporariamente do acesso de controle desabilitado para manipular o banco de dados.

Por exemplo, este trecho de código do método `_setStatusTo($status)` da entidade *Registration*:

            $app->disableAccessControl();
            $this->status = $status;
            $this->save(true);
            $app->enableAccessControl();

Como vemos, o método desabilita o controle de acesso, altera uma propriedade gerenciada do banco de dados e salva. Logo depois, habilita novamente o controle de acesso.


## Entidades com permissão de edição:
Toda entidade possui métodos que começam com `canUser*`, retornando **bool** checando se o usuário atual (logado ou não) tem permissão de realizar alguma action com essa entidade

canUser($action) - alias pra poder testar qualquer action

canUserView() - se o usuário pode ver essa entidade. Esses métodos são protected e não devem ser chamadas diretamente. Servem só pra implementarem as permissões. Sempre usar o canUser()

## Usuários com permissão de edição a entidade:

$entity->checkPermission($action) - checa se tem permissão e, se não tiver, throw execption de permissão negada
Este método, por sua vez, invoca o método canUser
