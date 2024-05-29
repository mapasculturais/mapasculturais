# MapaCultural - Nova Arquitetura

Essa é a nova documentação do MapaCultural, voltada para desenvolvedores e/ou entusiastas do código.

<details>
    <summary>Acesso Rápido</summary>
    
[Instalação dos Pacotes](#Instalação)<br>
[Controller](#API)<br>
[Repository](#Repository)<br>
[Command](#Command)<br>
[Data Fixtures](#Data-Fixtures)<br>
[Testes](#Testes)<br>
[Console](#console-commands)<br>

</details>

## Getting Started

### Instalação dos pacotes

Para instalar as dependências e atualizar o autoload, entre no container da aplicação e execute:
```shell
composer.phar install
```

--- 

## Controller

Os `Controllers` em conjunto com as `Routes` permitem criar endpoints para diversas finalidades.

<details>
<summary>Como criar um novo controller</summary>

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
        Request::METHOD_GET => [EventApiController::class, 'getList'],
        Request::METHOD_POST => [EventApiController::class, 'create'],
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

</details>

---

## Validation
A camada de validação é responsável por validar as entradas e saídas de dados

Documentação do Validator: <https://symfony.com/doc/current/components/validator.html>

Para organizar e validar esses dados nós utilizaremos o componente de validação atrelados a um DTO, cada propriedade terá suas regras e grupos

<details>
<summary>Como criar um novo DTO</summary>

#### 1 - Data Transfer Object (DTO)
Crie uma nova classe em `/app/DTO/`, por exemplo, `SealDTO.php`:

```php
<?php

declare(strict_types=1);

namespace App\DTO;

final class SealDTO
{
    ...   
}
```

#### 2 - Propriedades
Para cada propriedade do DTO, descreva as regras de validação e os grupos

> [Lista completa](https://symfony.com/doc/current/reference/constraints.html) de regras do componente
```php
<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;

final class SealDTO
{
    #[Sequentially([new NotBlank(), new Type('string')], groups: ['post'])]
    #[Type('string', groups: ['patch'])]
    public mixed $name;

    [...]   
}
```

#### 3 - Validando

Como mostrado no código acima a propriedade tem uma regra de validação e um grupo, esse grupo é importante para discernir o contexto de quais regras devem ser utilizadas
> Por exemplo, no código acima temos uma regra em especial para o post, a propriedade _name_ é requerida.

Agora como esse DTO será usado para validar algo?!

Após a requisição ser enviada, o corpo será transformado de array para SealDTO, então iremos passar o objeto e o grupo para o validator, caso tenha alguma violação ela será retornada

```php
[...]

$seal = $this->serializer->denormalize($data, SealDto::class);

$validation = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

$violations = $validation->validate($seal, groups: ['patch']);

if (0 < count($violations)) {
    throw new ValidatorException(violations: $violations);
}

[...] 
```
> O código acima normalmente estará no _Controller_ ou _Request_

O objeto dessa validação é validar dados e não regra de négocio

</details>

---

## Repository

A camada responsável pela comunicação entre nosso código e o banco de dados.

<details>
<summary>Como criar um novo repository</summary>

Siga o passo a passo a seguir:

#### Passo 1 - Crie sua classe no `/app/src/Repository` e extenda a classe abstrata `AbstractRepository`

```php
<?php

declare(strict_types=1);

namespace App\Repository;

class MyRepository extends AbstractRepository
{
}
```

#### Passo 2 - Defina a Entity principal que esse repositório irá gerenciar

```php

use Doctrine\Persistence\ObjectRepository;
use App\Entity\MyEntity;
...

private ObjectRepository $repository;

public function __construct()
{
    parent::__construct();
    
    $this->repository = $this->entityManager->getRepository(MyEntity::class);
}
```

caso a sua entidade não esteja mapeada nessa parte da aplicação (V8), você precisará de um `entityManager` diferente, observer a seguir:

```php
use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\MyEntity;
...

private ObjectRepository $repository;

public function __construct()
{
    parent::__construct();
    
    $this->repository = $this->mapaCulturalEntityManager->getRepository(MyEntity::class);
}
```
</details>

---

## Command
Comandos são entradas via CLI (linha de comando) que permitem automatizar alguns processos, como rodar testes, veririfcar estilo de código, e debugar rotas

<details>
<summary>Como criar um novo console command</summary>

#### Passo 1 - Criar uma nova classe em `app/src/Command/`:

```php
<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand extends Command
{
    protected static string $defaultName = 'app:my-command';
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello World!');
        
        return Command::SUCCESS;  
    }
} 
```

#### Passo 2 - Testar seu comando no CLI

Entre no container da aplicação PHP e execute isso

```shell
php app/bin/console app:my-command
```

Você deverá ver na tela o texto `Hello World!`

#### Passo 3 - Documentação do pacote
Para criar e gerenciar os nosso commands estamos utilizando o pacote `symfony/console`, para ver sua documentação acesse:

> Saiba mais em <https://symfony.com/doc/current/console.html>

Para ver outros console commands da aplicação acesse a seção [Console Commands](#console-commands)

</details>

---

## Data Fixtures
Data Fixtures são dados falsos, normalmente criados para testar a aplicação, algumas vezes são chamados de "Seeders".

<details>
<summary>Como criar uma DataFixture para uma Entidade</summary>

#### Passo 1 - Criar uma nova classe em `app/src/DataFixtures/`:

```php
<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Agent;

class AgentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $agent = new Agent();
        $agent->name = 'Agente Teste da Silva';
        
        $manager->persist($agent);
        $manager->flush();
    }
} 
```

#### Passo 2 - Executar sua fixture no CLI

Entre no container da aplicação PHP e execute isso

```shell
php app/bin/console app:fixtures
```

Pronto, você deverá ter um novo Agente criado de acordo com a sua Fixture.

> Saiba mais sobre DataFixtures em <https://www.doctrine-project.org/projects/doctrine-data-fixtures/en/1.7/index.html>

</details>

---

## Testes
Estamos utilizando o PHPUnit para criar e gerenciar os testes automatizados, focando principalmente nos testes funcionais, ao invés dos testes unitários.

Documentação do PHPUnit: <https://phpunit.de/index.html>

<details>
<summary>Como criar um novo teste</summary>

### Criar um novo teste
Para criar um no cenário de teste funcional, basta adicionar sua nova classe no diretório `/app/tests/functional/`, com o seguinte código:

```php
<?php

namespace App\Tests;

class MeuTest extends AbstractTestCase
{
    
}
```

Adicione dentro da classe os cenários que você precisa garantir que funcionem, caso precise imprimir algo na tela para "debugar", utilize o método `dump()` fornecido pela classe `AbstractTestCase`:

```php
public function testIfOneIsOne(): void
{
    $list = ['Mar', 'Minino'];
    
    $this->dump($list); // equivalente ao print_r
    
    $this->assertEquals(
        'MarMinino',
        implode('', $list)
    );
}
```

Para executar os testes veja a seção <a href="#console-commands">Console Commands</a>
</details>

---

## Console Commands


<details>
<summary>TESTS</summary>

### Testes Automatizados
Para executar os testes, entre no container da aplicação e execute o seguinte comando:

```shell
php app/bin/console tests:backend {path}
```

O `path` é opcional, o padrão é "app/tests"
</details>

<details>
<summary>STYLE CODE</summary>

### Style Code
Para executar o PHP-CS-FIXER basta entrar no container da aplicação e executar

```shwll
php app/bin/console app:code-style
```
</details>

<details>
<summary>DATA FIXTURES</summary>

### Fixtures

:memo: Fixtures são dados falsos com a finalidade de testes.


Para executar o conjunto de fixtures basta entrar no container da aplicação e executar
```
php app/bin/console app:fixtures
```
</details>

<details>
<summary>DEBUG ROUTES</summary>

### Debug router
Para listas as routas basta entrar no container da aplicação e executar
```
php app/bin/console debug:router
```

> Podemos usar as flags --show-actions e --show-controllers
</details>

<details>
<summary>DOCTRINE</summary>

### Doctrine
Para listas todos os comandos disponiveis para gerenciamento do banco de dados através do doctrine basta entrar no container da aplicação e executar
```
php app/bin/doctrine
```

</details>

