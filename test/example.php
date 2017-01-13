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

(new User())->register([
	'name' => 'username',
	'password' => 'password'
]);

$ret = 0;


$result = `echo "select name from users;" | sqlite3 database.db`;
if (trim($result) != "username") {
	$ret = 1;
	fprintf(STDERR, "table data is wrong\n");
}

unlink("database.db");

exit($ret);
