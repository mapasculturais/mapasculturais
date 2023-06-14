exemplo de uso:

```PHP
colocar na configuração de plugins:
            'SubsiteDomainSufix' => [
                'namespace' => 'SubsiteDomainSufix', 
                'config' => [
                    'sufix' => function () {
                        return 'domain-sufix.gov.br';
                    }
                ]
            ]
```
