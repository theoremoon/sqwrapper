<?php

namespace sqwrapper;

abstract class Model {
	public $columns;
	public $tablename;

	public $getpdo;

	abstract public function setschema();

	public function __construct($getpdo = NULL) {
		$this->columns = [];
		$this->setschema();

		if (is_callable($getpdo)) {
			$this->getpdo = $getpdo;
		}
		else {
			$this->getpdo = function () { return DB::connect(); };
		}
	}

	public static function select($where = [], $getpdo = NULL) {
		$pdo = NULL;
		if (is_callable($getpdo)) {
			$pdo = call_user_func($getpdo);
		}
		else {
			$pdo = DB::connect();
		}
		
		$classname = get_called_class();
		$table = (new $classname())->getname();
		// $schema = (new __class__())->getcolumns();
		$query = "select * from `$table`";
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function addcolumn($name,  $formtype, $dbtype) {
		$this->columns []=  new Column($name, $formtype, $dbtype, $this->getpdo);
		$i = count($this->columns) - 1;
		return $this->columns[$i];
	}

	public function number($name) {
		return $this->addcolumn($name, 'number', 'int');
	}

	public function text($name) {
		return $this->addcolumn($name, 'text', 'text');
	}

	public function password($name) {
		return $this->addcolumn($name, 'password', 'text');
	}

	public function getname() {
		return $this->tablename;
	}

	public function setname($name) {
		$this->tablename = $name;
	}

	public function getcolumns() {
		return $this->columns;
	}
	public function getschema() {
		$schema = sprintf('create table `%s`(', $this->tablename) . "\n";
		for ($i = 0; $i < count($this->columns); $i++) {
			$schema .= "    " . $this->columns[$i]->getschema() . (($i==count($this->columns)-1) ? "\n" : ",\n");
		}
		$schema .= ");";

		return $schema;
	}

	public function register($values=[]) {
		$db = $this->getpdo->__invoke();

		$keys = [];
		
		foreach ($this->columns as $column) {
			if (!isset($values[$column->name]) && $column->inserthook === null) {
				throw new Exception("missed key: " . $column->name);
			}
			
			$hookvalues = [
				'value' => (isset($values[$column->name])) ? $values[$column->name] : null,
				'table' => $this->tablename,
				'name' => $column->name
			];

			if (is_callable($column->insertvalidate)) {
			       	$s = $column->insertvalidate->__invoke($hookvalues);

				if ($s !== true) {
					throw new Exception($s);
				}
			}

			if (is_callable($column->inserthook)) {
				$values[$column->name] = $column->inserthook->__invoke($hookvalues);
			}

			$keys []= $column->name;
		}

		$stmt = DB::prepareinsert($db, $this->tablename, $keys);
		$stmt->execute($values);
	}
}
