<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\Param\ParamHolderTrait;

final class Output implements OutputInterface
{
    use ParamHolderTrait;

    /**
     * @param string $current_state
     */
    private $current_state;

    /**
     * @param string $current_state
     * @param mixed[] $params
     */
    public function __construct(string $current_state, array $params = [])
    {
        $this->current_state = $current_state;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getCurrentState(): string
    {
        return $this->current_state;
    }

    /**
     * @param  string $current_state
     *
     * @return OutputInterface
     */
    public function withCurrentState(string $current_state): OutputInterface
    {
        $output = clone $this;
        $output->current_state = $current_state;
        return $output;
    }

    /**
     * @param string $current_state
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public static function fromInput(string $current_state, InputInterface $input): OutputInterface
    {
        return new static($current_state, $input->toArray());
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [ 'params' => $this->params, 'current_state' => $this->current_state ];
    }
}
