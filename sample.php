<?php

	class Northwind {
		private $db = "dev1.dhingana.com";
		private $user="dhingana";
		private $pass = "dhinganaTest1ng";

		function __construct() {}

		function getTable($name) {
			$dbconn = mysql_connect($this->db, $this->user, $this->pass);
			if (!$dbconn) {
				throw new Exception("Cannot connect to database.");
			}

			mysql_select_db("dhingana", $dbconn);

			$sql = "SELECT count(*) FROM " . mysql_real_escape_string($name, $dbconn);
			$query_result = mysql_query($sql, $dbconn);
			if (!$query_result){
				throw new Exception("Invalid query. Error: " . mysql_error());
			}

			$rows = array();
			while ($row = mysql_fetch_assoc($query_result)) {
				array_push($rows, $row);
			}

			return $rows;
		}
	}
?>
