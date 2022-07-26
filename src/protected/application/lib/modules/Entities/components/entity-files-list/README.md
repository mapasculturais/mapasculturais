# Componente `<files-list>`
Mostra os links para download
  
## Propriedades
- **files**: *Array* - obrigatório -  Array com os arquivos para download `files.downloads`;
- **título**: *String* - obrigatório - Título do componente;

### Importando componente
```PHP
<?php 
$this->import('files-list');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem dos links para download-->
<files-list title="Arquivos para download" :files="entity.files.downloads"></files-list>

```
