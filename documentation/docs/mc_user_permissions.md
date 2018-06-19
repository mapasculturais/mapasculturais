# Permissões por perfil de usuário
O Mapas Culturais apresenta uma hierarquia de permissões composta por cinco tipos de perfil,\
de modo que cada nova camada de perfil acrescenta novas capacidades, além de possuir todas as permissões do perfil da camada imediatamente abaixo.

[Consulte aqui](mc_user_profile.md) a documentação de permissões por perfil.

# Permissões a nível de código

Após compreender o esquema de permissões do Mapas Culturais, veja abaixo métodos úteis para gerenciamento da permissão durante o desenvolvimendo:

### - is($role)
O método `$user->is($role)` é oriundo da interface UserInterface,  e implementado na entidade User.

É largamente utilizado ao longo da aplicação para verificar a permissão a alguma ação com base no perfil do usuário atual.

Por exemplo, conforme vimos, o perfil super admin pode gerenciar outros usuários admin.
No código, está implementado da seguinte maneira:

```
    protected function canUserRemoveRoleAdmin($user){
        return $user->is('superAdmin') && $user->id != $this->id;
    }
```

E, para verificarmos se o usuário não está logado: `$user->is('guest'))`

#### - getAuth
O método `$app->getAuth()` retorna o AuthProvider em uso.

Se o usuário estiver logado, retorna também a instância da entidade User correspondente, obtida por meio de `$app->getAuth()->getAuthenticatedUser()`.
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

#### - canUser
Toda entidade possui métodos que começam com `canUser*`, retornando **bool** checando se o usuário atual (logado ou não) tem permissão de realizar alguma ação com essa entidade.

Esses métodos, 'variação' de `canUser()` são encapsulados como **protected** e, portanto, não podem ser chamadas diretamente. 
Servem só pra implementarem as permissões. Assim, devemos utilizar sempre o `canUser()`.

 - **canUser($action)** - *alias* pra poder testar qualquer *$action*
 - **canUserView()** - Verifica se o usuário pode ver a entidade. (para testar essa permissão, devemos chamar `$entity->canUser('view')`)

#### - checkPermission
Checa se usuário tem permissão para a ação passada como parâmetro. Se não tiver, lança uma exceção de permissão negada.

Verifica a permissão utilizando o método `canUser($action)`.

Exemplo

```
$entity->checkPermission('view'); 
```
