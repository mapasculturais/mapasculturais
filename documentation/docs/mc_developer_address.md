# Personalizando montagem do endereço

A partir de cada elemento do endereço (rua, númer, bairro) é criado uma string única com o endereço por extenso.

Para alterar a maneira que este endereço é montado, sobreescreva a função MapasCulturais.buildAddress(), declarada em
[customizable.js](../../src/protected/application/themes/BaseV1/assets/js/customizable.js).

No seu tema ou plugin, adicione o novo script:

```PHP
    $this->enqueueScript('custom', 'my-script', 'path-to/my-script.js', array('mapasculturais-customizable'));
```

E siga as instruções em [customizable.js](../../src/protected/application/themes/BaseV1/assets/js/customizable.js)
para fazer sua própria função.
