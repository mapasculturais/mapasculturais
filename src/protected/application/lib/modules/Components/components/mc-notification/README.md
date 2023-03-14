# Componente `<__template__>`
A ideia do componente `__template__` é servir de modelo para a criação de novos componentes. Este implementa um exemplo 
de componente que manipula um objeto do tipo `Entity` e dispara um evento quando este for modificado.

Este documento (README.md) deve conter a descrição do que o componente faz e toda a interface pública do componente.

Neste exemplo, o componente recebe obrigatoriamente um nome e uma entidade e opcionalmente um apelido (nickname) e um sobrenome.
Baseado nos parâmetros passados para o componente, este gera um `displayName` e um `fullname`.

Quando o botão "Definir Nomes" é clicado, este definirá o `entity.name` e `entity.nomeCompleto` com o valores `displaName` e `fullname`, 
respectivamente, e emitirá o evento `namesDefined`, que poderá ser capturado fora do componente.

### Eventos
- **namesDefined** - disparado quando o método `defineNames` é chamado, após a definição do `name` e `nomeCompleto`
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **name*** - Nome
- *String **lastname** = ''* - Sobrenome

## Slots
- **default** `{fullname, displayName, compareDisplayName, compareFullname}`: informações dos nomes e comparação com o nome gerado

### Importando componente
```PHP
<?php 
$this->import('__template__');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<__template__ :entity="entity" name="Fulano"></__template__>

<!-- utilizano o evento on-names-defined para salvar a entidade -->
<__template__ :entity="entity" name="Fulano" nickname="Lano" @names-defined="entity.save()"></__template__>

<!-- utilizano o evento on-names-defined para salvar a entidade -->
<__template__ :entity="entity" name="Fulano" nickname="Lano" @names-defined="entity.save()" #default="props">
    <div v-if="props.compareDisplayName"> o nome está atualizado</div>
    <div v-if="!props.compareDisplayName"> o novo nome será <strong>{{props.displayName}}</strong></div>

    <div v-if="props.compareFullname"> o nome completo está atualizado</div>
    <div v-if="!props.compareFullname"> o novo nome completo será <strong>{{props.fullname}}</strong></div>
</__template__>

```