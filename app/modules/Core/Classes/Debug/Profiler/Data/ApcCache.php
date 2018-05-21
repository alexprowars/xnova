<?php

namespace Friday\Core\Debug\Profiler\Data;

use Fabfuel\Prophiler\DataCollectorInterface;

class ApcCache implements DataCollectorInterface
{
    public function getTitle()
    {
        return 'APC cache';
    }

    public function getIcon()
    {
        return '<i class="fa fa-bolt"></i>';
    }

    public function getData()
    {
    	$data = [];

    	foreach (apcu_cache_info() as $key => $value)
		{
			if ($key == 'slot_distribution')
				continue;

			if ($key == 'cache_list')
			{
				$v = [];

				foreach ($value as $row)
					$v[] = $row['info'];

				$data[$key] = $v;

				continue;
			}

			$data[$key] = $value;
		}

        return $data;
    }
}
