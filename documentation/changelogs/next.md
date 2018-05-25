* Corrige campo de bairro quando se utiliza um CEP geral de uma cidade e a informação de bairro vem vazia
* Adicona ao "describe" da API a lista dos grupos de arquivos disponíveis para o seletor @files.

* Faz com que a pesquisa por palavra-chave para eventos seja executada  usando o nome do espaço e o nome do evento.
* Corrige a posição dos marcadores de possição no mapa.
* Corrige os erros de interface para validação de campos de data e retorno JS de edição de ocorrências pendentes por usuários não-admin (Issue #1111)
* Adicona ao "describe" da API a lista dos grupos de arquivos disponíveis para o seletor @files.
* Altera a propriedade `app.geoDivisionsHierarchy` no config, adicionando o atributo `[showLayer]` para isso alterando a representação da estrutura de dados.
* Acrescenra taxonomias personallizadas na extração de planilhas
* Na página de inscrição em uma oportunidade, o campo "Agente responsável" já vem com o Agente Padrão do usuário logado preenchido
* Adiciona opção para ordenação na busca quando a opção de visualização por lista é selecionada.
* Após fazer login, redireciona usuário para página em que estava navegando
* Envia e-mails com as mensagens de denúncia apenas para os admins do subsite, e e-mails das mensagens de contato para os admins do subsite, a entidade e responsável  
* No perfil do agente, exibe os grupos que ele faz parte em relacionamentos com outras entidades
* Faz com que os temas presente no diretório `themes` sejam ativados automaticamente.
* Adiciona o campo referente ao shortcuts (singleUrl por exemplo) ao chamar a API describe.