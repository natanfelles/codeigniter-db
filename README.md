# Database Commands for CodeIgniter 4

Adds the following commands to the *Database* group:

 Command        | Description
--------------- | --------------------------------
db:create       | Creates a database
db:delete       | Deletes a database
db:delete_table | Deletes a database table
db:query        | Executes a SQL query
db:show         | Shows databases information
db:show_table   | Shows a database table structure

### Preview:

![Image of Database Commands for CodeIgniter 4](https://raw.githubusercontent.com/natanfelles/codeigniter-db/master/cli.png)

### Configuration

Map the `natanfelles\CodeIgniter\DB` namespace to the *src* folder of this project.

For example:

Open the *application/Config/Autoload.php* file and...

If the location of the *codeigniter-db* folder is at the same level as the *application* folder, configure the `$psr4` in this way:

```php
$psr4 = [
	'Config'      => APPPATH . 'Config',
	APP_NAMESPACE => APPPATH,
	'App'         => APPPATH,
	// codeigniter-db in the root path
	'natanfelles\CodeIgniter\DB' => ROOTPATH . 'codeigniter-db/src',
];
```

If the installation was via Composer, like this:

```php
$psr4 = [
	'Config'      => APPPATH . 'Config',
	APP_NAMESPACE => APPPATH,
	'App'         => APPPATH,
	// codeigniter-db installed via Composer
	'natanfelles\CodeIgniter\DB' => ROOTPATH . 'vendor/natanfelles/codeigniter-db/src',
];
```

> Note: Installation via Composer (under development) will be simplified.

### Contribute

Any contribution related to bugs or improvements of this project will be very well accepted.

If you can, consider a donation:

[![Paypal donation](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2EYQMLYN8GSU6)
