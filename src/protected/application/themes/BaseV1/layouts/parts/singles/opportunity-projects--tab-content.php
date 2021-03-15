<?php

namespace OpportunityAccountability;

use MapasCulturais\i;

?>
<div ng-controller='OpportunityProjects'>

    <div class="card" ng-repeat="project in data.projects">
        <header>
            <div class="thumb"><img src="<?php $this->asset('img/avatar--project.png'); ?>"></div>
            <h3>{{project.name}}</h3>
            <p><span class="label">Tipo:</span> <span class="type">{{project.type.name}}</span></p>
            <p><span class="label">Inscrições:</span> de 13/01/21 até 31/03/21</p>
        </header>

        <div class="content">
            <p>{{project.shortDescription}}</p>
        </div>

        <footer>
            <div class="tags">
                <p><span class="label">Tags:</span>
                    <span ng-repeat="tag in project.terms.tag">{{tag}}</span>
                </p>
            </div>

            <div class="status">
                <p><span>Status:</span> {{project.status}}</p>
                <button>Ver agenda</button>
            </div>
        </footer>
    </div><!-- /.card -->

</div><!-- /ng-controller OpportunityProjects -->