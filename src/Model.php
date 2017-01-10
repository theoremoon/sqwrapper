<?php

namespace sqwrapper;

class Model {
	public $columns;
	public $tablename;


	public function column($name, $type) {
		$this->columns []= new Column($name, $type);
		$i = count($this->columns);
		return $this->columns[$i-1];
	}

	public function setname($name) {
		$this->tablename = $name;
	}

	public function schema() {
		$schema = sprintf('create table `%s`(', $this->tablename) . "\n";
		for ($i = 0; $i < count($this->columns); $i++) {
			$schema .= "    " . $this->columns[$i]->schema() . (($i==count($this->columns)-1) ? "\n" : ",\n");
		}
		$schema .= ");";

		return $schema;
	}

	public function register($values=[]) {
		$db = DB::connect();

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
