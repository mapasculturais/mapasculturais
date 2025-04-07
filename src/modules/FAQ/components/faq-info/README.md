# Componente `<faq-info>`
Adiciona o 'i' de informação para exibir uma pergunta do FAQ


## Propriedades
- *String **path*** - Caminho para a pergunrta, no formato `slug-da-secao->slug-do-contexto->slug-da-pergunta`
- *String **title** = ''* - Sobrescreve o título original da pergunta

### Importando componente
```PHP
<?php 
$this->import('faq-info');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<faq-info :path="slug-da-secao->slug-do-contexto->slug-da-pergunta"></faq-info>

<!-- utilizando um título alternativo para a pergunta -->
<faq-info :path="slug-da-secao->slug-do-contexto->slug-da-pergunta" title="Título alternativo para a pergunta"></faq-info>

```