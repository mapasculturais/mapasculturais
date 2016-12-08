# Opções de Geocoding e busca de endereço por código postal

## Geocoding

Geocoding é o processo de buscar coordenadas geográficas a partir de um endereço.

O Mapas Culturais utiliza este processo em dois momentos:

1. Ao editar uma entidade, quando se preenchem os campos de endereço, o sistema busca a localização geográfica para posicionar o ponto no mapa;
2. Na busca, ao fazer uma busca por endereço (a caixa de busca dentro do mapa)

Por padrão, é utilizado o serviço [Nominatim](http://wiki.openstreetmap.org/wiki/Nominatim). Mas a opção de utilizar a API do Google Maps para esta tarefa
pode ser ativada alterando o valor da configuração app.useGoogleGeocode em seu config.php.

```PHP
    'app.useGoogleGeocode' => true,
```

Também é possível, a partir de um tema ou plugin, substituir a ferramenta de geocode por outra de sua preferência.

Para isso, basta sobreescrever o objeto JavaScript MapasCulturais.geocoder, declarado em
[customizable.js](../../src/protected/application/themes/BaseV1/assets/js/customizable.js).

No seu tema ou plugin, adicione o novo script:

```PHP
    $this->enqueueScript('custom', 'my-script', 'path-to/my-script.js', array('mapasculturais-customizable'));
```

E siga as instruções em [customizable.js](../../src/protected/application/themes/BaseV1/assets/js/customizable.js)
para fazer seu próprio geocoder.
