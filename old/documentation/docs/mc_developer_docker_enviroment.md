Desenvolvimento com Docker
==========================

Por enquanto o Docker é utilizado somente para auxiliar o processo de
desenvolvimento da plataforma.

*ATENÇÃO* Essa documentação funciona apenas para levantar o ambiente de
desenvolvimento, ou seja, o Docker ainda não levanta o ambiente de produção.
Se você quer ajudar com isso, entre em contato em (chat.mapasculturais.org)

Docker
------

Os arquivos do Docker estão no diretório "docker/dev" na raíz do projeto, sendo
um Dockerfile e um Dockerfile-db, responsáveis por configurar respectivamente
a imagem do sistema web e do banco de dados.


Docker Compose
--------------

O arquivo docker-compose.dev.yml está no diretório raiz do projeto. O projeto
está usando a porta 8090 para o host do ambiente web, para alterar basta
alterar o item "ports" no arquivo docker-compose.dev.yml de "8090:80" para
"[porta]:80".


Levantar o ambiente com Docker Compose
--------------------------------------

Para criar o ambiente é necessário ter o docker e o docker-compose instalado
no host, então basta executar o seguinte comando para criar as imagens.

```shell
$ docker-compose -f docker-compose.dev.yml build
```

Para criar os containers e executar o servidor deve ser utilizado o seguinte
comando.

```shell
$ docker-compose -f docker-compose.dev.yml up
```

A primeira vez que o comando for executado ele irá gerar as imagens docker,
caso o comando de build não tenha sido executado, em seguida ele executa o
servidor.

Também é possível criar os containers fazendo com que antes sejam criadas as
imagens através do seguinte comando.

```shell
$ docker-compose -f docker-compose.dev.yml up --build
```

Ao final do processo, basta acessar o link "localhost:8090", para acessar o site
do mapas.


Importar um dump para o banco de dados
--------------------------------------

Inicialmente certifique-se de que as imagens docker foram criadas, pode ser
verificado através do seguinte comando.

```shell
$ docker images
```

Então deve-se criar o container do banco de dados e permitir que esse container
tenha acesso ao arquivo de dump, para isso coloque o arquivo de dump na raiz do
projeto, em seguida execute o seguinte comando.

```shell
$ docker-compose run -v "$PWD":/var/lib db
```

Identifique o ID do container que foi criado através do seguinte comando.

```shell
$ docker ps -a
```

Execute o seguinte comando para dar um start no container.

```shell
$ docker start [CONTAINER ID]
```

Acesse o container através do seguinte comando.

```shell
$ docker exec -it [CONTAINER ID] bash
```

Estando dentro do container vá para o diretório /var/lib.

```shell
$ cd /var/lib
```

Deve-se recriar o banco de dados através do seguinte comando.

```shell
$ dropdb -U mapas mapas && createdb -U mapas -T template0 mapas
```

Agora basta rodar o seguinte comando para importar o dump.

```shell
$ psql -U mapas -f [ARQUIVO DO DUMP]
```
