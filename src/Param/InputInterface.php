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

interface InputInterface extends ParamHolderInterface
{
    /**
     * @param OutputInterface $output
     *
     * @return InputInterface
     */
    public static function fromOutput(OutputInterface $input): InputInterface;

    /**
     * @return string
     */
    public function getEvent(): string;

    /**
     * @return boolean
     */
    public function hasEvent(): bool;

    /**
     * @param  string $event
     * @return InputInterface
     */
    public function withEvent(string $event): InputInterface;
}
