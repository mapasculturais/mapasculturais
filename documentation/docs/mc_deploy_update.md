# Mapas Culturais > Update

Depois que a instalação está completa. Em alguma hora será necessário atualizar o código fonte da aplicação para adquirir novas funcionalidades que tenham sido adicionadas ao mapa.

A seguir serão descritos os passos para efetuar a atualização com sucesso.

## Atualizando o código fonte

O código pode ser atualizado através de um release, disponível em https://github.com/hacklabr/mapasculturais/releases, ou pode ser atualizado diretamente do branch `v2`.

O primeiro passo é atualizar as referências do novo código (pra saber que existem novas atualização no código e releases), isso pode ser feito utilizando o comando `git fetch`:

```
$ git fetch
```

### Atualizando através de uma TAG (release)

Se for atualizar através de um release, o é `git checkout [release]`, onde `[release]` é o release desejado. A seguir um exemplo fazendo um checkout para a versão "2.1.0":

```
$ git checkout 2.1.0
```

### Atualizando através do branch V2 (versão estável)

Para atualizar o código fonte através do branch V2, primeiro é necessário verificar se o branch é o correto. É possível verificar com o comando `git status`, o resultado deve ser parecido com isto:

```
$ git status
On branch v2
Your branch is up-to-date with 'origin/v2'.

nothing added to commit but untracked files present (use "git add" to track)

```

Se o resultado estiver diferente disso (origin/v2), é possível alterar para o branch utilizando o comando `git checkout [nome_da_branch]`:

```
$ git checkout v2
```

Por fim, o comando `git pull` para atualizar os arquivos:

```
$ git pull
```

### Atualização do Tema

Se sua instalação não utiliza um Tema customizado com repositório do Github, basta prosseguir para o próximo passo.

Se houver alguma atualização no tema que está sendo utilizado, que venha de outro repositório que não seja o Mapas Culturais. Também é necessário entrar na pasta do tema e atualizar o tema utilizando o `git pull` (no exemplo, a pasta do tema é "Macondo"):

```
$ cd src/protected/application/themes/Macondo
$ git pull
```

## Script de Atualização

Após obter o código fonte, o próximo passo é rodar o script `deploy.sh` localizado na pasta `scripts`. Esse arquivo atualiza libs e dependências da aplicação e atualiza o banco de dados com novos campos e/ou tabelas. O comando pra executar esse script (a partir da raiz da aplicação) é:

```
$ ./scripts/deploy.sh
```

## Reiniciando o PHP

Após essa alterações serem efetuadas, é necessário reiniciar o serviço do PHP para que todos os arquivos sejam reescritos corretamente. Isso requer permissão de root.

Se estiver utilizando `php-fpm` (instalação com nginx):

```
# sudo service php-fpm restart
```
Se estiver utilizando o apache, o php está sendo executado através do próprio apache:

```
# sudo service apache2 restart
```

# Possíveis problemas

## Permissão

Todos os comandos (exceto o comando para reiniciar o PHP) devem ser executados com o mesmo usuário da aplicação do mapas. Se algum comando tiver sido executado como root, ou qualquer outro usuário a aplicação pode apresentar problemas diversos. Para alterar os arquivos para o usuário correto, utilize o comando (assumindo que o usuário correto da aplicação se chama `mapas` e o grupo é `www-data`):

```
# chown -R mapas:www-data .
```

## Git Pull

Se algum arquivo da aplicação tiver sido alterado no servidor, essa alteração deve ser descartada para que o `git pull` funcione corretamente. Para reverter um arquivo utilize o comando `git checkout [endereco_do_arquivo]`

```
$ git checkout [endereco_do_arquivo]
```

Para mais detalhe sobre como utilizar o comando veja a documentação em https://git-scm.com/docs/git-pull.


## Chat

Caso o seu problema seja diferente, consulte o Chat do Mapas Culturais em http://chat.mapasculturais.org/channel/general. É possível pesquisar por relatos anteriores do Mapas na **parte superior direita**, no botão "Search".

Se o seu problema não tiver sido relatado por ninguém, basta fazer a pergunta que a comunidade pode ter uma solução para o erro.
