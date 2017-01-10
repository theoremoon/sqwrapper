<?php

namespace sqwrapper;

class DB {
	static $dbname;

	static function connect() {
		$pdo = new \PDO("sqlite:" . self::$dbname);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

		return $pdo;
	}

	static function prepareinsert($pdo, $table, $keys) {
		$query = "insert into $table(" . 
			implode(",", array_map(function($v) { return "`$v`";}, $keys)) .
			") values (" .
			implode(",", array_map(function($v) { return ":$v";}, $keys)) .
			")";
		return $pdo->prepare($query);
	}

	static function getmaxid($pdo, $table) {
		$stmt = $pdo->prepare("select max(id) from `$table`");
		$stmt->execute();

		return $stmt->fetchAll()[0]['max(id)'] or 0;
	}
}
