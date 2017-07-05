<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

final class Output implements OutputInterface
{
    use ParamHolderTrait;

    private $currentState;

    public function __construct(string $currentState, array $params = [])
    {
        $this->currentState = $currentState;
        $this->params = $params;
    }

    public function getCurrentState(): string
    {
        return $this->currentState;
    }

    public function withCurrentState(string $currentState): OutputInterface
    {
        $output = clone $this;
        $output->currentState = $currentState;
        return $output;
    }

    public static function fromInput(string $currentState, InputInterface $input): OutputInterface
    {
        return new static($currentState, $input->toArray());
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [ 'params' => $this->params, 'current_state' => $this->currentState ];
    }
}
