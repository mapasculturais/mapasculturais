<?php

$config = include 'conf-base.php';

return array_merge($config,
    array(
        // development, staging, production
	    'app.mode' => 'development',
    	'app.fakeAuthentication' => true
    )
);
