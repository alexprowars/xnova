<?php
namespace App;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\ResultInterface;

class Database extends Mysql
{
	public function extractResult (ResultInterface $result, $field = false)
	{
		$data = [];

		if (!$field)
		{
			while ($res = $result->fetch())
				$data[] = $res;
		}
		else
		{
			while ($res = $result->fetch())
				$data[$res[$field]] = $res;
		}

		return $data;
	}

	public function update ($table, $fields, $values, $whereCondition = null, $dataTypes = null)
	{
		$placeholders = [];
		$updateValues = [];
		$bindDataTypes = [];

		$escape = ini_get("db.escape_identifiers");

		foreach ($values AS $position => $value)
		{
			if (!isset($fields[$position]))
				throw new \Exception("The number of values in the update is not the same as fields");

			$field = $fields[$position];

			if ($escape)
				$field = $this->escapeIdentifier($field);

			if (is_object($value))
				$placeholders[] = $field." = ".$value;
			else
			{
				if (is_null($value))
					$placeholders[] = $field." = null";
				else
				{
					$exp = substr($field, 0, 1);

					if ($exp == '-' || $exp == '+')
					{
						if (!is_numeric($value))
							$value = 0;

						$field = substr($field, 1);

						$placeholders[] = "`".$field."` = `".$field."` ".$exp." ?";
					}
					elseif ($exp == '@')
					{
						$field = substr($field, 1);

						$placeholders[] = "`".$field."` = `".$value."`";

						continue;
					}
					else
						$placeholders[] = "`".$field."` = ?";

					$updateValues[] = $value;

					if (is_array($dataTypes))
					{
						if (!isset($dataTypes[$position]))
							throw new \Exception("Incomplete number of bind types");

						$bindDataTypes[] = $dataTypes[$position];
					}
				}
			}
		}

		if ($escape)
			$table = $this->escapeIdentifier($table);

		$setClause = implode(", ", $placeholders);

		if ($whereCondition !== null)
		{
			$updateSql = "UPDATE ".$table." SET ".$setClause." WHERE ";

			if (is_string($whereCondition))
				$updateSql .= $whereCondition;
			else
			{
				if (!is_array($whereCondition))
					throw new \Exception("Invalid WHERE clause conditions");

				if (isset($whereCondition["conditions"]))
					$updateSql .= $whereCondition["conditions"];

				if (isset($whereCondition["bind"]))
					$updateValues = array_merge($updateValues, $whereCondition["bind"]);

				if (isset($whereCondition["bindTypes"]))
					$bindDataTypes = array_merge($bindDataTypes, $whereCondition["bindTypes"]);
			}
		}
		else
			$updateSql = "UPDATE ".$table." SET ".$setClause;

		if (!count($bindDataTypes))
			return $this->execute($updateSql, $updateValues);

		return $this->execute($updateSql, $updateValues, $bindDataTypes);
	}
}

?>