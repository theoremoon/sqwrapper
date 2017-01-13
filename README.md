# sqwrapper
sqwrapper is a small sqlite PDO wrapper

## Usage

```php
<?php
require_once('vendor/autoload.php');
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

```

When you saved above script as `hoge.php`, after you executed ` php hoge.php ` we got below result.

`sqlite3 database.db`

```sql
sqlite> .schema
CREATE TABLE `users`(
	`id` `int` not null unique,
	`name` `text` not null unique,
	`password` `text` not null
);
sqlite> select * from users;
1|username|$2y$10$qes1LSAp5ONLcqP1ozFjZub1jUAulImQgjlvqmv9wEOY8LlHASfG6
```
