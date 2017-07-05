<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

interface InputInterface extends ParamHolderInterface
{
    public static function fromOutput(OutputInterface $input): InputInterface;

    public function getEvent(): string;

    public function hasEvent(): bool;

    public function withEvent(string $event): InputInterface;
}
