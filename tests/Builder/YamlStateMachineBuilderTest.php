<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Builder;

use Daikon\StateMachine\Builder\YamlStateMachineBuilder;
use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Tests\TestCase;

final class YamlStateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new YamlStateMachineBuilder($this->fixture('statemachine')))->build();

        $initial_input = new Input([ 'transcoding_required' => true ]);
        $initial_output = $state_machine->execute($initial_input);
        $current_state = $initial_output->getCurrentState();
        $this->assertEquals('transcoding', $current_state);
        $input = Input::fromOutput($initial_output)->withEvent('video_transcoded');
        $final_output = $state_machine->execute($input, $current_state);
        $this->assertEquals('ready', $final_output->getCurrentState());
    }

    public function testNonStringConstraint()
    {
        (new YamlStateMachineBuilder($this->fixture('non_string_constraint')))->build();
    }

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     */
    public function testNonExistantYamlFile()
    {
        new YamlStateMachineBuilder(__DIR__.'/foobar.yaml');
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     */
    public function testInvalidStateMachineSchema()
    {
        (new YamlStateMachineBuilder($this->fixture('invalid_schema')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     * @expectedExceptionMessage
        Trying to provide custom state that isn't initial but marked as initial in config.
     */
    public function testInconsistentInitialState()
    {
        (new YamlStateMachineBuilder($this->fixture('inconsistent_initial')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     * @expectedExceptionMessage
        Trying to provide custom state that isn't interactive but marked as interactive in config.
     */
    public function testInconsistentInteractiveState()
    {
        (new YamlStateMachineBuilder($this->fixture('inconsistent_interactive')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     * @expectedExceptionMessage
        Trying to provide custom state that isn't final but marked as final in config.
     */
    public function testInconsistentFinalState()
    {
        (new YamlStateMachineBuilder($this->fixture('inconsistent_final')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\MissingImplementation
     */
    public function testNonImplementedState()
    {
        (new YamlStateMachineBuilder($this->fixture('non_implemented_state')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\MissingImplementation
     */
    public function testNonImplementedTransition()
    {
        (new YamlStateMachineBuilder($this->fixture('non_implemented_transition')))->build();
    } // @codeCoverageIgnore

    private function fixture(string $name): string
    {
        return __DIR__.'/Fixture/Yaml/'.$name.'.yaml';
    }
}
