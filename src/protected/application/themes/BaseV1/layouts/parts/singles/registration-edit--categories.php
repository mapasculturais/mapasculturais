<?php if($opportunity->registrationCategories): ?>
    <div class="registration-fieldset"  ng-controller="RegistrationFieldsController">
        <div id="category">
            <span class="label"> 
                <?php echo $opportunity->registrationCategTitle ?>
                <!-- TODO: required Category -->
                <!-- <span ng-if="requiredField(field) ">obrigatório</span>   -->
            </span>
            
            <div class="attachment-description"><?php echo $opportunity->registrationCategDescription ?></div>
            
            <div>
            <!-- TODO: ng-required="requiredField(field)" -->
            <select  ng-model="entity['category']"  ng-blur="saveField({fieldName:'category'}, entity['category'])" >
                <option ng-repeat="option in registrationCategories" value="{{::option.indexOf(':') >= 0 ? option.split(':')[0] : option}}">{{::option.indexOf(':') >= 0 ? option.split(':')[1] : option}}</option>
            </select>
        </div>
    </div>

    </div>

    
<?php endif; ?>