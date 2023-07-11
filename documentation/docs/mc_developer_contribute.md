# Como Contribuir

No desenvolvimento do Mapas Culturais, utilizamos o padrão Git Workflow. Para saber mais, veja a página da ferramenta [Git Flow](https://danielkummer.github.io/git-flow-cheatsheet/).

Recomendamos que você instale o git flow na sua máquina.

## Fazendo um bug fix

Para fazer um Bug fix, faça um branch a partir da branch `master`, que é branch estável.

Caso esteja trabalhando em um fork, faça um pull request para branch `master` do repositorio principal, depois de seguir os passos abaixo em "finalizando sua contribuição".

Se você utiliza o git flow:

```
git flow hotfix start VERSION
```

Onde VERSION é a versão que será criada uma vez que a correção for aceita. De acordo com o versionamento semântico, será sempre um incremento no terceiro dígito. Por exemplo, se a versão estável atual é 3.2.1, será 3.2.2.

## Criando algo novo

Para criar algo novo, ou fazer uma melhoria, faça um branch a partir da branch `develop`.

Caso esteja trabalhando em um fork, faça um pull request para branch `develop` do repositorio principal, depois de seguir os passos abaixo em "finalizando sua contribuição".

Se você utiliza o git flow:

```
git flow feature start FEATURE
```

Onde FEATURE é um nome para sua branch.

## Finalizando sua contribuição

Antes de enviar um Pull request, ou antes de fechar o desenvolvimento de uma nova feature, se atente aos seguintes procedimentos:

### Documentação para releases

Se a modificação que você fez requer algum procedimento especial, ou algum cuidado, na hora em a plataforma for atualizada no servidor de testes, documente isso no arquivo `documentation/releases-notes/next.md`.

Por exemplo, se é necessário criar alguma pasta, rodar algum script, ou se é preciso verificar alguma compatibilidade no tema que a pessoa estiver usando, isso deve estar documentado aí.

### Changelog

Documente o que a sua modificação faz no changelog da aplicação em `documentation/changelogs/next.md`

### Documentação

Confira se o que você fez já está coberto nesta documentação. Se estiver, verifique se a documentação precisa de atualização e a atualize. 

Se criar uma página nova de documentação, atualize o índice em `documentation/mkdocs.yml`, dessa maneira essa nova página vai aparecer no índice navegável em (docs.mapasculturais.org)

