Desenvolvimento com Docker
==========================

Por enquanto o Docker é utilizado somente para auxiliar o processo de
desenvolvimento da plataforma.

Postgres
--------

Você pode usar qualquer imagem com postgis que tenha os requisitos da
aplicação instalados. Usamos esta abaixo para testes.

```shell
$ docker pull mdillon/postgis
$ docker run --name postgis -p 5432:5432 -e POSTGRES_PASSWORD=postgis -d mdillon/postgis
```

Dica: se você precisa inspecionar tabelas com `psql`, você pode fazer de 2 formas:

1. Rodar `psql` a partir da sua máquina para conectar ao container

```shell
$ PGPASSWORD=postgis psql -U postgres -d postgres
```

2. Usar o `psql` do container

```shell
$ docker exec -it postgis psql
```


Criando imagem do mapas
-----------------------

```shell
$ docker build -t mapasculturais .
```

Rodando o container do mapas
----------------------------

Assumo que se está na raiz do projeto

```shell
$ docker run --name mapasculturais --link postgis:postgis \
         -v $PWD:/srv/mapasculturais -i -p 80:8000  -t mapasculturais
```

Acesse seu browser em `http://localhost`.
