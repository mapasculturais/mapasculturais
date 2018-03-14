<?php
use MapasCulturais\i;
?>
<div class="sidebar-left sidebar registration">
    <?php if($action === 'single' && !$opportunity->publishedRegistrations && $entity->canUser('viewUserEvaluation')): ?>
    <style>
        #registrations-list-container{
            position: fixed;
            margin-right: -1.5em;
            margin-left: -1.5em;
            width:14.5%;
            display:block;
            font-size: .75em;
        }
        @media screen and (max-width: 1100px) {
            #registrations-list-container{
                display:none;
            }
        }

        @media screen and (max-width: 1366px) and (min-width: 1200px){
            #registrations-list-container{
                width:20%;
            }
        }
        .registrations-list {
            max-height:615px;
            overflow-y: auto;
            margin: -1px;
        }
        .registrations-list .registration-item {
            border-bottom: 1px solid #aaa;
            cursor: pointer !important;
            padding: .5em;
        }
        .registrations-list .registration-item:hover { background: #ffc; }
        .registrations-list .registration-item a { font-weight: normal; }
        .registrations-list .registration-item a:hover { text-decoration: none; }
        .registrations-list .registration-item.current { background: #fff !important; }
        .registrations-list .registration-item .registration-evaluated { font-style: italic; font-size:10px; color:#666; }
        .registrations-list .registration-item .registration-number { font-weight: bold; }
        .registrations-list .registration-item .registration-owner {  }
        .registrations-list .registration-item .registration-category { font-style:italic; }
        .registrations-list .registration-item.missing { background-color: #e6c5c5; }

    </style>
    <div ng-controller="RegistrationListController" id="registrations-list-container">
        <h4 style="margin-bottom: 10px"><?php i::_e('Inscrições'); ?></h4>

        <div><label><input type="checkbox" ng-model="data.pending" /> <?php i::_e('Somente avaliações pendentes'); ?></label></div>
        <input ng-model="data.keyword" ng-model-options="{ debounce: 333 }" id="registrations-list--filter" placeholder="<?php i::_e('Filtre pelo nome do agente'); ?>" style="width: 95%"/>
        <ul id="registrations-list" class="registrations-list">
            <li ng-repeat="registration in data.registrations" ng-show="show(registration)" class="registration-item"
                ng-class="{current: registration.id == data.current, visible:show(registration), missing: !evaluated(registration)}">

                <a href="{{::registration.singleUrl}}">
                    <div class="registration-evaluated"> (<?php i::_e('Avaliação:'); ?> <strong> {{status_str(registration)}}</strong>) </div>
                    <div class="registration-number">{{::registration.number}}</div>
                    <div class="registration-owner">{{::registration.owner.name}}</div>
                    <div ng-if="registration.category" class="registration-category">{{::registration.category}}</div>
                </a>
            </li>

        </ul>
    </div>
    <?php endif; ?>
</div>
