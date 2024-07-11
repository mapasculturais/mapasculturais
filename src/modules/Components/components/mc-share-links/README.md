# Componente `<mc-share-links>`
O componente `mc-share-links` facilita o compartilhamento de links em redes sociais populares. Ele exibe ícones das redes sociais e ao clicar em um ícone específico, abre uma nova janela para compartilhar o link atual junto com um texto customizável.
  
## Propriedades
- *Title **String*** (opcional) - Título do componente;
- *Text **String*** (opcional) - Texto de compartilhamento;
- *Classes **String*** (opcional) - Classes CSS adicionais a serem aplicadas ao elemento principal do componente.

### Importando componente
```PHP
<?php 
$this->import('mc-share-links');
?>
```
### Exemplos de uso
```PHP
<!-- Utilização Básica -->
<mc-share-links></mc-share-links>

<!-- Personalizando Título e Texto -->
<mc-share-links title="Compartilhe este conteúdo" text="Confira este link incrível!"></mc-share-links>

<!-- Utilizando Classes CSS Adicionais -->
<mc-share-links title="Compartilhe nas redes sociais" text="Veja isso!" :classes="'custom-share-links'"></mc-share-links>
```
