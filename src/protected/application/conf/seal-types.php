<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
    	'site' => array(
    			'label' => \MapasCulturais\i::__('Site'),
    			'validations' => array(
    				"v::url()" => \MapasCulturais\i::__("A url informada é inválida.")
    			)
    	),
    ),
    'items' => array(
		0 => array( 'name' => \MapasCulturais\i::__('Infinita' )),
        1 => array( 'name' => \MapasCulturais\i::__('Dias' )),
        2 => array( 'name' => \MapasCulturais\i::__('Semanas') ),
    	3 => array( 'name' => \MapasCulturais\i::__('Meses') ),
    	4 => array( 'name' => \MapasCulturais\i::__('Anos') )
    )
);