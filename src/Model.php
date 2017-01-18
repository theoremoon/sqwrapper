<?php

namespace sqwrapper;

abstract class Model {
	public $columns;
	public $tablename;

	public $getpdo;

	abstract public function setschema();

	public function __construct($values = [], $getpdo = NULL) {
		$this->columns = [];
		$this->setschema();

		if (is_callable($getpdo)) {
			$this->getpdo = $getpdo;
		}
		else {
			$this->getpdo = function () { return DB::connect(); };
		}

		if (count($values) > 0) {
			foreach($values as $k => $v) {
				$this->columns[$k]->setvalue($v);
			}
		}
	}

	public function addcolumn($name,  $formtype, $dbtype) {
		$this->columns[$name] =  new Column($name, $formtype, $dbtype, $this->getpdo);
		return $this->columns[$name];
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

	public function setname($name) {
		$this->tablename = $name;
	}

	public function getschema() {
		$schema = sprintf('create table `%s`(', $this->tablename) . "\n";
		foreach ($this->columns as $k => $v) {
			$schema .= "    " . $v->getschema() . ",\n";
		}
		$schema = substr($schema, 0, -2) . ");";

		return $schema;
	}

	public function insert($values=[]) {
		$db = $this->getpdo->__invoke();

		$keys = [];
		
		foreach ($this->columns as $name => $column) {
			$values[$name] = $column->getvalue($values);
			if ($values[$name] === null && !is_callable($column->inserthook)) {
				throw new \Exception("missed key: " . $column->name);
			}
			
			$hookvalues = [
				'value' => $values[$name],
				'table' => $this->tablename,
				'name' => $column->name
			];

			if (is_callable($column->insertvalidate)) {
			       	$s = $column->insertvalidate->__invoke($hookvalues);

				if ($s !== true) {
					throw new \Exception($s);
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
