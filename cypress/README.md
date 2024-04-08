# MapaCultural E2E Tests

Este repositório, agora integrado ao código principal da aplicação MapaCultural versão 7, contém os testes End-to-End (E2E) para a aplicação, acessível em [https://mapasculturais.secult.ce.gov.br](https://mapasculturais.secult.ce.gov.br).

## Pré-requisitos

- **NPM**: Você precisa ter o NPM instalado para gerenciar os pacotes necessários para os testes. Visite [https://www.npmjs.com/get-npm](https://www.npmjs.com/get-npm) para instruções de instalação.

## Instalação

Siga os passos abaixo para configurar o ambiente de teste junto com o código principal em sua máquina local:

1. Clone o repositório principal, que agora inclui os testes E2E:

   ```sh
   git clone https://github.com/secultce/mapacultural.git
   
Ou, se você preferir usar **SSH**: 

**git clone git@github.com:secultce/mapacultural.git**

Entre no diretório do repositório clonado:

Entre em
**cd mapacultural**
Instale os pacotes NPM necessários: **npm install**.

## Execução dos Testes
Para executar os testes E2E integrados, utilize o seguinte comando: **npx cypress open**

Isso abrirá a interface do Cypress, onde você pode executar os testes interativamente.

## Contribuição
Para contribuir com melhorias ou correções nos testes:

Crie um branch com um nome descritivo baseado no tipo de contribuição, por exemplo:

**feature/add-new-test**

**bugfix/login-issue**

**enhancement/refactor-test-code**

Faça suas alterações e submeta um Pull Request para este repositório.

## Documentação Cypress
Para mais informações sobre como criar e executar testes automatizados com o Cypress, consulte a documentação oficial do Cypress.

Visite a [Documentação oficial do Cypress](https://docs.cypress.io/guides/overview/why-cypress) para mais informações.

Agradecemos pela colaboração e contribuição para manter a qualidade da aplicação MapaCultural!