<?php declare(strict_types=1);

/*
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace BankStatementParser\Model;

abstract class AbstractType implements TypeInterface
{
    const PATTERN = 'SHOULD BE IMPLEMENTED BY CHILD CLASS';

    public static function createFromString(string $data) : ? TypeInterface
    {
        preg_match(static::PATTERN, $data, $matches);
        if (count($matches)) {
            return static::create($matches);
        }

        return null;
    }

    public static function createFormOperation(Operation $operation) : ? TypeInterface
    {
        return static::createFromString($operation->getDetails());
    }

    protected function tryToGuess(string $pattern, array $matches) : ? string
    {
        foreach ($matches as $key => $value) {
            if (substr($value, 0, strlen($pattern)) === $pattern && array_key_exists($key + 1, $matches)) {
                return trim($matches[$key + 1]);
            }
        }

        return null;
    }
}