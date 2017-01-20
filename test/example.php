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

$users = User::select();
if (! $users[0] instanceof User) {
	fprintf(STDERR, "select type invalid\n");
	$ret = 1;
}

if ($users[0]["name"] != "username") {
	fprintf(STDERR, "select value invalid\n");
	$ret = 1;
}

$a = (new User())->forminput();
$b = '<input type="text" name="name" value="" required>
<input type="password" name="password" value="" required>
';
if ($a != $b) {
	fprintf(STDERR, "forminput() function is invalid\n");
	$ret = 1;
}


unlink("database.db");

exit($ret);
