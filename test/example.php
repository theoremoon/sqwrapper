<?php

require_once(__DIR__ . '/../vendor/autoload.php');
use sqwrapper\Model;
use sqwrapper\DB;

class User extends Model {
	public function setschema() {
		$this->setname('users');

		$this->number('id')->autoincrement()->unique()->noform();
		$this->text('name')->unique();
		$this->password('password')->setinserthook(function($v) {
			return password_hash($v['value'], PASSWORD_DEFAULT);
		});
	}
}

DB::$dbname = 'database.db';
$pdo = DB::connect();
$pdo->exec((new User())->getschema());

(new User())->insert([
	'name' => 'username',
	'password' => 'password'
]);

(new User([
	  'name' => 'taro',
	  'password' => 'jiro'
]))->insert();

$ret = 0;



unlink("database.db");

exit($ret);
