<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>

<div>
    <div>
        <label> <?= i::__('Tipo de conta') ?></label>
        <select v-model="bankFields.account_type" @change="change();">
            <option value=""><?= i::__('Selecione o tipo de conta') ?></option>
            <option v-for="(value, key) in accountTypes" :key="key" :value="key">{{ value }}</option>
        </select>
    </div>
    <div>

        <label><?= i::__('Número do banco') ?> </label>
        <select v-model="bankFields.number" @change="change()">
            <option value=""><?= i::__('Selecione o banco') ?></option>
            <option v-for="(value, key) in bankTypes" :key="key" :value="key">{{ value }}</option>
        </select>

    </div>
    <div>
        <label><?= i::__('Agência') ?></label>
        <input type="text" v-model="bankFields.branch" @change="change()"/>
    </div>
    <div>
        <label><?= i::__('Dígito verificador da agência') ?> </label>
        <input type="text" v-model="bankFields.dv_branch" maxlength="1" @change="change()" />

    </div>
    <div>
        <label><?= i::__('Número da conta') ?> </label>
        <input type="text" v-model="bankFields.account_number" @change="change()" />

    </div>
    <div>
        <label><?= i::__('Dígito verificador da conta') ?> </label>
        <input type="text" v-model="bankFields.dv_account_number" maxlength="1"  @change="change()" />
    </div>
</div>