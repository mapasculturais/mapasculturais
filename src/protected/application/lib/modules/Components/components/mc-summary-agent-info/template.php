<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon    
');
?>

<div class="mc-summary-agent-info" :class="classes">
    <div v-if="opportunity && canSee('agentsSummary')" class="mc-summary-agent-info__section">
        <h3><?= i::__("Dados do proponente") ?></h3>

        <h4 v-if="owner.id"> <span class="bold"><?= i::__("ID:") ?></span> {{owner.id}} </h4>
        <h4 v-if="owner.name"> <span class="bold"><?= i::__("Nome:") ?></span> {{owner.name}} </h4>
        <h4 v-if="owner.location.latitude && owner.location.longitude"> <span class="bold"><?= i::__("Localização:") ?></span> {{owner.location.latitude}}, {{owner.location.longitude}} </h4>
        <h4 v-if="owner.nomeCompleto"> <span class="bold"><?= i::__("Nome completo:") ?></span> {{owner.nomeCompleto}} </h4>
        <h4 v-if="owner.cpf || owner.documento"> <span class="bold"><?= i::__("CPF:") ?></span> {{owner.cpf || owner.documento}} </h4>
        <h4 v-if="owner.raca"> <span class="bold"><?= i::__("Raça/cor:") ?></span> {{owner.raca}} </h4>
        <h4 v-if="owner.dataDeNascimento"> <span class="bold"><?= i::__("Nascimento:") ?></span> {{owner.dataDeNascimento}} </h4>
        <h4 v-if="owner.genero"> <span class="bold"><?= i::__("Gênero:") ?></span> {{owner.genero}} </h4>
        <h4 v-if="owner.emailPublico"> <span class="bold"><?= i::__("Email público:") ?></span> {{owner.emailPublico}} </h4>
        <h4 v-if="owner.emailPrivado"> <span class="bold"><?= i::__("Email Privado:") ?></span> {{owner.emailPrivado}} </h4>
        <h4 v-if="owner.telefonePublico"> <span class="bold"><?= i::__("Telefone público:") ?></span> {{owner.telefonePublico}} </h4>
        <h4 v-if="owner.telefone1"> <span class="bold"><?= i::__("Telefone 1:") ?></span> {{owner.telefone1}} </h4>
        <h4 v-if="owner.telefone2"> <span class="bold"><?= i::__("Telefone 2:") ?></span> {{owner.telefone2}} </h4>
        <h4 v-if="owner.endereco"> <span class="bold"><?= i::__("Endereço:") ?></span> {{owner.endereco}} </h4>
        <h4 v-if="owner.En_CEP"> <span class="bold"><?= i::__("CEP:") ?></span> {{owner.En_CEP}} </h4>
        <h4 v-if="owner.En_Nome_Logradouro"> <span class="bold"><?= i::__("Logradouro:") ?></span> {{owner.En_Nome_Logradouro}} </h4>
        <h4 v-if="owner.En_Num"> <span class="bold"><?= i::__("Número:") ?></span> {{owner.En_Num}} </h4>
        <h4 v-if="owner.complemento"> <span class="bold"><?= i::__("Complemento:") ?></span> {{owner.complemento}} </h4>
        <h4 v-if="owner.En_Bairro"> <span class="bold"><?= i::__("Bairro:") ?></span> {{owner.En_Bairro}} </h4>
        <h4 v-if="owner.En_Municipio"> <span class="bold"><?= i::__("Município:") ?></span> {{owner.En_Municipio}} </h4>
        <h4 v-if="owner.En_Estado"> <span class="bold"><?= i::__("Estado:") ?></span> {{owner.En_Estado}} </h4>
        <h4 v-if="owner.site"> <span class="bold"><?= i::__("Site:") ?></span> {{owner.site}} </h4>
        <h4 v-if="owner.facebook"> <span class="bold"><?= i::__("Facebook:") ?></span> {{owner.facebook}} </h4>
        <h4 v-if="owner.twitter"> <span class="bold"><?= i::__("Twitter:") ?></span> {{owner.twitter}} </h4>
    </div>

    <div v-if="opportunity.useAgentRelationColetivo && opportunity.useAgentRelationColetivo !== 'dontUse'" class="mc-summary-agent-info__section">
        <h3><?= i::__("Dados do coletivo") ?></h3>

        <div>
            <img v-if="getAvatarRelatedEntity('coletivo')" :src="getAvatarRelatedEntity('coletivo')" />
            <mc-icon v-if="!getAvatarRelatedEntity('coletivo')" name="agent"></mc-icon>
            <span v-if="colective">{{colective?.name}}</span>
            <span v-if="!colective && (opportunity.useAgentRelationColetivo)"><?= i::__("Instituição não informada") ?></span>
        </div>

        <div v-if="colective">
            <div><small><strong><?= i::__("ID:") ?></strong> {{colective?.id}}</small></div>
            <div><small><strong><?= i::__("Nome:") ?></strong> {{colective?.name}}</small></div>
            <div><small><strong><?= i::__("Localização:") ?></strong> {{colective?.location.latitude}}, {{colective?.location.longitude}}</small></div>
            <div><small><strong><?= i::__("Telefone 1:") ?></strong> {{colective?.telefone1}}</small></div>
        </div>
    </div>
</div>