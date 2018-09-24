# Caches de Permissão (pcache)
O pcache é utilizado pela API de leitura do Mapas Culturais para agilizar a busca de entidades quando são utilizados filtros por permissões de usuários, exemplo quando queremosobter todos os espaçõs que um determinado usuário tem permissão de editar. 

Esta consulta seria muito penosa sem o pcache porque seria necessário recuperar todos os espaços do banco de dados e perguntar um a um se o determinado usuário tem essa permissão, o que por si só já é uma operação um pouco complexa pois depende de diversos fatores, como por exemplos se o usuário tem permissão de editar o espaço pai ou se tem permissão de editar o agente que publicou o espaço.

Para resolver este problema foi criado o pcache, que se trata de uma tabela que relaciona usuários, entidades e as permissões deste usuário com a entidade:

```SQL
 id  | user_id |             action             |  create_timestamp   |            object_type              | object_id 

   1 |       2 | @control                       | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   2 |       2 | view                           | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   3 |       2 | create                         | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   4 |       2 | modify                         | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   5 |       2 | remove                         | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   6 |       2 | viewPrivateFiles               | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   7 |       2 | changeOwner                    | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   8 |       2 | viewPrivateData                | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
   9 |       2 | createAgentRelation            | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
  10 |       2 | createAgentRelationWithControl | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
  11 |       2 | removeAgentRelation            | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
  12 |       2 | removeAgentRelationWithControl | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
  13 |       2 | createSealRelation             | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
  14 |       2 | removeSealRelation             | 2018-09-24 19:29:16 | MapasCulturais\Entities\Project     |         1
```

Manter esta tabela atualiza é uma operação complexa e que em determinadas situações exige bastante poder de processamento e pode demorar um tempo considerável. Para não deixar o usuário esperando enquanto navega pelo sistema, foi criado um script (`/scripts/recreate-pending-pcache.sh`) que regera os caches para as entidades modificadas. Este script deve ser executado periodicamente. 

Há também um script (`/scripts/recreate-pcache.sh`) que apaga e recria todo o pcache que só deve ser utilizado em situações em que se suspeita que a tabela esteja corrompida.

## Configurando o cron para executar o script de manutenção
execute o comando `crontab -e` e adicione a linha abaixo:

```SHELL
*/5 * * * * /path/to/mapasculturais/scripts/recreate-pending-pcache.sh
```