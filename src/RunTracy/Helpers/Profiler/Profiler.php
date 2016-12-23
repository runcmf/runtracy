<?php

namespace RunTracy\Helpers\Profiler;

use /** @noinspection PhpInternalEntityUsedInspection */ RunTracy\Helpers\Profiler\ProfilerService;
use RunTracy\Helpers\Profiler\AdvancedProfiler;
use RunTracy\Helpers\Profiler\Profile;

class Profiler extends AdvancedProfiler
{
    /**
     * @var bool
     */
    protected static $enabled = false;

    /**
     * @var Profile[]
     */
    protected static $stack = [];

    /**
     * @var callable
     */
    protected static $postProcessor = null;

    /**
     * @inheritdoc
     */
    public static function enable($real_usage = false)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        ProfilerService::init();
        parent::enable($real_usage);
    }

    /**
     * @inheritdoc
     * @internal
     */
    public static function setPostProcessor(callable $postProcessor)
    {
        parent::setPostProcessor($postProcessor);
    }
}
