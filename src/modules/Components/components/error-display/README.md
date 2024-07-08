# Componente `<error-display>`
Lista informações sobre o error-display

## Descrição
- O componente error-display é utilizado para exibir mensagens de erro de forma amigável ao usuário. Ele utiliza o mc-modal para mostrar os detalhes do erro em uma janela modal.
- O template define a estrutura HTML do componente error-display. Ele utiliza o componente mc-modal para exibir a mensagem de erro em uma janela modal.
- mc-modal: Um componente modal que exibe um título e uma área de conteúdo onde a mensagem de erro será mostrada. O título do modal é definido como "Erro 403".

# Propriedades
Error:
- Tipo: String
- Obrigatório: true
- Descrição: Representa a mensagem de erro a ser exibida.

# Métodos
VerifyError:
- Descrição: Método reservado para verificar ou tratar o erro. Atualmente, está vazio e pode ser implementado conforme necessário.

# Slots
O componente mc-modal utilizado no template contém dois slots:
Content:
- Descrição: Área principal do modal onde o conteúdo (mensagem de erro) será exibido.

# Actions
- Descrição: Slot para botões de ação do modal. Contém dois botões:
- Voltar para a página inicial: Um botão primário que, ao ser clicado, chama o método send(modal).
- Cancelar: Um botão secundário que fecha o modal ao ser clicado (modal.close()).

# Importando componente
```PHP
<?php 
$this->import('error-display');
?>
```
# Exemplo de Uso 
```HTML
<!-- utilizaçao básica -->
<error-display error="Você não tem permissão para acessar esta página."></error-display>

```
Neste exemplo, o componente error-display é usado para exibir uma mensagem de erro personalizada ao usuário.

# Notas
- O componente VueRecaptcha é listado em components mas não é utilizado no template atual. Ele pode ser removido ou utilizado conforme a necessidade do projeto.
- O método verifyError está presente, mas não contém nenhuma lógica. Você pode implementar a verificação ou o tratamento de erro específico conforme necessário.
- O slot content no modal atualmente exibe o texto "Teste". Substitua este texto pelo conteúdo desejado ou utilize a propriedade error para exibir a mensagem dinamicamente.