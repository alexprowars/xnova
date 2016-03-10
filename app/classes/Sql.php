<?php
namespace App;

use Phalcon\Mvc\User\Component;

class Sql extends Component
{
	private $query 	= '';
	private $fields = [];
	private $from 	= [];
	private $mode	= 'select';
	private $where	= '';

	/**
	 * @var sql
	 */
	private static $instance;

	public function __construct() {}

	public static function build()
    {
        if (!isset(self::$instance))
		{
            $className = __CLASS__;
            self::$instance = new $className;
        }

        return self::$instance;
    }

	public function getNativeSql ()
	{
		if (!$this->query)
			$this->buildQuery();

		return $this->query;
	}

	public function clear ()
	{
		$this->query 	= '';
		$this->fields 	= [];
		$this->from 	= [];
		$this->mode		= 'select';
		$this->where	= '';
	}

	public function __call ($name, $arguments)
	{
		if (substr($name, 0, 4) == 'set_')
		{
			$this->fields[substr($name, 4)] = $arguments[0];
		}

		return $this;
	}

	public function update($tableName)
	{
		$this->clear();

		$this->from[] 	= $tableName;
		$this->mode 	= 'update';

		return $this;
	}

	public function insert($tableName)
	{
		$this->clear();

		$this->from[] 	= $tableName;
		$this->mode 	= 'insert';

		return $this;
	}

	public function set ($coloumns)
	{
		$this->fields = array_merge($this->fields, $coloumns);

		return $this;
	}

	public function setField ($key, $value)
	{
		$this->fields[$key] = $value;

		return $this;
	}

	public function where ($field, $expression, $value)
	{
		if (!in_array($expression, Array('=', '<', '>', '<=', '>=', 'IN', '!=')))
			$expression = '=';

		$this->where .= ' `'.$field.'` '.$expression.' ';

		if (!is_string($value) && is_array($value))
		{
			$this->where .= '(';

			foreach ($value AS $i => $val)
				$this->where .= ($i > 0 ? ', ' : '').'\''.$val.'\'';

			$this->where .= ')';
		}
		else
			$this->where .= '\''.$value.'\'';

		return $this;
	}

	public function addExp ($expression)
	{
		$expression = strtoupper($expression);

		if (!in_array($expression, ['AND', 'OR']))
			$expression = 'AND';

		$this->where .= ' '.$expression.' ';

		return $this;
	}

	public function addAND ()
	{
		$this->addExp('AND');

		return $this;
	}

	public function addOR ()
	{
		$this->addExp('OR');

		return $this;
	}

	public function addBracket ($position)
	{
		$position = strtoupper($position);

		if (!in_array($position, ['LEFT', 'RIGHT']))
			$position = 'LEFT';

		$this->where .= ($position == 'LEFT' ? ' ( ' : ' ) ');

		return $this;
	}

	public function addLBracket()
	{
		$this->addBracket('LEFT');

		return $this;
	}

	public function addRBracket()
	{
		$this->addBracket('RIGHT');

		return $this;
	}

	private function buildQuery ()
	{
		switch ($this->mode)
		{
			case 'update':

				if (!count($this->fields) || !count($this->from))
					return false;

				$this->query .= 'UPDATE `'.$this->from[0].'` SET ';

				$i = 0;

				foreach ($this->fields AS $key => $value)
				{
					$this->query .= ($i > 0 ? ', ' : '');

					$exp = substr($key, 0, 1);

					if (in_array($exp, Array('+', '-')))
					{
						if (!is_numeric($value) || !$value)
							$value = 0;

						$key = substr($key, 1);

						$this->query .= '`'.$key.'` = `'.$key.'` '.$exp.' \''.$value.'\'';
					}
					elseif ($exp == '@')
					{
						$key = substr($key, 1);

						$this->query .= '`'.$key.'` = `'.$value.'`';
					}
					else
						$this->query .= '`'.$key.'` = \''.$value.'\'';

					$i++;
				}

				if ($this->where != '')
					$this->query .= ' WHERE '.$this->where;

			break;

			case 'insert':

				if (!count($this->fields) || !count($this->from))
					return false;

				$this->query .= 'INSERT INTO `'.$this->from[0].'` SET ';

				$i = 0;

				foreach ($this->fields AS $key => $value)
				{
					$this->query .= ($i > 0 ? ', ' : '').'`'.$key.'` = \''.$value.'\'';

					$i++;
				}

			break;
		}

		return true;
	}

	public function execute ()
	{
		$q = $this->getNativeSql();

		$this->clear();

		if ($q != '')
			return $this->db->query($q);
		else
			return false;
	}
}

?>