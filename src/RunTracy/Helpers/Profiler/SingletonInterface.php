<?php

namespace RunTracy\Helpers\Profiler;

/**
 * Singleton interface
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-01-09
 * @category Patterns
 * @package  PetrKnap\Php\Singleton
 * @version  0.1
 * @license  https://github.com/petrknap/php-singleton/blob/master/LICENSE MIT
 */
interface SingletonInterface
{
    /**
     * Returns instance, if instance does not exist then creates new one and returns it
     *
     * @return $this
     */
    public static function getInstance();
}
