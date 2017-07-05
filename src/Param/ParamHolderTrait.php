<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

trait ParamHolderTrait
{
    private $params = [];

    public function __get($paramName)
    {
        return $this->get($paramName);
    }

    public function get(string $paramName, bool $treatNameAsPath = true)
    {
        if (!$treatNameAsPath) {
            return $this->has($paramName) ? $this->params[$paramName] : null;
        }
        $params = $this->params;
        $nameParts = array_reverse(explode('.', $paramName));
        $curVal = &$params;
        while (count($nameParts) > 1 && $curName = array_pop($nameParts)) {
            if (!array_key_exists($curName, $curVal)) {
                return null;
            }
            $curVal = &$curVal[$curName];
        }
        return array_key_exists($nameParts[0], $curVal) ? $curVal[$nameParts[0]] : null;
    }

    public function has(string $paramName): bool
    {
        return array_key_exists($paramName, $this->params);
    }

    public function withParam(string $paramName, $paramValue, bool $treatNameAsPath = true): ParamHolderInterface
    {
        $paramHolder = clone $this;
        if ($treatNameAsPath) {
            $nameParts = array_reverse(explode('.', $paramName));
            $curVal = &$paramHolder->params;
            while (count($nameParts) > 1 && $curName = array_pop($nameParts)) {
                if (!isset($curVal[$curName])) {
                    $curVal[$curName] = [];
                }
                $curVal = &$curVal[$curName];
            }
            $curVal[$nameParts[0]] = $paramValue;
            return $paramHolder;
        }

        $paramHolder->params[$paramName] = $paramValue;
        return $paramHolder;
    }

    public function withParams(array $params): ParamHolderInterface
    {
        $paramHolder = clone $this;
        $paramHolder->params = array_merge($paramHolder->params, $params);
        return $paramHolder;
    }

    public function withoutParam(string $paramName): ParamHolderInterface
    {
        if (!$this->has($paramName)) {
            return $this;
        }
        $paramHolder = clone $this;
        unset($paramHolder->params[$paramName]);
        return $paramHolder;
    }

    public function withoutParams(array $paramNames): ParamHolderInterface
    {
        return array_reduce(
            $paramNames,
            function (ParamHolderInterface $paramHolder, $paramName) {
                return $paramHolder->withoutParam($paramName);
            },
            $this
        );
    }

    public function toArray(): array
    {
        return $this->params;
    }
}
