<?php

namespace OpportunityAccountability;

use MapasCulturais\i;

?>
<div ng-controller='OpportunityProjects'>

    <div class="card">
        <header>
            <div class="thumb"><img src="<?php $this->asset('img/avatar--project.png'); ?>"></div>
            <h3>Nome do Projeto</h3>
            <p><span class="label">Tipo:</span> <span class="type">Teste</span></p>
            <p><span class="label">Inscrições:</span> de 13/01/21 até 31/03/21</p>
        </header>

        <div class="content">
            <p>Commodo deserunt est ullamco commodo sint ad tempor proident labore aute sunt laboris. Dolore nostrud duis occaecat ex labore enim laborum est tempor esse eiusmod esse do.</p>
        </div>

        <footer>
            <div class="tags">
                <p><span class="label">Tags:</span> <span>Tag 1</span> <span>Tag 2</span> <span>Tag 3</span></p>
            </div>

            <div class="status">
                <p><span>Status:</span> Teste de status</p>
                <button>Ver agenda</button>
            </div>
        </footer>
    </div><!-- /.card -->

</div><!-- /ng-controller OpportunityProjects -->