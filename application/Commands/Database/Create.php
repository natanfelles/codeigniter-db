<?php namespace App\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Create
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package App\Commands\Database
 */
class Create extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:create';
	protected $description = 'Creates a Database';

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description = lang('Database.createsDatabase');
	}

	public function run(array $params)
	{
		$database = array_shift($params);

		if (empty($database))
		{
			$database = CLI::prompt(lang('Database.databaseName'), null, 'alpha_dash');
		}

		$show = \Config\Database::connect()
		                        ->query('SHOW DATABASES LIKE :database:', [
			                        'database' => $database,
		                        ])->getRowArray();

		if ( ! empty($show))
		{
			CLI::beep();
			CLI::error(lang('Database.databaseExists', [$database]));
			CLI::newLine();
			exit;
		}

		$result = \Config\Database::forge()->createDatabase($database);

		if ($result)
		{
			CLI::write(lang('Database.databaseCreated', [$database]), 'green');
			CLI::newLine();
			exit;
		}

		CLI::error(lang('Database.databaseNotCreated', [$database]));
		CLI::newLine();
	}
}
