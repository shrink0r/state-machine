<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

interface ParamHolderInterface
{
    public function get(string $paramName);

    public function has(string $paramName): bool;

    public function withParam(string $paramName, $paramValue, bool $treatNameAsPath = true): self;

    public function withParams(array $params): self;

    public function withoutParam(string $paramName): ParamHolderInterface;

    public function withoutParams(array $paramNames): ParamHolderInterface;

    public function toArray(): array;
}
