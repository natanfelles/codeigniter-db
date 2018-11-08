<?php namespace App\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Query
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package App\Commands\Database
 */
class Query extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:query';
	protected $description = 'Executes a SQL query';

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description = lang('Database.executesQuery');
	}

	public function run(array $params)
	{
		$query = array_shift($params);

		if (empty($query))
		{
			$query = CLI::prompt(lang('Database.query'), null, 'required');
		}

		// TODO: Transaction
		$db = \Config\Database::connect();

		try
		{
			$db->transStart();
			$result = $db->query($query);
			$db->transCommit();
		}
		catch (\Exception $e)
		{
			$db->transRollback();

			CLI::beep();
			CLI::error($e->getMessage());
			CLI::newLine();
			exit;
		}

		// TODO
		if (\is_bool($result))
		{
			$result ? CLI::write('OK', 'green') : CLI::error('FALSE');
		}
		else
		{
			$result = $result->getResultArray();

			CLI::table($result, isset($result[0]) ? array_keys($result[0]) : []);
			CLI::newLine();
		}
	}
}
