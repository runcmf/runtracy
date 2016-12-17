<?php

namespace RunTracy\Helpers\Profiler;

use JsonSerializable;

/**
 * Profile
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2015-12-19
 * @license  https://github.com/petrknap/php-profiler/blob/master/LICENSE MIT
 */
class Profile implements JsonSerializable
{
    #region JSON keys
    const ABSOLUTE_DURATION = "absolute_duration";
    const DURATION = "duration";
    const ABSOLUTE_MEMORY_USAGE_CHANGE = "absolute_memory_usage_change";
    const MEMORY_USAGE_CHANGE = "memory_usage_change";
    #endregion

    /**
     * @var array
     */
    public $meta = [];

    /**
     * Absolute duration in seconds
     *
     * @var float
     */
    public $absoluteDuration;

    /**
     * Duration in seconds
     *
     * @var float
     */
    public $duration;

    /**
     * Absolute memory usage change in bytes
     *
     * @var int
     */
    public $absoluteMemoryUsageChange;

    /**
     * Memory usage change in bytes
     *
     * @var int
     */
    public $memoryUsageChange;

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return array_merge(
            $this->meta,
            [
                self::ABSOLUTE_DURATION => $this->absoluteDuration,
                self::DURATION => $this->duration,
                self::ABSOLUTE_MEMORY_USAGE_CHANGE => $this->absoluteMemoryUsageChange,
                self::MEMORY_USAGE_CHANGE => $this->memoryUsageChange
            ]
        );
    }
}
