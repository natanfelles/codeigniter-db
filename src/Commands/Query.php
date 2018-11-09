<?php namespace natanfelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Query
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package natanfelles\CodeIgniter\DB\Commands
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

		$this->description        = lang('DB.executesQuery');
		$this->arguments['query'] = lang('DB.queryToExecute');
	}

	public function run(array $params)
	{
		$query = array_shift($params);

		if (empty($query))
		{
			$query = CLI::prompt(lang('DB.query'), null, 'required');
		}

		CLI::write(
			CLI::color(lang('DB.query') . ': ', 'white')
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

			return;
		}

		if ($db->getLastQuery()->isWriteType())
		{
			CLI::write(lang('DB.affectedRows', [$db->affectedRows()]));

			if ($db->insertID())
			{
				CLI::write(
					lang('DB.lastInsertID') . ': ' . CLI::color($db->insertID(), 'green')
				);
			}

			return;
		}

		$result = $result->getResultArray();

		if (empty($result))
		{
			CLI::write(lang('DB.noResults'));

			return;
		}

		CLI::table($result, array_keys($result[0]));
	}
}
