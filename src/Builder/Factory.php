<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Builder;

use Daikon\StateMachine\Exception\ConfigException;
use Daikon\StateMachine\Exception\MissingImplementation;
use Daikon\StateMachine\Param\Settings;
use Daikon\StateMachine\State\FinalState;
use Daikon\StateMachine\State\InitialState;
use Daikon\StateMachine\State\InteractiveState;
use Daikon\StateMachine\State\State;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\Validator;
use Daikon\StateMachine\State\ValidatorInterface;
use Daikon\StateMachine\Transition\ExpressionConstraint;
use Daikon\StateMachine\Transition\Transition;
use Daikon\StateMachine\Transition\TransitionInterface;
use Ds\Map;
use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Factory as PhpSchemaFactory;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Factory implements FactoryInterface
{
    const SUFFIX_IN = '-input_schema';

    const SUFFIX_OUT = '-output_schema';

    private $classMap;

    private static $defaultClasses = [
        'initial' => InitialState::CLASS,
        'interactive' => InteractiveState::CLASS,
        'state' => State::CLASS,
        'final' => FinalState::CLASS,
        'transition' => Transition::CLASS
    ];

    private static $defaultValidationSchema = [ ':any_name:' => [ 'type' => 'any' ] ];

    private $expressionEngine;

    public function __construct(array $classMap = [], ExpressionLanguage $expressionEngine = null)
    {
        $this->expressionEngine = $expressionEngine ?? new ExpressionLanguage;
        $this->classMap = new Map(array_merge(self::$defaultClasses, $classMap));
    }

    public function createState(string $name, array $state = null): StateInterface
    {
        $state = Maybe::unit($state);
        $stateImplementor = $this->resolveStateImplementor($state);
        $settings = $state->settings->get() ?? [];
        $settings['_output'] = $state->output->get() ?? [];
        $stateInstance = new $stateImplementor(
            $name,
            new Settings($settings),
            $this->createValidator($name, $state),
            $this->expressionEngine
        );
        if ($state->final->get() && !$stateInstance->isFinal()) {
            throw new ConfigException(
                'Trying to provide custom state that is not final but marked as final in config.'
            );
        }
        if ($state->initial->get() && !$stateInstance->isInitial()) {
            throw new ConfigException(
                'Trying to provide custom state that is not initial but marked as initial in config.'
            );
        }
        if ($state->interactive->get() && !$stateInstance->isInteractive()) {
            throw new ConfigException(
                'Trying to provide custom state that is not interactive but marked as interactive in config.'
            );
        }
        return $stateInstance;
    }

    public function createTransition(string $from, string $to, array $config = null): TransitionInterface
    {
        $transition = Maybe::unit($config);
        if (is_string($transition->when->get())) {
            $config['when'] = [ $transition->when->get() ];
        }
        $implementor = $transition->class->get() ?? $this->classMap->get('transition');
        if (!in_array(TransitionInterface::CLASS, class_implements($implementor))) {
            throw new MissingImplementation(
                'Trying to create transition without implementing required '.TransitionInterface::CLASS
            );
        }
        $constraints = [];
        foreach (Maybe::unit($config)->when->get() ?? [] as $expression) {
            if (!is_string($expression)) {
                continue;
            }
            $constraints[] = new ExpressionConstraint($expression, $this->expressionEngine);
        }
        $settings = new Settings(Maybe::unit($config)->settings->get() ?? []);
        return new $implementor($from, $to, $settings, $constraints);
    }

    private function resolveStateImplementor(Maybe $state): string
    {
        switch (true) {
            case $state->initial->get():
                $stateImplementor = $this->classMap->get('initial');
                break;
            case $state->final->get() === true || $state->get() === null: // cast null to final-state by convention
                $stateImplementor = $this->classMap->get('final');
                break;
            case $state->interactive->get():
                $stateImplementor = $this->classMap->get('interactive');
                break;
            default:
                $stateImplementor = $this->classMap->get('state');
        }
        $stateImplementor = $state->class->get() ?? $stateImplementor;
        if (!in_array(StateInterface::CLASS, class_implements($stateImplementor))) {
            throw new MissingImplementation(
                'Trying to use a custom-state that does not implement required '.StateInterface::CLASS
            );
        }
        return $stateImplementor;
    }

    private function createValidator(string $name, Maybe $state): ValidatorInterface
    {
        return new Validator(
            $this->createValidationSchema(
                $name.self::SUFFIX_IN,
                $state->input_schema->get() ?? self::$defaultValidationSchema
            ),
            $this->createValidationSchema(
                $name.self::SUFFIX_OUT,
                $state->output_schema->get() ?? self::$defaultValidationSchema
            )
        );
    }

    private function createValidationSchema(string $name, array $schemaDefinition): SchemaInterface
    {
        return new Schema($name, [ 'type' => 'assoc', 'properties' => $schemaDefinition ], new PhpSchemaFactory);
    }
}
