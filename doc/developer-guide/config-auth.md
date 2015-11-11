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
