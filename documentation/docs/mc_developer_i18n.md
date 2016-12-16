# Internacionalização

A internacionalização do Mapas Culturais é feita usando o padrão de arquivos .po e .mo, dentro do padrão Gettext.

## Arquivos de tradução

Os arquivos de tradução estão localizados na pasta [src/protected/application/translations](../../src/protected/application/translations) e
seguem o padrão de nomenclatura segundo seus locales.

Para cada idioma, deve haver um par de arquivos, por exemplo:

```
es_ES.po
es_ES.mo
```

## Definindo a linguagem do site

Por padrão, Mapas Culturais roda em português. Para alterar o idioma, basta modificar a opção *app.lcode* no seu config. Por exemplo:

```
'app.lcode' => 'es_ES',
```

## Funções de tradução

A internacionalização é feita pela classe *MapasCulturais\i*. Existem métodos para imprimir e retornar strings traduzidas. 

As strings do core devem estar em português para serem traduzidas para outros idiomas por meio do arquivo .PO.

Abaixo vemos os principais métodos:

### _e($string, $domain = 'defalut')

Imprime a string traduzida.

Exemplos:

```PHP
<h1><?php \MapasCulturais\i::_e('Meu título'); ?></h1>
```

```PHP
<h1><?php \MapasCulturais\i::_e('Meu título', 'dominio-do-meu-plugin'); ?></h1>
```


### __($string, $domain = 'defalut')

Retorna a string traduzida.

Exemplo:

```PHP
$title = \MapasCulturais\i::__('Meu título');
```


### esc_attr_e($string, $domain = 'defalut')

Imprime a string traduzida e tratada com htmlspecialchars(). Útil para strings que ficam dentro de atributos HTML.

Exemplo:

```PHP
<a href="#" title="<?php \MapasCulturais\i::esc_attr_e('Meu título'); ?>">
    <?php \MapasCulturais\i::_e('Meu título'); ?>
</a>
```


### esc_attr__($string, $domain = 'defalut')

Retorna a string traduzida e tratada com htmlspecialchars(). Útil para strings que ficam dentro de atributos HTML.

Exemplo:

```PHP
$title = \MapasCulturais\i::esc_attr__('Meu título');
```


### _n($stringSingular, $stringPlural, $numero, $domain = 'default')

Retorna a string traduzida no singular ou no plural, dependendo do número.

```PHP
<?php echo \MapasCulturais\i::_n('E-mail enviado', 'E-mails enviados', $count_emails); ?></h1>
```


## Traduzindo javascript

Para traduzir strings dentro de arquivos javascript, devemos passar um objeto com as strings através do método *localizeScript*.
As strings passadas através deste método estarão disponíveis no objeto javascript MapasCulturais.gettext.

Se quiser ver um exmemplo prático completo, veja [este commit modelo](https://github.com/hacklabr/mapasculturais/commit/6a5ab14365365166ff3e5c83a1b055107cece2b3).

Exemplo:

No seu Theme.php 
```PHP
$this->localizeScript('MeuGrupo', [
    'Minha String' => \MapasCulturais\i::__('Meu título')
]);
```

E no seu arquivo Javascript
```Javascript
alert(MapasCulturais.gettext.MeuGrupo['Minha String']);
```

Dica: utilize um grupo para cada arquivo Javascript e faça a chamada do método localizeScript logo após o enqueueScript. Dessa maneira o código fica mais organizado.


## Internacionalizando temas e plugins

Cada tema ou plugin que não faz parte do core do Mapas Culturais deve ter seu próprio arquivo .po para que possam
ser internacionalizados.

Para fazer isso é necessário:

1. Regitrar um novo domínio de tradução
2. Usar os métodos de tradução, passando o domínio

No Theme.php do seu tema ou no Plugin.php chamar o método *load_textdomain()* para registrar seus arquivos .po e .mo.

Este método recebe dois parâmetros. O nome do domínio e o caminho para a pasta onde ficam os arquivos .po e .mo do seu tema ou plugin.

```PHP
\MapasCulturais\i::load_textdomain( 'dominio-do-meu-plugin', __DIR__ . "/translations" );
```

Neste caso, há uma pasta "translations" dentro da pasta do tema ou do plugin, onde ficam os arquivos de tradução seguindo os mesmos
padrões de nomenclatura dos arquivos principais.

Após registrar seu domínio, basta passar o domínio como segundo parâmetro das funções de tradução;

```PHP
<h1><?php \MapasCulturais\i::_e('Meu título', 'dominio-do-meu-plugin'); ?></h1>
```



## Dicas e melhores práticas

(Esta documentação está em construção, acrescente dicas aqui)

Ao incluir strings internacionalizadas, sempre tente facilitar a vida do tradutor. Lembre-se que ele vai ver aquela string
no meio de uma lista com um monte de strings, e com muito pouca informação do contexto onde ela aparece.

Para isso, algumas dicas:

### Sempre prefira frases inteiras

Se você tem uma variável, um link, ou algum outro código no meio da string, você deve evitar situações como essa:

```PHP
<?php \MapasCulturais\i::_e('Você possui'); ?> <?php echo $number; ?> <?php \MapasCulturais\i::_e('dias para resolver isso.'); ?>
```

Se você fizer isso, as frases "Você possui" e "dias para resolver isso" ficarão totalmente independetes e desconectadas. Na hora de traduzir, o tradutor não saberá que isso
não faz parte de uma mesma sentença e elas parecerão frases sem sentido.

Prefira usar uma frase só, utilizando as funções printf() e sprintf() do php. Veja como ficaria:

```PHP
<?php printf(\MapasCulturais\i::__('Você possui %s dias para resolver isso.'), $number); ?>
```


### Não concatene um "s" para criar plural

Utilize as funções que lidam com plurais e mantenha sempre as palavras inteiras nas funções de tradução.

### Adicione comentários para os tradutores

As vezes algumas palavras podem ser ambíguas e sem significados quando isoladas.

Por exemplo, "de" pode significar que é *de alguém*, ou pode significar *a partir de*.

Se você quiser passar um contexto para o tradutor, pode usar um comentário PHP na linha acima da chamada php. Esses comentários
aparecem para ele dentro do editor PoEdit. Faça assim:

```PHP
<?php

// Translators: canto de um passáro, e não canto da mesa
$text = \MapasCulturais\i::__('Canto');

?>
```
