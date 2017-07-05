<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Builder;

use Daikon\StateMachine\Error\ConfigError;
use Daikon\StateMachine\StateMachine;
use Daikon\StateMachine\StateMachineInterface;
use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;

final class ArrayStateMachineBuilder implements StateMachineBuilderInterface
{
    private $config;

    private $factory;

    public function __construct(array $config, FactoryInterface $factory = null)
    {
        $this->config = $config;
        $this->factory = $factory ?? new Factory;
    }

    public function build(): StateMachineInterface
    {
        $data = $this->config;
        $result = (new StateMachineSchema)->validate($data);
        if ($result instanceof Error) {
            throw new ConfigError('Invalid state-machine configuration given: '.print_r($result->unwrap(), true));
        }
        list($states, $transitions) = $this->realizeConfig($data['states']);
        $stateMachineClass = Maybe::unit($this->config)->class->get() ?? StateMachine::CLASS;
        return (new StateMachineBuilder($stateMachineClass))
            ->addStateMachineName($data['name'])
            ->addStates($states)
            ->addTransitions($transitions)
            ->build();
    }

    private function realizeConfig(array $config): array
    {
        $states = [];
        $transitions = [];
        foreach ($config as $name => $stateConfig) {
            $states[] = $this->factory->createState($name, $stateConfig);
            if (!is_array($stateConfig)) {
                continue;
            }
            foreach ($stateConfig['transitions'] as $key => $transitionConfig) {
                if (is_string($transitionConfig)) {
                    $transitionConfig = [ 'when' => $transitionConfig ];
                }
                $transitions[] = $this->factory->createTransition($name, $key, $transitionConfig);
            }
        }
        return [ $states, $transitions ];
    }
}
