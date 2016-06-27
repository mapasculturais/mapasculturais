<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
    	'site' => array(
    			'label' => 'Site',
    			'validations' => array(
    				"v::url()" => "A url informada é inválida."
    			)
    	)
    ),
    'items' => array(
		0 => array( 'name' => 'Infinita' ),
        1 => array( 'name' => 'Dias' ),
        2 => array( 'name' => 'Semanas' ),
    	3 => array( 'name' => 'Meses' ),
    	4 => array( 'name' => 'Anos' )
    )
);