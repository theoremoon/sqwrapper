<?php

namespace sqwrapper;

abstract class Model implements \ArrayAccess {
	public $columns;
	public $tablename;

	public $getpdo;

	abstract public function setschema();

	public function offsetExists($offset) {
		return isset($this->columns[$offset]);
	}
	public function offsetSet($offset, $value) {
		$this->columns[$offset] = $value;
	}
	public function offsetGet($offset) {
		return $this->columns[$offset];
	}
	public function offsetUnset($offset) {
		unset($this->columns[$offset]);
	}

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

	public static function select($where = [], $getpdo = NULL) {
		$pdo = NULL;
		if (is_callable($getpdo)) {
			$pdo = call_user_func($getpdo);
		}
		else {
			$pdo = DB::connect();
		}

		$class = get_called_class();
		$tablename = (new $class())->getname();
		
		$where = DB::where($where);
		$stmt = $pdo->prepare("select * from `$tablename`" . $where);
		$stmt->execute();

		$rows = [];
		while ($row = $stmt->fetch()) {
			$r = new $class();
			foreach ($row as $k => $v) {
				$r->columns[$k]->setvalue($v);
			}
			$rows [] = $r;
		}

		return $rows;
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

	public function getname() {
		return $this->tablename;
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

	public function forminput() {
		$inputs = '';
		foreach ($this->columns as $column) {
			$inputs .= $column->form();
		}
		return $inputs;
	}

	public function form($url) {
		$forminput = str_replace('<input', '	' . '<input', $this->forminput());
		return sprintf('<form action="%s" method="get">', $url) . PHP_EOL . $forminput . '</form>' . PHP_EOL;
	}

	public function update($data) {
		$where = [];
		foreach ($this->columns as $k => $v) {
			if (! isset($data[$k])) {
				$where[$k] = $v->getvalue();
			}
		}

		$query = sprintf("update `%s` %s %s", $this->tablename, DB::update($data), DB::where($where));
		$pdo = call_user_func($this->getpdo);
		$stmt = $pdo->prepare($query);
		return $stmt->execute();
	}

	public static function where($condition) {
		$class = get_called_class();
		$model = new $class();
		$where = new Where($model->tablename, $condition);
		return $where;
	}

	public function delete() {
		$query = sprintf("delete from `%s` %s", $this->tablename, DB::where($this->columns));
		$pdo = call_user_func($this->getpdo);
		$stmt = $pdo->prepare($query);
		return $stmt->execute();
	}
}
