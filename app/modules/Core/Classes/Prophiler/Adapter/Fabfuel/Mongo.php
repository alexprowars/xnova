<?php
/**
 * @author @fabfuel <fabian@fabfuel.de>
 * @created 13.11.14, 20:41 
 */
namespace Friday\Core\Prophiler\Adapter\Fabfuel;

use Friday\Core\Prophiler\Adapter\AdapterAbstract;
use Friday\Core\Prophiler\Benchmark\BenchmarkInterface;
use Mongo\Profiler\ProfilerInterface;

/**
 * Class Mongo
 * Profiler adapter for Friday\Core\Mongo
 *
 * Usage:
 * $profiler = new \Friday\Core\Prophiler\Profiler();
 * $adapter = new \Friday\Core\Prophiler\Adapter\Friday\Core\Mongo($profiler);
 * $mongoDb->setProfiler($adapter);
 *
 * @package Friday\Core\Prophiler\Adapter\Fabfuel
 */
class Mongo extends AdapterAbstract implements ProfilerInterface
{
    /**
     * @var BenchmarkInterface[]
     */
    protected $benchmarks = [];

    /**
     * Start a new benchmark
     *
     * @param string $name Unique identifier like e.g. Class::Method (\Foobar\MyClass::doSomething)
     * @param array $metadata Addtional interesting metadata for this benchmark
     * @return string benchmark identifier
     */
    public function start($name, array $metadata = [])
    {
        $benchmark = $this->getProfiler()->start($name, $metadata, 'MongoDB');
        $identifier = spl_object_hash($benchmark);
        $this->benchmarks[$identifier] = $benchmark;
        return $identifier;
    }

    /**
     * Stop a running benchmark
     *
     * @param string $identifier benchmark identifier
     * @return void
     */
    public function stop($identifier)
    {
        $benchmark = $this->benchmarks[$identifier];
        $this->getProfiler()->stop($benchmark);
    }
}
