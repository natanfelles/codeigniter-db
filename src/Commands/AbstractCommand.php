<?php namespace NatanFelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Class AbstractCommand
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package NatanFelles\CodeIgniter\DB\Commands
 *
 * @property string|null                          $description
 * @property array|null                           $arguments
 * @property string                               $databaseGroup
 * @property \CodeIgniter\Database\BaseConnection $db
 * @property \CodeIgniter\Database\Forge          $forge
 */
abstract class AbstractCommand extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $group = 'Database';

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		if ($this->description)
		{
			$this->description = lang($this->description);
		}

		if ($this->arguments)
		{
			foreach ($this->arguments as &$argument)
			{
				$argument = lang($argument);
			}
		}
	}

	protected function setDatabaseGroup()
	{
		$this->databaseGroup = CLI::getOption('group') ?? config('Database')->defaultGroup;
	}

	protected function getDatabaseGroup(): string
	{
		if ( ! $this->databaseGroup)
		{
			$this->setDatabaseGroup();
		}

		return $this->databaseGroup;
	}

	public function __get(string $key)
	{
		if ($key === 'db' && ! isset($this->db))
		{
			return $this->db = Database::connect($this->getDatabaseGroup());
		}
		elseif ($key === 'forge' && ! isset($this->forge))
		{
			return $this->db = Database::forge($this->getDatabaseGroup());
		}

		return parent::__get($key);
	}
}
