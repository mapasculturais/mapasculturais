# MapaCultural - Nova Arquitetura

Essa a

## Instalação

Para instalar as dependências e atualizar o autoload, entre no container da aplicação e execute:
```shell
composer.phar install
```

---

## Arquitetura


### Web


### API
Para criar novos endpoints siga o passo a passo:

#### 1 - Controller
Crie uma nova classe em `/app/Controller/Api/`, por exemplo, `EventApiController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api;

class EventApiController
{
    
}
```

#### 2 - Método/Action
Crie seu(s) método(s) com a lógica de resposta.

> Para gerar respostas em json, estamos utilizando a implementação da `JsonResponse` fornecida pelo pacote do Symfony:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class EventApiController
{
    public function getList(): JsonResponse
    {
        $events = [
            ['id' => 1, 'name' => 'Palestra'],
            ['id' => 2, 'name' => 'Curso'],
        ];   
    
        return new JsonResponse($events);
    }
}
```

#### 3 - Rotas

Acesse o arquivo `/app/routes/api.php` e adicione sua rota, apontando pro seu controller e método

```php
    use App\Controller\Api\EventApiController;

    $routes = [
        //... other routes
        '/api/v2/events' => [EventApiController::class, 'getList'],
    ];
```

#### 4 - Pronto

Feito isso seu endpoint deverá estar disponivel em:
<http://localhost/api/v2/events>

---

## Testes
Para executar os testes, entre no container da aplicação e execute o seguinte comando:

```shell
php vendor/bin/phpunit app/tests
```