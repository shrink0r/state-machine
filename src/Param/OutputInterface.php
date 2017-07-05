<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

interface OutputInterface extends ParamHolderInterface
{
    /**
     * @param string $currentState
     * @param InputInterface $input
     *
     * @return self
     */
    public static function fromInput(string $currentState, InputInterface $input): self;

    /**
     * @return string
     */
    public function getCurrentState(): string;
}
