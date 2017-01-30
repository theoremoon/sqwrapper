<?php

namespace sqwrapper;

class Where {
	public $tablename;
	public $condition;
	public $getpdo;

	public function __construct($tablename, $condition, $getpdo = []) {
		$this->tablename = $tablename;
		$this->condition = $condition;

		if (is_callable($getpdo)) {
			$this->getpdo = $getpdo;
		}
		else {
			$this->getpdo = function () { return DB::connect(); };
		}
	}

	public function delete() {
		$query = sprintf("delete from `%s` %s", $this->tablename, DB::where($this->condition));
		$pdo = call_user_func($this->getpdo);
		$stmt = $pdo->prepare($query);
		return $stmt->execute();
	}
}
