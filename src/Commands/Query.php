<?php namespace NatanFelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\CLI;

/**
 * Class Query
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package NatanFelles\CodeIgniter\DB\Commands
 */
class Query extends AbstractCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:query';
	protected $description = 'DB.executesQuery';
	protected $usage       = 'db:query [query]';
	protected $arguments   = [
		'query' => 'DB.queryToExecute',
	];

	public function run(array $params)
	{
		$query = array_shift($params);

		if (empty($query))
		{
			$query = CLI::prompt(lang('DB.query'), null, 'required');
		}

		CLI::write(
			CLI::color(lang('DB.query') . ': ', 'white') . CLI::color($query, 'yellow')
		);

		try
		{
			$result = $this->db->query($query);
		}
		catch (\Exception $e)
		{
			CLI::beep();
			CLI::error($e->getMessage());

			return;
		}

		if ($this->db->getLastQuery()->isWriteType())
		{
			CLI::write(lang('DB.affectedRows', [$this->db->affectedRows()]));

			if ($this->db->insertID())
			{
				CLI::write(
					lang('DB.lastInsertID') . ': ' . CLI::color($this->db->insertID(), 'green')
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
