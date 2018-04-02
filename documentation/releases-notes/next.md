
## Arquivos privados para as inscrições

Nesta versão adicionamos a capacidade de gravar os arquivos enviados nos formulários de inscrição em uma pasta privada, não acessível via web. 

Dessa maneira, os arquivos ZIP gerados pelas inscrições não são acessíveis por usuários sem as permissões adequadas, mesmo que eles tenham a URL do arquivo.

Para isso, é preciso criar esta pasta para os arquivos privados.

Por padrão, esta pasta fica na raíz do repositório, no mesmo nível da pasta `src` (e por isso inacessível via web) e é chamada `private-files`.

Esta configuração pode ser alterada utilizando a chave `private_dir` do config.

Apenas como referência, esta é a declaração do valor padrão dessa opção, presente em `Storage/FileSystem.php`:

```
protected function __construct(array $config = []) {
    $this->config = $config + [
        'dir' => BASE_PATH . 'files/',
        'private_dir' => dirname(BASE_PATH) . '/private-files/',
        'baseUrl' => 'files/'
    ];
}
```

Recapitulando, é necessário criar a pasta `private-dir`, na raíz do repositório, com as permissões adequadas para que o servidor web possa escrever nela (as mesmas da pasta `files` que já existe dentro de `src`). Caso queira que essa pasta fique em outro lugar, modifique o config.

IMPORTANTE: Criar a pasta antes de rodar o script de deploy. No `db-updates`, os arquivos de inscrições antigas serão movidos para esta nova pasta.
