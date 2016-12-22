<?php

namespace RunTracy\Tests;

use RunTracy\Helpers\Profiler\Profiler;
use RunTracy\Helpers\Profiler\ProfilerService;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testEnableCallsProfilerServiceInit()
    {
        Profiler::enable();

        Profiler::start();
        Profiler::finish();

        /** @noinspection PhpInternalEntityUsedInspection */
        $this->assertCount(1, ProfilerService::getInstance()->getProfiles());
    }
}
