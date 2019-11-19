<?php
/**
 * Database Include
 * Handles all database functions required by the REST web services.
 */



#Use this to work arround lack of document_root variable under PHP CGI.
//if( empty($_SERVER['DOCUMENT_ROOT']) )
//{
//	$_SERVER['DOCUMENT_ROOT'] = dirname("c:/inetpub/wwwroot/rest");
//}


/**
 * Return postgres data connection
 * @return 		object		- adodb data connection
 */
function pgConnection() {
    //$conn = new PDO ("pgsql:host=server_name;dbname=dbname","username","password", array(PDO::ATTR_PERSISTENT => true));
    //return $conn;
    //Gets PDO From Doctrine Connection
    include '../../../bootstrap.php';
    return $app->em->getConnection()->getWrappedConnection();
}


/**
 * Sample SQL Server connection
 * @return 		object		- adodb data connection
 */
function camaConnection() {
    $conn = new PDO("odbc:Driver={SQL Server};Server=server_name;Database=database;Uid=userid;Pwd=password;Pooling=false;", "userid", "password");
    return $conn;
}




?>
