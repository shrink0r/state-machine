<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use Symfony\Component\Yaml\Parser;
use Daikon\StateMachine\Builder\Factory;
use Daikon\StateMachine\Builder\StateMachineBuilderInterface;
use Daikon\StateMachine\Error\ConfigError;
use Daikon\StateMachine\StateMachine;
use Daikon\StateMachine\StateMachineInterface;

final class YamlStateMachineBuilder implements StateMachineBuilderInterface
{
    private $parser;

    private $yaml_filepath;

    private $factory;

    public function __construct(string $yaml_filepath, FactoryInterface $factory = null)
    {
        $this->parser = new Parser;
        if (!is_readable($yaml_filepath)) {
            throw new ConfigError("Trying to load non-existant statemachine definition at: $yaml_filepath");
        }
        $this->yaml_filepath = $yaml_filepath;
        $this->factory = $factory ?? new Factory;
    }

    public function build(): StateMachineInterface
    {
        $data = $this->parser->parse(file_get_contents($this->yaml_filepath));
        $result = (new StateMachineSchema)->validate($data);
        if ($result instanceof Error) {
            throw new ConfigError('Invalid statemachine configuration given: '.print_r($result->unwrap(), true));
        }
        list($states, $transitions) = $this->realizeConfig($data['states']);
        $state_machine_class = Maybe::unit($data)->class->get() ?? StateMachine::CLASS;
        return (new StateMachineBuilder($state_machine_class))
            ->addStateMachineName($data['name'])
            ->addStates($states)
            ->addTransitions($transitions)
            ->build();
    }

    private function realizeConfig(array $config): array
    {
        $states = [];
        $transitions = [];
        foreach ($config as $name => $state_config) {
            $states[] = $this->factory->createState($name, $state_config);
            if (!is_array($state_config)) {
                continue;
            }
            foreach ($state_config['transitions'] as $key => $transition_config) {
                if (is_string($transition_config)) {
                    $transition_config = [ 'when' => $transition_config ];
                }
                $transitions[] = $this->factory->createTransition($name, $key, $transition_config);
            }
        }
        return [ $states, $transitions ];
    }
}
