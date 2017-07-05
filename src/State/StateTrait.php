<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Daikon\StateMachine\Error\ConfigError;
use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\Param\ParamHolderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

trait StateTrait
{
    private $name;

    private $settings;

    private $validator;

    private $expressionEngine;

    public function __construct(
        string $name,
        ParamHolderInterface $settings,
        ValidatorInterface $validator,
        ExpressionLanguage $expressionEngine
    ) {
        $this->name = $name;
        $this->settings = $settings;
        $this->validator = $validator;
        $this->expressionEngine = $expressionEngine;
        foreach ($this->getRequiredSettings() as $settingName) {
            if (!$this->settings->has($settingName)) {
                throw new ConfigError("Trying to configure state '$name' without required setting '$settingName'.");
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
            $exports[$key] = $this->expressionEngine->evaluate($value, [ 'input' => $input ]);
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
