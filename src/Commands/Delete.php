<?php namespace NatanFelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\CLI;

/**
 * Class Delete
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package NatanFelles\CodeIgniter\DB\Commands
 */
class Delete extends AbstractCommand
{
	protected $name        = 'db:delete';
	protected $description = 'DB.deletesDatabase';
	protected $usage       = 'db:delete [database]';
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

		if (empty($show))
		{
			CLI::beep();
			CLI::error(lang('DB.databaseNotExists', [$database]));

			return;
		}

		$result = $this->forge->dropDatabase($database);

		if ($result)
		{
			CLI::write(lang('DB.databaseDeleted', [$database]), 'green');

			return;
		}

		CLI::error(lang('DB.databaseNotDeleted', [$database]));
	}
}
