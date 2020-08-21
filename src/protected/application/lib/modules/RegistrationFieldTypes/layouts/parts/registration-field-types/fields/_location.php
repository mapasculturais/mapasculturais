<?php use \MapasCulturais\i; ?>
<p>
    <label>
        <?= i::__('CEP') ?><br>
        <input ng-model="entity[fieldName].En_CEP" data-mask="99999-99" required />
    </label>
</p>
<p>
    <label>
        <?= i::__('Logradouro') ?><br>
        <input ng-model="entity[fieldName].En_Nome_Logradouro" required="required" />
    </label>
</p>
<p>
    <label>
        <?= i::__('Número') ?><br>
        <input ng-model="entity[fieldName].En_Num" />
    </label>
</p>
<p>
    <label>
        <?= i::__('Complemento') ?><br>
        <input ng-model="entity[fieldName].En_Complemento" />
    </label>
</p>
<p>
    <label>
        <?= i::__('Bairro') ?><br>
        <input ng-model="entity[fieldName].En_Bairro" />
    </label>
</p>
<p>
    <label>
        <?= i::__('Cidade') ?><br>
        <input ng-model="entity[fieldName].En_Municipio" />
    </label>
</p>

