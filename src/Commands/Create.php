<?php namespace NatanFelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\CLI;

/**
 * Class Create
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package NatanFelles\CodeIgniter\DB\Commands
 */
class Create extends AbstractCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:create';
	protected $description = 'DB.createsDatabase';
	protected $usage       = 'db:create [database]';
	protected $arguments   = [
		'database' => 'DB.databaseName',
	];

	public function run(array $params)
	{
		$database = array_shift($params);

		if (empty($database))
		{
			$database = CLI::prompt(lang('DB.databaseName'), null, 'regex_match[\w.]');
		}

		$show = $this->db->query('SHOW DATABASES LIKE :database:', [
			'database' => $database,
		])->getRowArray();

		if ($show)
		{
			CLI::beep();
			CLI::error(lang('DB.databaseExists', [$database]));

			return;
		}

		$result = $this->forge->createDatabase($database);

		if ($result)
		{
			CLI::write(lang('DB.databaseCreated', [$database]), 'green');

			return;
		}

		CLI::error(lang('DB.databaseNotCreated', [$database]));
	}
}
