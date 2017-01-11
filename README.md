# sqwrapper
sqwrapper is a small sqlite PDO wrapper

## Usage

```php

<?php

require_once('src/Model.php');
require_once('src/Column.php');
require_once('src/DB.php');

use sqwrapper\Model;
use sqwrapper\DB;

class User extends Model {
	public function __construct() {
		$this->setname('users');
		$this->column('id', 'int')->autoincrement()->unique();
		$this->column('name', 'text')->unique();
		$this->column('password', 'text')->setinserthook(function($v) {
			return password_hash($v['value'], PASSWORD_DEFAULT);
		});
	}
}

DB::$dbname = 'database.db';
$pdo = DB::connect();
$pdo->exec((new User())->schema());

(new User())->register([
	'name' => 'username',
	'password' => 'password'
]);

```

When you saved above script as `hoge.php`, after you executed ` php hoge.php ` we got below result.

`sqlite3 database.db`

```sqlite
sqlite> .schema                                                                 
CREATE TABLE `users`(                                                           
		    `id` `int` not null unique,                                                 
		        `name` `text` not null unique,                                              
			    `password` `text` not null                                                  
		);                                                                              
sqlite> select * from users;                                                    
1|username|$2y$10$xfrRN2TKb9.TtmCtrNYuGeSwGmWWcxFgSHKwCPc5cV8CjebWs5dXW   
```


