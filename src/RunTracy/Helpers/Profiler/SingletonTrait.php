<?php

declare(strict_types=1);

namespace RunTracy\Helpers\Profiler;

/**
 * Singleton trait.
 *
 * @author   Petr Knap <dev@petrknap.cz>
 *
 * @since    2015-04-18
 *
 * @category Patterns
 *
 * @version  0.4
 *
 * @license  https://github.com/petrknap/php-singleton/blob/master/LICENSE MIT
 */
trait SingletonTrait
{
    /**
     * @var self[]
     */
    private static $instances = [];

    /**
     * Returns instance, if instance does not exist then creates new one and returns it.
     *
     * @return $this
     */
    public static function getInstance()
    {
        $self = static::class;
        if (!isset(self::$instances[$self])) {
            self::$instances[$self] = new $self();
        }

        return self::$instances[$self];
    }

    /**
     * @return bool true if has instance, otherwise false
     */
    public static function hasInstance()
    {
        $self = static::class;

        return isset(self::$instances[$self]);
    }
}
