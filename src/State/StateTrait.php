<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Daikon\StateMachine\Error\ConfigError;
use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\Param\ParamHolderInterface;
use Daikon\StateMachine\State\ValidatorInterface;

trait StateTrait
{
    private $name;

    private $settings;

    private $validator;

    private $expression_engine;

    public function __construct(
        string $name,
        ParamHolderInterface $settings,
        ValidatorInterface $validator,
        ExpressionLanguage $expression_engine
    ) {
        $this->name = $name;
        $this->settings = $settings;
        $this->validator = $validator;
        $this->expression_engine = $expression_engine;
        foreach ($this->getRequiredSettings() as $setting_name) {
            if (!$this->settings->has($setting_name)) {
                throw new ConfigError("Trying to configure state '$name' without required setting '$setting_name'.");
            }
        }
    }

    public function execute(InputInterface $input): OutputInterface
    {
        $this->validator->validateInput($this, $input);
        $output = $this->generateOutput($input);
        $this->validator->validateOutput($this, $output);
        return $output;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInitial(): bool
    {
        return false;
    }

    public function isFinal(): bool
    {
        return false;
    }

    public function isInteractive(): bool
    {
        return false;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function getSetting(string $name, $default = null)
    {
        return $this->settings->get($name) ?? $default;
    }

    public function getSettings(): ParamHolderInterface
    {
        return $this->settings;
    }

    private function generateOutput(InputInterface $input): OutputInterface
    {
        return new Output(
            $this->name,
            array_merge(
                $this->evaluateInputExports($input),
                $this->generateOutputParams($input)
            )
        );
    }

    private function evaluateInputExports(InputInterface $input): array
    {
        $exports = [];
        foreach ($this->getSetting('_output', []) as $key => $value) {
            $exports[$key] = $this->expression_engine->evaluate($value, [ 'input' => $input ]);
        }
        return $exports;
    }

    private function generateOutputParams(InputInterface $input): array
    {
        return [];
    }

    private function getRequiredSettings(): array
    {
        return [];
    }
}
