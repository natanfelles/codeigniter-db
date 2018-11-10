<?php namespace natanfelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class ListAll
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package natanfelles\CodeIgniter\DB\Commands
 */
class ListAll extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:list';
	protected $description = 'Lists Databases';

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description = lang('DB.listsDatabases');
	}

	public function run(array $params)
	{
		$sql = 'SELECT SCHEMA_NAME AS "database",
DEFAULT_COLLATION_NAME AS "collation"
FROM information_schema.SCHEMATA
ORDER BY SCHEMA_NAME';

		$databases = \Config\Database::connect()->query($sql)->getResultArray();

		if ( ! $databases)
		{
			CLI::write(lang('DB.noDatabases'));

			return;
		}

		$sql = 'SELECT TABLE_SCHEMA AS "database",
SUM(DATA_LENGTH + INDEX_LENGTH) AS "size",
COUNT(DISTINCT CONCAT(TABLE_SCHEMA, ".", TABLE_NAME)) AS "tables"
FROM information_schema.TABLES
GROUP BY TABLE_SCHEMA';

		$infos = \Config\Database::connect()->query($sql)->getResultArray();

		helper('number');

		foreach ($databases as &$database)
		{
			$database['size'] = $database['tables'] = 0;

			foreach ($infos as $info)
			{
				if ($info['database'] === $database['database'])
				{
					$database['tables'] = $info['tables'];
					$database['size']   = number_to_size($info['size']);
					break;
				}
			}
		}

		CLI::table($databases, [
			lang('DB.database'),
			lang('DB.collation'),
			lang('DB.tables'),
			lang('DB.size'),
		]);

		CLI::write(lang('DB.total') . ': ' . count($databases));
	}
}
