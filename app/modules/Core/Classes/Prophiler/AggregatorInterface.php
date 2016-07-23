<?php
/**
 * @author @fabfuel <fabian@fabfuel.de>
 * @created: 23.04.15 17:44
 */

namespace Friday\Core\Prophiler;

use Friday\Core\Prophiler\Aggregator\AggregationInterface;
use Friday\Core\Prophiler\Benchmark\BenchmarkInterface;

interface AggregatorInterface extends \Countable
{
    /**
     * Add Benchmark to aggregator
     *
     * @param BenchmarkInterface $benchmark
     * @return bool
     */
    public function aggregate(BenchmarkInterface $benchmark);

    /**
     * @param BenchmarkInterface $benchmark
     * @return string
     */
    public function getCommand(BenchmarkInterface $benchmark);

    /**
     * Get the title of this aggregator
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get the icon HTML markup
     *
     * For example font-awesome icons: <i class="fa fa-pie-chart"></i>
     * See: http://fortawesome.github.io/Font-Awesome/icons/
     *
     * @return string
     */
    public function getIcon();

    /**
     * @return string
     */
    public function getSeverity();

    /**
     * @return AggregationInterface[]
     */
    public function getAggregations();
}
