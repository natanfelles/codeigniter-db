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
	protected $usage       = 'db:query [query]';
	protected $arguments   = [
		'query' => 'The query to execute',
	];

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description        = lang('Database.executesQuery');
		$this->arguments['query'] = lang('Database.queryToExecute');
	}

	public function run(array $params)
	{
		$query = array_shift($params);

		if (empty($query))
		{
			$query = CLI::prompt(lang('Database.query'), null, 'required');
		}

		CLI::write(
			CLI::color(lang('Database.query') . ': ', 'white')
			. CLI::color($query, 'yellow')
		);

		$db = \Config\Database::connect();

		try
		{
			$result = $db->query($query);
		}
		catch (\Exception $e)
		{
			CLI::beep();
			CLI::error($e->getMessage());
			CLI::newLine();
			exit;
		}

		if ($db->getLastQuery()->isWriteType())
		{
			CLI::newLine();
			CLI::write(lang('Database.affectedRows', [$db->affectedRows()]));

			if ($db->insertID())
			{
				CLI::write(
					lang('Database.lastInsertID') . ': ' . CLI::color($db->insertID(), 'green')
				);
			}
		}
		else
		{
			$result = $result->getResultArray();

			if (empty($result))
			{
				CLI::newLine();
				CLI::write(lang('Database.noResults'));
			}
			else
			{
				CLI::table($result, array_keys($result[0]));
			}
		}

		CLI::newLine();
	}
}
