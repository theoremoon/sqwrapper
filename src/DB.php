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

		$a = $stmt->fetchAll();
		if (isset($a[0]['max(id)'])) {
			return $a[0]['max(id)'];
		}
		return 0;
	}

	public static function keyescape($str) {
		return '`' . str_replace('`', '``', $str) . '`';
	}
	public static function valueescape($str) {
		$str = str_replace('"', '""', $str);
		if (!is_numeric($str)) {
			$str = '"' . $str . '"';
		}
		return str_replace('\\', '\\\\', $str);
	}

	public static function where($where) {
		if (count($where) == 0) {
			return "";
		}

		$queries = [];
		foreach ($where as $k => $v) {
			$queries []= sprintf("%s=%s", self::keyescape($k), self::valueescape($v));
		}

		return 'where ' . implode(" AND ", $queries);

		
	}

	public static function update($datas) {
		if (count($datas) == 0) {
			return "";
		}

		$queries = [];
		foreach ($datas as $k => $v) {
			$queries []= sprintf("%s=%s", self::keyescape($k), self::valueescape($v));
		}

		return 'set ' . implode(", ", $queries);
	}
}
