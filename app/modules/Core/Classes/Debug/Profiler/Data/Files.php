<?php

namespace Friday\Core\Debug\Profiler\Data;

use Fabfuel\Prophiler\DataCollectorInterface;

class Files implements DataCollectorInterface
{
    public function getTitle()
    {
        return 'Files';
    }

    public function getIcon()
    {
        return '<i class="fa fa-copy"></i>';
    }

    public function getData()
    {
        return get_included_files();
    }
}
