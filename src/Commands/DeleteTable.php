<?php namespace natanfelles\CodeIgniter\DB\Commands;

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
	protected $usage       = 'db:delete_table [table]';
	protected $arguments   = [
		'table' => 'Table name',
	];

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description        = lang('DB.deletesTable');
		$this->arguments['table'] = lang('DB.tableName');
	}

	public function run(array $params)
	{
		$table = array_shift($params);

		if (empty($table))
		{
			$table = CLI::prompt(lang('DB.tableName'), null, 'regex_match[\w.]');
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
			CLI::error(lang('DB.tableNotExists', [$table]));

			return;
		}

		$result = \Config\Database::forge()->dropTable($table);

		if ($result)
		{
			CLI::write(lang('DB.tableDeleted', [$table]), 'green');

			return;
		}

		CLI::error(lang('DB.tableNotDeleted', [$table]));
	}
}
