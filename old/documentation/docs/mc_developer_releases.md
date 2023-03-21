# Fazendo um novo Release

Este é o procedimento de lançamento de uma nova versão do Mapas Culturais.

Quando o desenvolvimento que está sendo realizando na branch `develop` atinge o ponto para se tornar uma nova versão estável, esses são os passos que seguimos:

## Criando e homologando o release

Em primeiro lugar, criamos um release que poderá ser homologado, e ainda terá tempo de ganhar correções de útima hora. Para isso criamos uma branch a partir da `develop`, utilizando o git flow:

```
git flow release start VERSION
```

Onde VERSION é a versão que será criada. Neste projeto, usamos o [versionamento semântico](http://semver.org/), que basicamente diz que:

* Major releases (2.0, 3.0): Quando existe uma mudança de API ou alguma mudança que não mantenha compatibilidade com código anterior e possa quebrar temas e plugins.
* Minor releases (3.1, 3.2): Quando são implementadas novas funcionalidades que não quebram a compatibilidade   
* Patches (3.2.1, 3.2.2): Quando você faz correções de bugs ou de segurança que não quebram a compatibilidade

Uma vez que a branch de homologação está criada, colocamos ela em teste para validarmos que está tudo certo com o release. Se for necessário, podemos fazer novos commits nesta branch com correções de última hora.

## Fazendo o release

Depois de validada, podemos preparar o release da nova versão.

Transferiamos o conteúdo do *change log* e das *release notes* para um arquivo com o nome da versão:

```
cp releases-notes/next.md releases-notes/VERSION.md
cp changelogs/next.md changelogs/VERSION.md
``` 

Em seguida, apagamos o conteúdo dos arquivos `next.md` que estão nessas duas pastas.

Finalmente, fazemos o release, criando uma nova tag, e enviando as modificações para as branches master e develop.

```
git flow release finish VERSION
```

E na sequencia o arquivo `version.txt` é atualizado com o número da versão do release.

Envie tudo para o servidor

```
git push --all
git push --tags
```

[Crie um release](https://github.com/hacklabr/mapasculturais/releases/new) no github a partir da tag recem criada e coloque o changelog na descrição.
