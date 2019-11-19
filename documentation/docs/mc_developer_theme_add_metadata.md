# Adicionando campos adicionais ao seu tema

Ao estender o tema padrão do Mapas Culturais para personalizá-lo a sua realidade local ou setorial, além de poder  modificar cores, textos e layouts, você também pode ter a necessidade de criar campos adicionais. Por exemplo, a plataforma [Museus BR](http://museus.cultura.gov.br), do Instituto Brasileiro de Museus, cataloga muitos dados adicionais, específicos para os museus, além das informações básicas já presentes por padrão na plataforma.

Para fazer isso no seu tema, são necessário basicamente 3 passos simples:

1. [Registrar os novos metadados](#registrar-os-novos-metadados)
3. [Criar um novo template para o campo](#criar-um-novo-template-para-o-campo)
2. [Carregar novo template](#carregar-novo-template)


## Registar novos metadados

Para isso, dentro do método register() no arquivo classe Theme do arquivo Theme.php do seu tema, chame o método correspondente: _registerAgentMetadata()_, _registerSpaceMetadata()_, _registerEventMetadata()_, _registerProjectMetadata()_ ou _registerSealMetadata()_.

Esses métodos recebem dois parâmetros: _$key_ e _$cfg_.

**_$key_** (string) é um identificado único para este metadado. Utilize nomes sem acentos ou espaços e, para evitar conflitos, utilize um prefixo com o nome do seu tema. Ex: 'meutema_cor_preferida'.

**_$cfg_** (array) Array contendo as configurações do campo.

Exemplo:

```PHP

<?php
namespace MeuTema;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

class Theme extends BaseV1\Theme{

    function register() {
        $this->registerAgentMetadata('meutema_cor_preferida', array(
            'label' => 'Qual sua cor preferida?',
            'type' => 'select',
            'options' => ['Amarelo', 'Vermelho', 'Azul', 'Sou daltônico, não ligo muito pra isso']
        ));

    }

}
```
Exemplo adicionando um metadado em vários tipos de entidade:

```PHP

    function register() {
        $teste = array(
            'label' => 'Justificativa',
            'type' => 'text',
        );
        $this->registerAgentMetadata('meutema_justificativa', $teste);
        $this->registerEventMetadata('meutema_justificativa', $teste);
        $this->registerSpaceMetadata('meutema_justificativa', $teste);

    }

```
Você também pode definir regras de validação para o campo, utilizando as opções da classe [Validation](https://github.com/Respect/Validation), consulte sua [documentação](https://github.com/Respect/Validation/blob/master/docs/VALIDATORS.md) para todas as possibilidades.
```PHP

    function register() {
        $this->registerAgentMetadata('meutema_lattes', array(
            'label' => 'Link do currículo Lattes',
            'type' => 'string',
            'validations' => [
                    'v::url()' => 'Url inválida'
                ]
        ));

        $this->registerSpaceMetadata('meutema_geladeiras', array(
            'label' => 'Número de geladeiras',
            'type' => 'string',
            'validations' => [
                    'v::intVal()' => 'O valor deve ser um número inteiro'
                ]
        ));
    }

```

Abaixo as opções e possibilidades de valores para a configuração do campo (em contrução):

**_label_** Nome de exibição do campo

**_type_** Tipo do campo. Por padrão, é exibido uma caixa de texto simples, mas alguns outros tipos já exibem campos diferentes no formlário. Valores possíveis: string (padrão), text, select, multiselect

**_validations_** Array com métodos de validação que devem ser acionados, sendo a chave o método e o valor a mensagem de erro. Veja exemplo acima.

**_options_** Array com as opções para os campos select e multiselect

**_private_** (bool) Padrao para false. Indica se o campo é ou não privado

**_allowOther_** (bool) Padrão para falso. Para campos select e multiselect, adiciona a opção 'outro' caso seja definido como true, que abre uma caixa de texto para inserção de um novo valor.

**_allowOtherText_** Caso allowOther seja true, define o texto do novo campo de texto. Por ex: "Especifique a cor"

## Criar um novo template para o campo

Agora que já registramos o metadado, precisamos criar um template que vai exibí-lo. Para isso vamos criar um novo arquivo e salvá-lo na pasta layouts/parts do nosso tema. O conteúdo deste arquivo vai seguir o mesmo padrão dos outros campos, mas aqui você tem toda liberdade para exibir o campo da maneira que quiser.

Por exemplo, abaixo o conteúdo do arquivo layouts/parts/cor-preferida.php.

```PHP
<?php if($this->isEditable() || $entity->meutema_cor_preferida): ?>
<p>
    <span class="label">Cor preferida:</span>
    <span class="js-editable" data-edit="meutema_cor_preferida" data-original-title="Cor preferida" data-emptytext="Selecione">
        <?php echo $entity->meutema_cor_preferida; ?>
    </span>
</p>
<?php endif; ?>
```

## Carregar novo template

Agora basta carregarmos a parte no ponto em que desejarmos do tema. Para isso, utilizaremos os template hooks.

No tema BaseV1 existem hooks (ganchos) em várias partes do código onde vocẽ pode inserir ações sem precisar sobreescrever o arquivo. Essas ações devem ser colocadas no método __init__ da classe Theme do seu Theme.php. Veja os exemplos abaixo.

Para inserir nosso campo como a primeira coisa da aba "sobre" dos agentes.

```PHP

    $app->hook('template(agent.<<create|edit|single>>.tab-about):begin', function(){
        $this->part('cor-preferida', ['entity' => $this->data->entity]);
    });

```
Para inserir o novo campo como última coisa da aba sobre de vários tipos de entidade

```PHP

    $app->hook('template(<<agent|event|space|project>>.<<create|edit|single>>.tab-about):end', function(){
        $this->part('cor-preferida', ['entity' => $this->data->entity]);
    });

```
