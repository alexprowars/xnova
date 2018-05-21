<?php

namespace Friday\Core\Debug\Profiler\Plugins;

use Fabfuel\Prophiler\Benchmark\BenchmarkInterface;
use Fabfuel\Prophiler\Plugin\PluginAbstract;
use Phalcon\Events\Event;

class Router extends PluginAbstract
{
    /**
     * @var BenchmarkInterface
     */
    protected $benchmarkRoute;

    public function beforeCheckRoutes(Event $event)
    {
        $name = get_class($event->getSource()) . '::сheckRoutes';
        $this->benchmarkRoute = $this->getProfiler()->start($name, [], 'Router ');
    }

    public function afterCheckRoutes()
    {
        $this->getProfiler()->stop($this->benchmarkRoute);
    }
}
