<?php namespace App\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class ShowTable
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package App\Commands\Database
 */
class ShowTable extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:show_table';
	protected $description = 'Shows a Database Table Structure';
	protected $usage       = 'db:show_table [table]';
	protected $arguments   = [
		'table' => 'Table name',
	];

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description        = lang('Database.showsTable');
		$this->arguments['table'] = lang('Database.tableName');
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

		// FIELDS
		$fields = $this->getFields($table);
		CLI::write(
			CLI::color(lang('Database.table') . ': ', 'white')
			. CLI::color($table, 'yellow')
		);
		CLI::table($fields, array_keys($fields[0]));
		CLI::newLine();

		// INDEXES
		$indexes = $this->getIndexes($table);

		if ($indexes)
		{
			CLI::write(lang('Database.indexes'), 'white');
			CLI::table($indexes, array_keys($indexes[0]));
			CLI::newLine();
		}

		// FOREIGN KEYS
		$foreign_keys = $this->getForeignKeys($table);

		if ($foreign_keys)
		{
			CLI::write(lang('Database.foreignKeys'), 'white');
			CLI::table($foreign_keys, array_keys($foreign_keys[0]));
			CLI::newLine();
		}
	}

	public function getFields(string $table): array
	{
		$show = \Config\Database::connect()
		                        ->query('SHOW FULL COLUMNS FROM ' . $table)
		                        ->getResultArray();

		if ( ! empty($show))
		{
			$columns = [];

			foreach ($show as $row)
			{
				preg_match('~^([^( ]+)(?:\\((.+)\\))?( unsigned)?( zerofill)?$~', $row['Type'],
					$match);

				$columns[] = [
					'field'          => $row['Field'],
					'full_type'      => $row['Type'],
					'type'           => $match[1] ?? null,
					'length'         => $match[2] ?? null,
					'unsigned'       => ltrim(($match[3] ?? null) . ($match[4] ?? null)),
					'default'        => ($row['Default'] !== '' || preg_match('~char|set~',
						$match[1]) ? $row['Default'] : null),
					'null'           => ($row['Null'] === 'YES'),
					'auto_increment' => ($row['Extra'] === 'auto_increment'),
					'on_update'      => (preg_match('~^on update (.+)~i', $row['Extra'], $match)
						? $match[1] : ''),
					'collation'      => $row['Collation'],
					'privileges'     => array_flip(preg_split('~, *~', $row['Privileges'])),
					'comment'        => $row['Comment'],
					'primary'        => ($row['Key'] === 'PRI'),
				];
			}

			$cols = [];

			foreach ($columns as $col)
			{
				$cols[] = [
					lang('Database.column')   => $col['field'] . ($col['primary']
							? ' PRIMARY' : ''),
					lang('Database.type')     => $col['full_type'] . ($col['collation']
							? ' ' . $col['collation'] : '') . ($col['auto_increment']
							? ' ' . lang('Database.autoIncrement') : ''),
					lang('Database.nullable') => $col['null'] ? lang('Database.yes')
						: lang('Database.no'),
					lang('Database.default')  => $col['default'],
					lang('Database.comment')  => $col['comment'],
				];
			}

			return $cols;
		}

		return [];
	}

	public function getIndexes(string $table): array
	{
		$indexes = \Config\Database::connect()
		                           ->query('SHOW INDEX FROM ' . $table)->getResultArray();

		if ( ! empty($indexes))
		{
			$i = [];

			foreach ($indexes as $index)
			{
				$i[] = [
					lang('Database.name')    => $index['Key_name'],
					lang('Database.type')    => ($index['Key_name'] === 'PRIMARY'
						? 'PRIMARY'
						: ($index['Index_type'] === 'FULLTEXT'
							? 'FULLTEXT'
							: ($index['Non_unique'] ? ($index['Index_type'] === 'SPATIAL'
								? 'SPATIAL' : 'INDEX') : 'UNIQUE'))),
					lang('Database.columns') => $index['Column_name'],
				];
			}

			return $i;
		}

		return [];
	}

	public function getForeignKeys(string $table): array
	{
		$show = \Config\Database::connect()
		                        ->query('SHOW CREATE TABLE ' . $table)->getRowArray();

		if ( ! empty($show))
		{
			$create_table = $show['Create Table'];

			$on_actions = 'RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT';

			$pattern = '`(?:[^`]|``)+`';

			preg_match_all("~CONSTRAINT ($pattern) FOREIGN KEY ?\\(((?:$pattern,? ?)+)\\) REFERENCES ($pattern)(?:\\.($pattern))? \\(((?:$pattern,? ?)+)\\)(?: ON DELETE ($on_actions))?(?: ON UPDATE ($on_actions))?~",
				$create_table, $matches, PREG_SET_ORDER);

			$foreign_keys = [];

			foreach ($matches as $match)
			{
				preg_match_all("~$pattern~", $match[2], $source);
				preg_match_all("~$pattern~", $match[5], $target);
				$foreign_keys[] = [
					'index'     => str_replace('`', '', $match[1]),
					'source'    => str_replace('`', '', $source[0][0]),
					'database'  => str_replace('`', '', $match[4] !== '' ? $match[3] : $match[4]),
					'table'     => str_replace('`', '', $match[4] !== '' ? $match[4] : $match[3]),
					'field'     => str_replace('`', '', $target[0][0]),
					'on_delete' => (! empty($match[6]) ? $match[6] : 'RESTRICT'),
					'on_update' => (! empty($match[7]) ? $match[7] : 'RESTRICT'),
				];
			}

			$fks = [];

			foreach ($foreign_keys as $fk)
			{
				$fks[] = [
					lang('Database.source') => $fk['source'],
					lang('Database.target') => (! empty($fk['database'])
							? $fk['database'] . '.'
							: '')
						. $fk['table'] . '(' . $fk['field'] . ')',
					'ON DELETE'             => $fk['on_delete'],
					'ON UPDATE'             => $fk['on_update'],
				];
			}

			return $fks;
		}

		return [];
	}
}
