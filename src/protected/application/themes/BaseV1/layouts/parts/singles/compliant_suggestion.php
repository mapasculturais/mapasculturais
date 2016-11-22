<?php
if($this->controller->action === 'create')
    return false;
?>

<div class="denuncia">
    <input class="botao" type="button" name="Envia" value="Denuncie Abusos">
</div>

<form class="form-complaint-suggestion js-compliant-form hidden" action="">
  <p>
    Nome:<br />
    <input type="text" rows="5" name="nome">
  </p>
  <p>
    E-mail:<br />
    <input type="text" rows="5" name="email">
  </p>
    Mensagem:<br />
    <textarea type="text" rows="5" cols="40" name="mensagem"></textarea>
  </p>
  <p>
    <button class="js-submit-button">Enviar Denúncia</button>
  </p>
</form>
