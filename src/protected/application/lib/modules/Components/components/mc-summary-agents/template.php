<?php

use MapasCulturais\i;

$this->import('
    mapas-card
    mc-icon
    
');
?>
<mapas-card v-if="opportunity && canSee('agentsSummary')">
    <template #title>
        <div>
            <div>
                <h4><strong><?= i::__("Dados do proponente") ?> </strong></h4>
            </div>
            <div>
                <img v-if="entity.owner.files.avatar" :src="entity.owner.files.avatar?.transformations?.avatarMedium?.url" />
                <mc-icon v-if="!entity.owner.files.avatar" name="agent-1"></mc-icon>
                <span>{{owner.name}}</span>
            </div>
            <div><small><strong><?= i::__("ID:") ?></strong> {{owner.id}}</small></div>
            <div><small><strong><?= i::__("Nome:") ?></strong> {{owner.name}}</small></div>
            <div><small><strong><?= i::__("Localização:") ?></strong> {{owner.location.latitude}}, {{owner.location.longitude}}</small></div>
            <div><small><strong><?= i::__("Nome completo:") ?></strong> {{owner.nomeCompleto}}</small></div>
            <div><small><strong><?= i::__("CPF:") ?></strong> {{owner.cpf || owner.documento}}</small></div>
            <div><small><strong><?= i::__("Raça/cor:") ?></strong> {{owner.raca}}</small></div>
            <div><small><strong><?= i::__("Nascimento:") ?></strong> {{owner.dataDeNascimento}}</small></div>
            <div><small><strong><?= i::__("Gênero:") ?></strong> {{owner.genero}}</small></div>
            <div><small><strong><?= i::__("Email público:") ?></strong> {{owner.emailPublico}}</small></div>
            <div><small><strong><?= i::__("Email Privado:") ?></strong> {{owner.emailPrivado}}</small></div>
            <div><small><strong><?= i::__("Telefone público:") ?></strong> {{owner.telefonePublico}}</small></div>
            <div><small><strong><?= i::__("Telefone 1:") ?></strong> {{owner.telefone1}}</small></div>
            <div><small><strong><?= i::__("Telefone 2:") ?></strong> {{owner.telefone2}}</small></div>
            <div><small><strong><?= i::__("Endereço:") ?></strong> {{owner.endereco}}</small></div>
            <div><small><strong><?= i::__("CEP:") ?></strong> {{owner.En_CEP}}</small></div>
            <div><small><strong><?= i::__("Logradouro:") ?></strong> {{owner.En_Nome_Logradouro}}</small></div>
            <div><small><strong><?= i::__("Número:") ?></strong> {{owner.En_Num}}</small></div>
            <div><small><strong><?= i::__("Complemento:") ?></strong> {{owner.complemento}}</small></div>
            <div><small><strong><?= i::__("Bairro:") ?></strong> {{owner.En_Bairro}}</small></div>
            <div><small><strong><?= i::__("Município:") ?></strong> {{owner.En_Municipio}}</small></div>
            <div><small><strong><?= i::__("Estado:") ?></strong> {{owner.En_Estado}}</small></div>
            <div><small><strong><?= i::__("Site:") ?></strong> {{owner.site}}</small></div>
            <div><small><strong><?= i::__("Facebook:") ?></strong> {{owner.facebook}}</small></div>
            <div><small><strong><?= i::__("Twitter:") ?></strong> {{owner.twitter}}</small></div>
        </div>
        <br>
        <div v-if="opportunity.useAgentRelationInstituicao && opportunity.useAgentRelationInstituicao !== 'dontUse'">
            <div>
                <h4><strong><?= i::__("Dados da instituição responsável") ?> </strong></h4>
            </div>
            <div>
                <div>
                    <img v-if="getAvatarRelatedEntity('instituicao')" :src="getAvatarRelatedEntity('instituicao')" />
                    <mc-icon v-if="!getAvatarRelatedEntity('instituicao')" name="space"></mc-icon>
                    <span v-if="institution">{{institution?.name}}</span>
                    <span v-if="!institution && opportunity.useAgentRelationInstituicao"><?= i::__("Instituição não informada") ?></span>
                </div>
            </div>
            <div v-if="institution">
                <div><small><strong><?= i::__("ID:") ?></strong> {{institution?.id}}</small></div>
                <div><small><strong><?= i::__("Nome:") ?></strong> {{institution?.name}}</small></div>
                <div><small><strong><?= i::__("Localização:") ?></strong> {{institution?.location.latitude}}, {{institution?.location.longitude}}</small></div>
                <div><small><strong><?= i::__("Nome completo:") ?></strong> {{institution?.nomeCompleto}}</small></div>
                <div><small><strong><?= i::__("CNPJ:") ?></strong> {{institution?.cnpj || institution?.documento}}</small></div>
                <div><small><strong><?= i::__("Email público:") ?></strong> {{institution?.emailPublico}}</small></div>
                <div><small><strong><?= i::__("Email Privado:") ?></strong> {{institution?.emailPrivado}}</small></div>
                <div><small><strong><?= i::__("Telefone público:") ?></strong> {{institution?.telefonePublico}}</small></div>
                <div><small><strong><?= i::__("Telefone 1:") ?></strong> {{institution?.telefone1}}</small></div>
                <div><small><strong><?= i::__("Telefone 2:") ?></strong> {{institution?.telefone2}}</small></div>
                <div><small><strong><?= i::__("Endereço:") ?></strong> {{institution?.endereco}}</small></div>
                <div><small><strong><?= i::__("CEP:") ?></strong> {{institution?.En_CEP}}</small></div>
                <div><small><strong><?= i::__("Logradouro:") ?></strong> {{institution?.En_Nome_Logradouro}}</small></div>
                <div><small><strong><?= i::__("Número:") ?></strong> {{institution?.En_Num}}</small></div>
                <div><small><strong><?= i::__("Complemento:") ?></strong> {{institution?.complemento}}</small></div>
                <div><small><strong><?= i::__("Bairro:") ?></strong> {{institution?.En_Bairro}}</small></div>
                <div><small><strong><?= i::__("Município:") ?></strong> {{institution?.En_Municipio}}</small></div>
                <div><small><strong><?= i::__("Estado:") ?></strong> {{institution?.En_Estado}}</small></div>
                <div><small><strong><?= i::__("Site:") ?></strong> {{institution?.site}}</small></div>
                <div><small><strong><?= i::__("Facebook:") ?></strong> {{institution?.facebook}}</small></div>
                <div><small><strong><?= i::__("Twitter:") ?></strong> {{institution?.twitter}}</small></div>
            </div>
        </div>
        
        <br>
        <div v-if="opportunity.useAgentRelationColetivo && opportunity.useAgentRelationColetivo !== 'dontUse'">
            <div>
                <h4><strong><?= i::__("Dados do coletivo") ?> </strong></h4>
            </div>
            <div>
                <div>
                    <img v-if="getAvatarRelatedEntity('coletivo')" :src="getAvatarRelatedEntity('coletivo')" />
                    <mc-icon v-if="!getAvatarRelatedEntity('coletivo')" name="agent"></mc-icon>
                    <span v-if="colective">{{colective?.name}}</span>
                    <span v-if="!colective && (opportunity.useAgentRelationColetivo)"><?= i::__("Instituição não informada") ?></span>
                </div>
            </div>
            <div v-if="colective">
                <div><small><strong><?= i::__("ID:") ?></strong> {{colective?.id}}</small></div>
                <div><small><strong><?= i::__("Nome:") ?></strong> {{colective?.name}}</small></div>
                <div><small><strong><?= i::__("Localização:") ?></strong> {{colective?.location.latitude}}, {{colective?.location.longitude}}</small></div>
                <div><small><strong><?= i::__("Telefone 1:") ?></strong> {{colective?.telefone1}}</small></div>
            </div>
        </div>
    </template>
</mapas-card>