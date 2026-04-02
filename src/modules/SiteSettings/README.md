## Como instalar

### Requisitos Mínimos

MapasCulturais na versão v7.5.25^

### Informações gerais

Atualmente, a plataforma Mapas Culturais oferece duas opções de instalação: uma por meio do repositório MapasCore e outra pelo repositório BaseProject. Em ambos os casos, o resultado final será a plataforma Mapas Culturais.

Recomendamos sempre utilizar o ambiente BaseProject para a instalação, pois ele permite personalizações e adaptações sem a necessidade de modificar diretamente o código do núcleo (Core). Isso garante que futuras atualizações sejam aplicadas sem problemas de compatibilidade.

A seguir, apresentaremos o processo de instalação detalhado para cada caso, garantindo uma implementação do nosso plugin em qualquer ambiente de forma simples e sem complicações.

### Fazer clone do plugin

- Para o ambiente MapasCore, acesse o diretório src/plugins
- Para o ambiente BaseProject, acesse o diretório plugins.
- Em seguida, clone o plugin no diretório utilizando o seguinte comando: 

```shell
git clone https://github.com/ElljSistemas/plugin-OneClick.git OneClick
```
### Configurar o plugin
_Essa configuração é válida tanto para o ambiente MapasCore quanto para o BaseProject._

- No arquivo `docker/common/config.d/plugins.php`, adicione a linha `'OneClick'` para ativar o plugin:

```php
 <?php
    return [
        'plugins' => [
            ....
            "OneClick",
        ]
    ];
```
### Mapeando o plugin no ambiente de desenvolvimento

- No ambiente MapasCore, adicione a seguinte linha ao arquivo `dev/docker-compose.yml` para mapear o plugin: `../src/plugins/OneClick:/var/www/src/plugins/OneClick`

```yml
mapas:
    ....
    volumes:
      - ../src/plugins/OneClick:/var/www/src/plugins/OneClick
      
    environment:
    ....

    depends_on:
     ....
```

- No arquivo `docker/common/config.d/plugins.php`, adicione a linha `'OneClick'` para ativar o plugin:

```yml
  ....
    volumes:
      - ../plugins/OneClick:/var/www/src/plugins/OneClick
      
    environment:
    ....

    depends_on:
     ....
```

## Como utilizar

Para uma documentação de uso mais abrangente acesse
https://elljsistemas.gitbook.io/plugin-oneclick
