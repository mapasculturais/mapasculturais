# Mapa Cultural do Ceará

## Instruções para instalação

1 - clone o repositório:

```shell
git clone https://github.com/secultce/mapacultural.git
```

ou

```shell
git clone git@github.com:secultce/mapacultural.git
```

2 - Configure a variável BASE_URL do arquivo .env

```
BASE_URL=http://ip-da-sua-máquina/
```
3 - Suba os Containers

```
docker compose up -d
```
4 - Verifique os logs

```
docker compose logs -f
```

5 - Abra o navegador com o endereço usado no BASE_URL que foi utilizado no passo 2.

Login:
```
Admin@local
```

Senha:
```
mapas123
```