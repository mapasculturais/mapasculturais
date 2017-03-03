<?php 
use MapasCulturais\i;
?>
<div class="sidebar-left sidebar registration">
    <?php if($entity->canUser('evaluate')): ?>
    <style>
        #registrations-list-container{
            position: fixed;
            margin-right: -1.5em;
            margin-left: -1.5em;
            width:15%;
            display:block;
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
            margin:0;
        }
        .registrations-list .registration-item { 
            border-bottom: 1px solid #aaa; 
            cursor: pointer !important;
            padding: .5em;
        }
        .registrations-list .registration-item:hover { background: #ffc; }
        .registrations-list .registration-item a { font-weight: normal; }
        .registrations-list .registration-item a:hover { text-decoration: none; }
        .registrations-list .registration-item.current { background: #cfc; }
        .registrations-list .registration-item .registration-evaluated { font-style: italic; font-size:10px; font-color:#666; }

    </style>
    <div ng-controller="RegistrationListController" id="registrations-list-container">

        <div><label><input type="checkbox" ng-model="data.pending" /> <?php i::_e('somente pendentes'); ?></label></div>
        <input ng-model="data.keyword" ng-model-options="{ debounce: 333 }" id="registrations-list--filter" placeholder="<?php i::_e('Filtro'); ?>"/>
        <ul id="registrations-list" class="registrations-list">
            <li ng-repeat="registration in registrations" ng-show="show(registration)" ng-class="{current: registration.id == data.current, visible:show(registration)}" class="registration-item" >
                <a href="{{::registration.singleUrl}}">
                    <div ng-if="evaluated(registration)" class="registration-evaluated">(<?php i::_e('Avaliação:')?> <strong>{{evaluations[registration.id].resultString}}</strong>)</div>
                    <div class="registration-number">{{::registration.number}}</div>
                    <div ng-if="registration.category" class="registration-category">{{::registration.category}}</div>
                    <div class="registration-owner">{{::registration.owner.name}}</div>
                </a>
            </li>
            
        </ul>
    </div>
    <?php endif; ?>
</div>