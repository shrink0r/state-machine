<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\Param\ParamHolderInterface;

interface StateInterface
{
    public function execute(InputInterface $input): OutputInterface;

    public function getName(): string;

    public function isInitial(): bool;

    public function isFinal(): bool;

    public function isInteractive(): bool;

    public function getValidator(): ValidatorInterface;

    public function getSetting(string $name, $default = null);

    public function getSettings(): ParamHolderInterface;
}
