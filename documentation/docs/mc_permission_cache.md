# Cache de Permissões (pcache)
O pcache é utilizado pela API de leitura do Mapas Culturais para agilizar a busca de entidades quando são utilizados filtros por permissões de usuários, exemplo quando queremos obter todos os espaços que um determinado usuário tem permissão de editar. 

Esta consulta seria muito penosa sem o pcache porque seria necessário recuperar todos os espaços do banco de dados e perguntar um a um se o determinado usuário tem essa permissão, o que por si só já é uma operação um pouco complexa que depende de diversos fatores, como por exemplos se o usuário tem permissão de editar o espaço pai ou se tem permissão de editar o agente que publicou o espaço.

Para resolver este problema foi criado o pcache, que se trata de uma tabela que relaciona usuários, entidades e as permissões deste usuário com a entidade:

```SQL
 id  | user_id |             action             |            object_type          | object_id |  create_timestamp  
-----+---------+--------------------------------+---------------------------------+-----------+--------------------
   1 |       2 | @control                       | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   2 |       2 | view                           | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   3 |       2 | create                         | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   4 |       2 | modify                         | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   5 |       2 | remove                         | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   6 |       2 | viewPrivateFiles               | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   7 |       2 | changeOwner                    | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   8 |       2 | viewPrivateData                | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
   9 |       2 | createAgentRelation            | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
  10 |       2 | createAgentRelationWithControl | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
  11 |       2 | removeAgentRelation            | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
  12 |       2 | removeAgentRelationWithControl | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
  13 |       2 | createSealRelation             | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
  14 |       2 | removeSealRelation             | MapasCulturais\Entities\Project |         1 | 2018-09-24 19:29:16
```

Manter esta tabela atualiza é uma operação complexa e que em determinadas situações exige bastante poder de processamento e pode demorar um tempo considerável. Para não deixar o usuário esperando enquanto navega pelo sistema, foi criado um script (`/scripts/recreate-pending-pcache.sh`) que regera os caches para as entidades modificadas. Este script deve ser executado periodicamente. 

Há também um script (`/scripts/recreate-pcache.sh`) que apaga e recria todo o pcache que só deve ser utilizado em situações em que se suspeita que a tabela esteja corrompida.

## Configurando o cron para executar o script de manutenção

Para rodar o script a cada 5 minutos, adicione a linha abaixo ao crontab utilizando o comando `crontab -e`:

```SHELL
*/5 * * * * /path/to/mapasculturais/scripts/recreate-pending-pcache.sh
```

## Configurando o intervalo de execução dentro das imagens docker

Nas imagens docker não é utilizado o cron para este agendamento. Há um processo que roda em background que aguarda um intervalo entre o final da execução do script e o inicio da nova execução. Por padrão este intervalo é de 60 seguntos mas pode ser configurado pela variável de ambiente `PENDING_PCACHE_RECREATION_INTERVAL` 
