# Configurando o sistema de autenticação do Mapas Culturais
O Mapas Culturais disponibiliza dois _drivers_ de autenticação para serem utilizados em ambientes de produção/homologação ([OpauthOpenId](#OpauthOpenId) e [OpauthLoginCidadao](#OpauthLoginCidadao)), um para ser utilizado em ambiente de _desenvolvimento/homologação_ ([Fake](#Fake)), e outro utilizado nos testes automatizados ([Test](../src/protected/application/lib/MapasCulturais/AuthProviders/Test.php)).

## Fake
**classe**: [MapasCulturais\AuthProviders\Fake](../src/protected/application/lib/MapasCulturais/AuthProviders/Fake.php)

Este método de autenticação é para ser utilizado em ambientes de desenvolvimento, teste ou homologação, quando não é necessário testar o processo de autenticação por completo. Ele simplifica o processo de autenticação/criação de usuários ao mostrar numa mesma tela uma lista dos usuários existentes no sistema e um formulário simplificado para criação de novos usuários.

Para utilizar este método de autenticação você precisa colocar a linha abaixo no seu arquivo **src/protected/application/conf/config.php**
```PHP
    'auth.provider' => 'Fake',
```

## OpauthOpenID
**classe**: [MapasCulturais\AuthProviders\OpauthOpenId](../src/protected/application/lib/MapasCulturais/AuthProviders/OpauthOpenId.php)

Este driver é implementado utilizando a biblioteca [Opauth](http://opauth.org/) com a estratégia [OpenID](https://github.com/opauth/openid) e pode ser utilizada para autenticar o Mapas Culturais com qualquer OpenID provider. 
O exemplo a seguir mostra como configurar o Mapas Culturais para se autenticar com o [Id da Cultura](https://github.com/hacklabr/mapasculturais-openid)¹ instalado no servidor que responde pelo domínio _id.map.as_². 

_1. Não confundir com o [IDCultura do Ministério da Cultura](id.cultura.gov.br), que é baseado no [Login Cidadão](https://github.com/PROCERGS/login-cidadao/) abordado a [seguir](#OpauthLoginCidadao)._

_2. O deploy e customização do tema do Id da Cultura não será abordado neste documento._

#### Exemplo de configuração do OpauthOpenId
```PHP
    'auth.provider' => 'OpauthOpenId',
    'auth.config' => [
        // substituir o dominio "id.map.as" pelo domínio do
        'login_url' => 'http://id.map.as/openid/', 
        'logout_url' => 'http://id.map.as/accounts/logout/', 
        'salt' => 'UMAS STRING ALEATÓRIA', // exemplo 'fsdf#F#$T!WHS%$Y%HThw45h45h$%H45h42y45.$$234'
        'timeout' => '24 hours' // o tempo que dura a sessão
    ],
```

## Login Cidadão & Mapas Culturais > Logout Inside Application

### Configurações para autenticação via LC no config.php

Para usar a autenticação do Mapas com Login Cidadão, esteja atento os seguintes parâmetros que devem ser inseridos no arquivo de configurações da aplicação (este arquivo está em /mapasculturais/src/protected/application/conf/config.php): 

```

        'auth.provider' => 'OpauthLoginCidadao',
        'auth.config' => array(
        'client_id' => 'NUMERO-DO-CLIENT-ID',
        'client_secret' => 'HASH-DO-SECRET',
	    'auth_endpoint' => 'http://id.cultura.gov.br/oauth/v2/auth',
        'token_endpoint' => 'http://id.cultura.gov.br/oauth/v2/token',
        'user_info_endpoint' => 'http://id.cultura.gov.br/api/v1/person.json'
        ),
```

### Logout via Login Cidadão

O [Login Cidadão] (https://github.com/redelivre/login-cidadao) possui uma feature chamada [Remote Logout] (https://github.com/PROCERGS/login-cidadao/blob/9bc9ff8220b968726682767f8c934d5562fe6a35/app/Resources/doc/en/remoteLogout.md) que permite com que usuários de uma aplicação externa (no caso o Mapas Culturais) possa fazer logout no sistema de autenticação (Login Cidadão) sem sairem do ambiente da aplicação. Em outras palavras, de dentro do Mapas, usando Remote Logout, um usuário poderia sair do Login Cidadão. No entanto essa funcionalidade ainda não foi implementada/testada para operar com Mapas Culturais. Provisoriamente, uma função javascript tem funcionado como um caminho seguro para fazer logout do Login Cidadão de dentro do Mapas. Caso o Login Cidadão seja seu único sistema de autenticação funcionando em conjunto com Mapas Culturais e você queira prover o que chamamos de Logout Inside Application, siga as instruções abaixo para implementar a saída. 

1 - Acesse o arquivo de header da aplicação, do template criado e ativo na instalação:

```
$ vi ~/mapasculturais/src/protected/application/themes/NAME-OF-YOUR-TEMPLATE/layouts/parts/header-main-nav.php
```
2 - Acrescente, no final deste arquivo, a seguinte função javascript:  

```
<script language="javascript" type="text/javascript">
function logoutForce() {
    window.open('https://NAME-OF-YOUR-URL-LOGIN-CIDADAO-HERE/logout', '_blank');
    var oldURL = document.referrer;
    alert("Logout efetuado com sucesso!");
    location.reload();
}
</script>
```
3 - No mesmo arquivo, busque a linha que gera o botão "Sair" e acrescente o evento onclick="return logoutForce(). Veja como a linha deve ficar: 

```
<a href="<?php echo $app->createUrl('auth', 'logout'); ?>" onclick="return logoutForce()">Sair</a>
```

