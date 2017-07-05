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
    public static function fromInput(string $currentState, InputInterface $input): OutputInterface;

    public function getCurrentState(): string;
}
