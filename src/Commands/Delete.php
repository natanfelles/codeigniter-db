<?php namespace natanfelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Delete
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package natanfelles\CodeIgniter\DB\Commands
 */
class Delete extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:delete';
	protected $description = 'Deletes a Database';
	protected $usage       = 'db:delete [database]';
	protected $arguments   = [
		'database' => 'Database name',
	];

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description           = lang('DB.deletesDatabase');
		$this->arguments['database'] = lang('DB.databaseName');
	}

	public function run(array $params)
	{
		$database = array_shift($params);

		if (empty($database))
		{
			$database = CLI::prompt(lang('DB.databaseName'), null, 'regex_match[\w.]');
		}

		$show = \Config\Database::connect()
		                        ->query('SHOW DATABASES LIKE :database:', [
			                        'database' => $database,
		                        ])->getRowArray();

		if (empty($show))
		{
			CLI::beep();
			CLI::error(lang('DB.databaseNotExists', [$database]));

			return;
		}

		$result = \Config\Database::forge()->dropDatabase($database);

		if ($result)
		{
			CLI::write(lang('DB.databaseDeleted', [$database]), 'green');

			return;
		}

		CLI::error(lang('DB.databaseNotDeleted', [$database]));
	}
}
