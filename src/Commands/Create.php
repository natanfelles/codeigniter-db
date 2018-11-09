<?php namespace natanfelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Create
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package natanfelles\CodeIgniter\DB\Commands
 */
class Create extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:create';
	protected $description = 'Creates a Database';
	protected $usage       = 'db:create [database]';
	protected $arguments   = [
		'database' => 'Database name',
	];

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description           = lang('DB.createsDatabase');
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

		if ($show)
		{
			CLI::beep();
			CLI::error(lang('DB.databaseExists', [$database]));

			return;
		}

		$result = \Config\Database::forge()->createDatabase($database);

		if ($result)
		{
			CLI::write(lang('DB.databaseCreated', [$database]), 'green');

			return;
		}

		CLI::error(lang('DB.databaseNotCreated', [$database]));
	}
}
