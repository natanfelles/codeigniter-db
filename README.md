# Installation
Installation is best done via Composer. Assuming Composer is installed globally, you may use the following command:

> composer require natanfelles/codeigniter-db

# Database Commands for CodeIgniter 4

Adds the following commands to the *Database* group:

 Command        | Description
--------------- | --------------------------------
db:create       | Creates a database
db:delete       | Deletes a database
db:delete_table | Deletes a database table
db:list         | Lists databases
db:query        | Executes a SQL query
db:show         | Shows databases information
db:show_table   | Shows a database table structure

### Preview

![Image of Database Commands for CodeIgniter 4](https://natanfelles.github.io/assets/img_posts/codeigniter-db.png)

### Configuration

Map the `NatanFelles\CodeIgniter\DB` namespace to the *src* folder of this project.

For example:

Open the *app/Config/Autoload.php* file and add a `$psr4` index like this:

```php
$psr4['NatanFelles\CodeIgniter\DB'] = ROOTPATH . 'codeigniter-db/src';
```

### Contribute

Any contribution related to bugs or improvements of this project will be very well accepted.

If you can, consider a donation:

[![Paypal donation](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2EYQMLYN8GSU6)
