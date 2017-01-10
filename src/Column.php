<?php

namespace sqwrapper;

class Column {
	public function __construct($name, $type) {
		$this->name = $name;
		$this->type = $type;

		$this->unique = null;

		$this->inserthook = null;
		$this->insertvalidate = null;
	}

	public function unique() {
		$this->unique = true;
		return $this;
	}

	public function currenttime() {
		$this->inserthook = function($v) {
			return date('Y-m-d H:i:s');
		};
		return $this;
	}

	public function setdefault($value) {
		$local = $value;
		$this->inserthook = function($v) use ($local) {
			return $local;
		};
		return $this;
	}

	public function setinserthook($f) {
		$this->inserthook = $f;
		return $this;
	}
	public function setinsertvalidate($f) {
		$this->insertvalidate = $f;
		return $this;
	}

	public function autoincrement() {
		$this->increment = true;
		$this->inserthook = function($v) {
			$db = DB::connect();

			return DB::getmaxid($db, $v['table']) + 1;
		};
		return $this;
	}	

	public function schema() {
		$schema = sprintf("`%s` `%s` not null", $this->name, $this->type);
		if ($this->unique) {
			$schema .= " unique";
		}

		return $schema;
	}
}
