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
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\Tests\TestCase;

final class OutputTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(OutputInterface::CLASS, new Output('initial'));
    }

    public function testToArray()
    {
        $params = [ 'foo' => 'bar', 'msg' => 'hello world' ];
        $output = new Output('initial', $params);
        $this->assertEquals([ 'params' => $params, 'current_state' => 'initial' ], $output->toArray());
    }

    public function testWithCurrentState()
    {
        $output = new Output('initial', [ 'foo' => 'bar', 'msg' => 'hello world' ]);
        $this->assertEquals('initial', $output->getCurrentState());
        $output = $output->withCurrentState('some_other_state');
        $this->assertEquals('some_other_state', $output->getCurrentState());
    }

    public function testFromInput()
    {
        $params = [ 'foo' => 'bar', 'msg' => 'hello world' ];
        $output = Output::fromInput('initial', new Input($params));
        $this->assertInstanceOf(OutputInterface::CLASS, $output);
        $this->assertEquals([ 'params' => $params, 'current_state' => 'initial' ], $output->toArray());
    }
}
