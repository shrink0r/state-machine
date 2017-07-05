<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\State;

use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\Param\Settings;
use Daikon\StateMachine\State\FinalState;
use Daikon\StateMachine\State\InitialState;
use Daikon\StateMachine\State\InteractiveState;
use Daikon\StateMachine\State\State;
use Daikon\StateMachine\State\ValidatorInterface;
use Daikon\StateMachine\Tests\State\Fixture\StateWithRequiredSettings;
use Daikon\StateMachine\Tests\TestCase;

final class StateTest extends TestCase
{
    public function testExecute()
    {
        $state = $this->createState('foobar');
        $output = $state->execute(new Input([ 'foo' => 'bar' ]));
        $this->assertInstanceOf(OutputInterface::CLASS, $output);
    }

    public function testGetName()
    {
        $state = $this->createState('foobar');
        $this->assertEquals('foobar', $state->getName());
    }

    public function testIsFinal()
    {
        $this->assertFalse($this->createState('foobar')->isFinal());
        $this->assertTrue($this->createState('foobar', FinalState::CLASS)->isFinal());
    }

    public function testIsInitial()
    {
        $this->assertFalse($this->createState('foobar')->isInitial());
        $this->assertTrue($this->createState('foobar', InitialState::CLASS)->isInitial());
    }

    public function testIsInteractive()
    {
        $this->assertFalse($this->createState('foobar')->isInteractive());
        $this->assertTrue($this->createState('foobar', InteractiveState::CLASS)->isInteractive());
    }

    public function testGetValidator()
    {
        $state = $this->createState('foobar');
        $this->assertInstanceOf(ValidatorInterface::CLASS, $state->getValidator());
    }

    public function testGetSettings()
    {
        $state = $this->createState('foobar', State::CLASS, new Settings([ 'foo' => 'bar' ]));
        $this->assertInstanceOf(Settings::CLASS, $state->getSettings());
        $this->assertEquals('bar', $state->getSettings()->get('foo'));
    }

    public function testGetSetting()
    {
        $state = $this->createState('foobar', State::CLASS, new Settings([ 'foo' => 'bar' ]));
        $this->assertEquals('bar', $state->getSetting('foo'));
    }

    /**
     * @expectedException \Daikon\StateMachine\Exception\ConfigException
     * @expectedExceptionMessage Trying to configure state "foobar" without required setting "foobar".
     */
    public function testMissingRequiredSetting()
    {
        $this->createState('foobar', StateWithRequiredSettings::CLASS);
    } // @codeCoverageIgnore
}
