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

interface TypeInterface
{
    /**
     * @param string $data
     *
     * @return TypeInterface|null
     */
    public static function createFromString(string $data) : ? TypeInterface;

    /**
     * @param Operation $operation
     *
     * @return TypeInterface|null
     */
    public static function createFormOperation(Operation $operation) : ? TypeInterface;

    /**
     * @param array $matches
     *
     * @return TypeInterface
     */
    public static function create(array $matches) : TypeInterface;
}
