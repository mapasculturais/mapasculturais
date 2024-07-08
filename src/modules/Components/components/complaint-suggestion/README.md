# Componente `complaint-suggestion`
Este componente Vue.js permite aos usuários enviar denúncias ou sugestões, com a opção de anonimato e verificação reCAPTCHA. Ele apresenta dois modais distintos: um para enviar denúncias e outro para enviar sugestões. O usuário pode optar por enviar a mensagem de forma anônima e também pode escolher receber uma cópia da mensagem enviada.

## Propriedades
- entity: Objeto da entidade que o componente manipula (obrigatório).
- classes: Classes CSS adicionais para customização (opcional).

## Data
- isAuth: Indica se o usuário está autenticado.
- typeMessage: Tipo da mensagem sendo enviada.
- sitekey: Chave do site para o reCAPTCHA.
- definitions: Definições das notificações.
- recaptchaResponse: Resposta do reCAPTCHA.
- formData: Dados do formulário para envio.
- options: Opções disponíveis para tipos de denúncia e sugestão.

## Methodo 
- send(): Envia os dados do formulário para o backend.
- verifyCaptcha(response): Verifica a resposta do reCAPTCHA.
- expiredCaptcha(): Reseta a resposta do reCAPTCHA quando expira.
- validade(objt): Valida os dados do formulário.
- initFormData(type): Inicializa os dados do formulário com base no tipo de mensagem.

### Importando componente
Para utilizar o componente, inclua o seguinte código em seu template HTML:

```PHP
<?php 
$this->import('complaint-suggestion');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<complaint-suggestion :entity="entity"></complaint-suggestion>

```
## Descrição dos Elementos
Modal de Denúncia
- Título do Modal: denúncia
- Campos do Formulário:
- Anonimato: Checkbox para enviar a denúncia de forma anônima.
- Nome: Campo de texto para o nome do remetente, exibido se não for anônimo.
- E-mail: Campo de texto para o e-mail do remetente, exibido se não for anônimo ou se optar por receber uma cópia.
- Tipo: Select para escolher o tipo de denúncia.
- Mensagem: Textarea para a mensagem da denúncia.
- Receber Cópia: Checkbox para optar por receber uma cópia da mensagem, desabilitado se anônimo.

## Ações
- reCAPTCHA: Verificação reCAPTCHA.
- Enviar: Botão para enviar a denúncia.
- Cancelar: Botão para cancelar e fechar o modal.
- Botão do Modal: Botão para abrir o modal de denúncia.

## Modal de Sugestão
- Título do Modal: Contato
- Campos do Formulário
- Anonimato: Checkbox para enviar a sugestão de forma anônima.
- Nome: Campo de texto para o nome do remetente, exibido se não for anônimo.
- E-mail: Campo de texto para o e-mail do remetente, exibido se não for anônimo ou se optar por receber uma cópia.
- Tipo: Select para escolher o tipo de sugestão.
- Mensagem: Textarea para a mensagem da sugestão.
- Enviar Somente para o Responsável: Checkbox para enviar a mensagem apenas para o responsável.
- Receber Cópia: Checkbox para optar por receber uma cópia da mensagem, desabilitado se anônimo.

## Ações de Sugestão
- reCAPTCHA: Verificação reCAPTCHA.
- Enviar: Botão para enviar a sugestão.
- Cancelar: Botão para cancelar e fechar o modal.
- Botão do Modal: Botão para abrir o modal de sugestão.