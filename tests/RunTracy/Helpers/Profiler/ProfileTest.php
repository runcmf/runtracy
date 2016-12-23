<?php
/**
 * Copyright 2016 1f7.wizard@gmail.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Tests\RunTracy\Helpers\Profiler;

use RunTracy\Helpers\Profiler\Profile;

/**
 * Class ProfileTest
 * @package Tests\RunTracy\Helpers\Profiler
 */
class ProfileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \RunTracy\Helpers\Profiler\Profile::class
     */
    public function testProfile()
    {
        $p = new Profile();

        $this->assertInstanceOf('\RunTracy\Helpers\Profiler\Profile', $p);
        $this->assertTrue(empty($p->meta));

        // fill meta
        $p->absoluteDuration = 0.05;
        $p->duration = 0.03;
        $p->absoluteMemoryUsageChange = 640;
        $p->memoryUsageChange = 1024;

        $p->meta = $p->jsonSerialize();
//        fwrite(STDERR, '$p->meta: ' . print_r($p->meta) . " ###\n");
        $this->assertFalse(empty($p->meta));
    }
}
