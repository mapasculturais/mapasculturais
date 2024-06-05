# Mapa Cultural do Ceará

## Instruções para Criação de Pull Requests

1 - Escolha uma issue

Acesse <https://github.com/secultce/mapacultural/issues> e escolha em qual issue quer trabalhar

2 - Atualize o seu branch principal (develop)

Para isso você já deve ter uma instalação local desse repositório.

Caso não possua uma instalação, [veja aqui como instalar](./INSTALL.md)

Agora, dentro do diretório atualize seu branch:
````shell
git pull origin develop
````

3 - Crie um branch para a resolução da issue

```shell
git checkout -b feat/resolve-tal-coisa
```

4 - Faça os códigos da implementação

Siga as regras da [PSR-12](https://www.php-fig.org/psr/psr-12/), bem como, boas práticas de programação PHP:
- Kiss
- Dry
- Clean Code
- Design Patterns
- etc

5 - Execute os testes e o CS-FIXER

Para garantir que você não quebrou nada, e que o código está de acordo com as regras da aplicação, execute os comandos de STYLE CODE e TESTS que se encontra [nessa seção da documentação](../app/README.md#console-commands)

6 - Faça o commit 

A regra, para melhor organização do repositório, é apenas 1 (um) commit por Pull Request

7 - Abra o pull request

Abra o pull request para o projeto `secultce/mapacultural` e na descrição do PR informe se há algo a ser levado em consideração, bem como, especifique qual issue aquele pull request está atrelado.

> Pronto, agora é só esperar o seu PR ter 2 approves