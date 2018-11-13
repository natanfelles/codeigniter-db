<?php namespace natanfelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class ShowTable
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package natanfelles\CodeIgniter\DB\Commands
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
	/**
	 * @var \CodeIgniter\Database\BaseConnection
	 */
	protected $db;

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description        = lang('DB.showsTable');
		$this->arguments['table'] = lang('DB.tableName');
	}

	public function run(array $params)
	{
		$table = array_shift($params);

		if (empty($table))
		{
			$table = CLI::prompt(lang('DB.tableName'), null, 'regex_match[\w.]');
			CLI::newLine();
		}

		$this->db = \Config\Database::connect();

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

		// FIELDS
		$fields = $this->getFields($table);
		CLI::write(
			CLI::color(lang('DB.table') . ': ', 'white')
			. CLI::color($table, 'yellow')
		);
		CLI::table($fields, array_keys($fields[0]));
		CLI::newLine();

		// INDEXES
		$indexes = $this->getIndexes($table);

		if ($indexes)
		{
			CLI::write(lang('DB.indexes'), 'white');
			CLI::table($indexes, array_keys($indexes[0]));
			CLI::newLine();
		}

		// FOREIGN KEYS
		$foreign_keys = $this->getForeignKeys($table);

		if ($foreign_keys)
		{
			CLI::write(lang('DB.foreignKeys'), 'white');
			CLI::table($foreign_keys, array_keys($foreign_keys[0]));
			CLI::newLine();
		}
	}

	protected function getFields(string $table): array
	{
		$show = $this->db->query('SHOW FULL COLUMNS FROM ' . $this->db->escapeIdentifiers($table))
		                 ->getResultArray();

		if ($show)
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
					lang('DB.column')   => $col['field'] . ($col['primary']
							? ' PRIMARY' : ''),
					lang('DB.type')     => $col['full_type'] . ($col['collation']
							? ' ' . $col['collation'] : '') . ($col['auto_increment']
							? ' ' . lang('DB.autoIncrement') : ''),
					lang('DB.nullable') => $col['null'] ? lang('DB.yes')
						: lang('DB.no'),
					lang('DB.default')  => $col['default'],
					lang('DB.comment')  => $col['comment'],
				];
			}

			return $cols;
		}

		return [];
	}

	protected function getIndexes(string $table): array
	{
		$indexes = $this->db->getIndexData($table);
		$keys    = [];

		if ($indexes)
		{
			$lang_name    = lang('DB.name');
			$lang_type    = lang('DB.type');
			$lang_columns = lang('DB.columns');

			foreach ($indexes as $index)
			{
				$keys[] = [
					$lang_name    => $index->name,
					$lang_type    => $index->type,
					$lang_columns => implode(', ', $index->fields),
				];
			}
		}

		return $keys;
	}

	protected function getForeignKeys(string $table): array
	{
		$show = $this->db->query('SHOW CREATE TABLE ' . $this->db->escapeIdentifiers($table))
		                 ->getRowArray();

		if ($show)
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
					lang('DB.source') => $fk['source'],
					lang('DB.target') => (! empty($fk['database'])
							? $fk['database'] . '.'
							: '')
						. $fk['table'] . '(' . $fk['field'] . ')',
					'ON DELETE'       => $fk['on_delete'],
					'ON UPDATE'       => $fk['on_update'],
				];
			}

			return $fks;
		}

		return [];
	}
}
