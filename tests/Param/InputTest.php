<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Param;

use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\Tests\TestCase;

final class InputTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(InputInterface::CLASS, new Input([ 'foo' => 'bar' ]));
    }

    public function testWithEvent()
    {
        $input = new Input([ 'foo' => 'bar' ]);
        $this->assertEmpty($input->getEvent());
        $this->assertFalse($input->hasEvent());

        $input = $input->withEvent('something_happended');
        $this->assertEquals('something_happended', $input->getEvent());
        $this->assertTrue($input->hasEvent());
    }

    public function testFromOutput()
    {
        $input = Input::fromOutput(new Output('some_state', [ 'foo' => 'bar' ]));
        $this->assertEquals('bar', $input->get('foo'));
    }
}
