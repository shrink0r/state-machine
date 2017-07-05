<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Builder;

use Daikon\StateMachine\Builder\StateMachineSchema;
use Daikon\StateMachine\Tests\TestCase;
use Shrink0r\PhpSchema\FactoryInterface;

final class StateMachineSchemaTest extends TestCase
{
    public function testGetName()
    {
        $schema = new StateMachineSchema;
        $this->assertEquals('state-machine', $schema->getName());
    }

    public function testGetType()
    {
        $schema = new StateMachineSchema;
        $this->assertEquals('assoc', $schema->getType());
    }

    public function testGetCustomTypes()
    {
        $schema = new StateMachineSchema;
        $this->assertEquals([ 'transition' ], array_keys($schema->getCustomTypes()));
    }

    public function testGetProperties()
    {
        $schema = new StateMachineSchema;
        $expected_keys = [ 'class', 'name', 'states' ];
        foreach (array_keys($schema->getProperties()) as $key) {
            $this->assertContains($key, $expected_keys);
        }
    }

    public function testGetFactory()
    {
        $schema = new StateMachineSchema;
        $this->assertInstanceOf(FactoryInterface::CLASS, $schema->getFactory());
    }
}
