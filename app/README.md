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


--- 

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

Acesse o arquivo `/app/routes/api.php` e adicione sua rota, informando o `path`, o verbo HTTP, e apontando pro seu controller e método

```php
    use App\Controller\Api\EventApiController;
    use Symfony\Component\HttpFoundation\Request;

    $routes = [
        //... other routes
        '/api/v2/events' => [
            Request::METHOD_GET => [EventApiController::class, 'getList']
        ],
    ];
```

Se preferir pode criar um arquivo no diretório `/app/routes/api/` e isolar suas novas rotas nesse arquivo, basta fazer isso:

```php
    // /app/routes/api/event.php

    use App\Controller\Api\EventApiController;
    use Symfony\Component\HttpFoundation\Request;

    return [
        '/api/v2/events' => [
            Request::METHOD_GET => [EventApiController::class, 'getList']
            Request::METHOD_POST => [EventApiController::class, 'create']
        ],
    ];
```

Esse seu novo arquivo será reconhecimento automagicamente dentro da aplicação.

#### 4 - Pronto

Feito isso, seu endpoint deverá estar disponivel em:
<http://localhost/api/v2/events>

E deve estar retornando algo como:
```json
[
  {
    "id": 1,
    "name": "Palestra"
  },
  {
    "id": 2,
    "name": "Curso"
  }
]
```

---

## Testes
Para executar os testes, entre no container da aplicação e execute o seguinte comando:

```shell
php vendor/bin/phpunit app/tests
```