## Mapas Culturais > Migração

Para migrar o mapas culturais de servidor, esteja atento aos seguintes pontos:

1 - Certifique-se de que o servidor de origem e destino correspondem as especificações da aplicação

Para verificar a versão de uma determinada versão gnu/linux, o comando abaixo pode ajudar: 

```
$ cat /etc/*-release 
```

2 - Faça instalação dos [requisitos mínimos de software](https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_deploy.md)

3 - Compacte a aplicação em produção

```
$ tar -czvf 2016_09_06_mapasculturais.tgz diretorio/mapasculturais
```

4 - Faça um dump da base de dados

```
$ pg_dump mapas > 2016_09_06_mapas.sql
```

5 - Transfira os arquivos para o novo servidor (recomendamos usar scp)

```
$ scp 2016_09_08_mapas.sql USUARIO-DA-MAQUINA-DE-DESTINO@IP-MAQUINA-DE-DESTINO:/srv/mapas/
$ scp 2016_09_06_mapasculturais.tgz USUARIO-DA-MAQUINA-DE-DESTINO@IP-MAQUINA-DE-DESTINO:/srv/mapas/
```

6 - Reinicie os serviços

```
# service php5-fpm restart
# service nginx 
```
