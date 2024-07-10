# Componente `<error-display>`
O componente `error-display` é utilizado para exibir mensagens de erro em uma interface de usuário, integrando um modal para fornecer detalhes e ações para o usuário. Ele utiliza o componente `VueRecaptcha` para validação adicional, se necessário.

### Propriedades
- *String **error*** - Mensagem de erro a ser exibida

### Slots
- **actions** - Customiza as ações do modal de erro

### Importando componente
```PHP
<?php 
$this->import('error-display');
?>
```
### Exemplos de uso
```HTML
<!-- Utilização básica -->
<error-display error="Ocorreu um erro ao processar sua solicitação."></error-display>

<!-- Customizando ações do modal -->
<error-display error="Você não tem permissão para acessar esta página.">
    <template #actions="modal">
        <button class="button button--primary" @click="redirectToHome(modal)">Voltar para a página inicial</button>
        <button class="button button--text button--text-del" @click="modal.close()">Cancelar</button>
    </template>
</error-display>
```