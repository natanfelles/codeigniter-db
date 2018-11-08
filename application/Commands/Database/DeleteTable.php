<?php namespace App\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class DeleteTable
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package App\Commands\Database
 */
class DeleteTable extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:delete_table';
	protected $description = 'Deletes a Database Table';

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description = lang('Database.deletesTable');
	}

	public function run(array $params)
	{
		$table = array_shift($params);

		if (empty($table))
		{
			$table = CLI::prompt(lang('Database.tableName'), null, 'regex_match[\w.]');
			CLI::newLine();
		}

		if (strpos($table, '.') !== false)
		{
			[$database, $table] = explode('.', $table, 2);

			\Config\Database::connect()->setDatabase($database);
		}

		$show = \Config\Database::connect()
		                        ->query('SHOW TABLES LIKE :table:', [
			                        'table' => $table,
		                        ])->getRowArray();

		if (empty($show))
		{
			CLI::beep();
			CLI::error(lang('Database.tableNotExists', [$table]));
			CLI::newLine();
			exit;
		}

		$result = \Config\Database::forge()->dropTable($table);

		if ($result)
		{
			CLI::write(lang('Database.tableDeleted', [$table]), 'green');
			CLI::newLine();
			exit;
		}

		CLI::error(lang('Database.tableNotDeleted', [$table]));
		CLI::newLine();
	}
}
