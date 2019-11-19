# Testes Unitários 

## Travis-CI
O Travis-CI é utilizado para automatizar a execução dos teste, a configuração do travis-ci é encontrado no arquivo [/mapasculturais/.travis.yml](../../.travis.yml).


Após o Travis-CI implantar os requisitos, especificados no seu arquivo de configuração ([/mapasculturais/.travis.yml](../../.travis.yml)), para a realização dos testes, o scrpit [run-tests.sh](../../scripts/run-tests.sh) é executado. Através desse script será criado a base de dados **mapasculturais_test** no servidor do Travis-CI e na sequência todos os testes, presente na diretório [tests](../../tests/), serão executados.


## Testes Locais
Os testes também podem serem executados localmente, para isso realize os seguintes passos:

1. Criar ou atualizar o arquivo [conf-test-local.php](../../src/protected/application/conf/conf-test-local.php) (cópia de conf-test.php) com as configurações para utilização da infraestrutura local;

2. Executar o script [run-test-local.sh](../../scripts/run-test-local.sh) 
    * O script possui algumas opções:
        ```
        * Somente executará os testes:
            ./run-test-local.sh --createdb

        * Recriar a base de dados:
            ./run-test-local.sh --createdb        

        * Ajuda sobre outras configurações:
            ./run-test-local.sh --help

        ```
    
    * O script por padrão utiliza as informações de acesso contidas no arquivo [conf-test-local.php](../../src/protected/application/conf/conf-test-local.php) para executar os testes.




### docker
Os testes utilizando os containers docker apresentam passos similares a execução dos testes localmente, apresentando as seguintes diferenças:

A base de testes, mapasculturais_test é criada ao instanciar um novo container da imagem da base dados do mapas culturais ( arquivo [Dockerfile-db](../../docker/dev/Dockerfile-db)), para mais informações sobre como executar os containers dockers veja o documento [guia do docker](../../docs/mc_developer_tests.md). Dessa forma a criação da base utilizando o comando ```./run-test-local.sh --createdb``` é desnecessário.

Para executar os testes, devemos executá-los dentro do container. Para isso, basta executar o seguinte comando:

```

docker exec -it <container (ID ou nome)> <comando>:

docker exec -it mapasculturais_web_1 ./scripts/run-test-local.sh

```


