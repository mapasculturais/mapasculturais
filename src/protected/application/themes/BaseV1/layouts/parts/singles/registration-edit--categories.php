<?php if($opportunity->registrationCategories): ?>
    <div class="registration-fieldset">
        <div id="category">
            <span class="label"> 
                <?php echo $opportunity->registrationCategTitle ?>
                <!-- TODO: required Category -->
                <!-- <span ng-if="requiredField(field) ">obrigatório</span>   -->
            </span>
            
            <div class="attachment-description"><?php echo $opportunity->registrationCategDescription ?></div>
            
            <div>
                <!-- TODO: ng-required="requiredField(field)" -->
                <!-- foi trocado ng-blur para ng-change, para dar o trigger na função sempre que uma nova opção no select for escolhida -->
                <select  ng-model="entity.category" ng-change="saveField({fieldName:'category'}, entity.category)" >
                    <option ng-repeat="option in registrationCategories" value="{{::option.indexOf(':') >= 0 ? option.split(':')[0] : option}}">{{::option.indexOf(':') >= 0 ? option.split(':')[1] : option}}</option>
                </select>
            </div>
        </div>
        <div ng-repeat="error in data.errors.category" class="alert danger">{{error}}</div>
    </div>

    
<?php endif; ?>