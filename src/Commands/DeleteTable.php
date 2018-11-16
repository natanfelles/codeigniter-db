<?php namespace NatanFelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\CLI;

/**
 * Class DeleteTable
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package NatanFelles\CodeIgniter\DB\Commands
 */
class DeleteTable extends AbstractCommand
{
	protected $name        = 'db:delete_table';
	protected $description = 'DB.deletesTable';
	protected $usage       = 'db:delete_table [table]';
	protected $arguments   = [
		'table' => 'DB.tableName',
	];

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

			$this->db->setDatabase($database);
		}

		$show = $this->db->query('SHOW TABLES LIKE :table:', [
			'table' => $table,
		])->getRowArray();

		if (empty($show))
		{
			CLI::beep();
			CLI::error(lang('DB.tableNotExists', [$table]));

			return;
		}

		$result = $this->forge->dropTable($table);

		if ($result)
		{
			CLI::write(lang('DB.tableDeleted', [$table]), 'green');

			return;
		}

		CLI::error(lang('DB.tableNotDeleted', [$table]));
	}
}
