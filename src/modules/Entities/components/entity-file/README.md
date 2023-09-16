# Componente `<entity-file>`
Componente resposanvel por subir um unico arquivo
  
## Propriedades
- *Entity **entity** - Entidade que receberá o arquivo
- *String **groupName** - Define o grupo de arquivos que foi registrado
- *String **title = ''** - Título do componente
- *String **uploadFormTitle = ""** - Título da popover do envio do arquivo
- *Boolean **required = false** - Quando true, exibe o asterisco de obrigatório e não exibe botão para remover o arquivo
- *Boolean **editable = false** - Quando true, coloca o componente em modo de desenvolvimento
- *Boolean **disableName = false** - Quado passado true, não pergunta o nome do arquivo e usa o filename 
- *Boolean **enableDescription = false** - Quando definido como true deve exibir um textarea para definir o description
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-file');
?>
```
### Exemplos de uso
```PHP
<!-- MODO DE EDIÇÃO -->
<entity-file :entity="entity" groupName="groupo_registrado_do_arquivo" title="Titulo do componente" editable></entity-file>

<!-- MODO DE VISUALIZAÇÃO -->
<!--OBS.: No modo de visualização só será exibido conteudos se existir algum arquivo no grupo -->
<entity-file :entity="entity" groupName="groupo_registrado_do_arquivo" title="Titulo do componente"></entity-file>

```
