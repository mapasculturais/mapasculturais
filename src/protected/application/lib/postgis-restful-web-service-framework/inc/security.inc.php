<?php
/**
 * Security Module
 * Sanitizes SQL requests for injections and holds any block lists.
 */


/**
 * Sanitize SQL
 * Sanitize SQL statements for ';--' etc.
 * @param 		string	$sql	the SQL to be sanitized 
 * @return 		string			cleaned sql string
 */
function sanitizeSQL ($sql) {
	# return pg_escape_string($param);
	return $sql;
}

/**
 * Block List
 * This is the area to do white or black lists for security.
 */

?>