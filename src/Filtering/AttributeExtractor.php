<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Filtering;

use App\Filtering\Exception\PhpIncompleteClassException;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class AttributeExtractor
{
    private $rootObject = null;
    private $rootAttribute = null;

    /**
     * Try to extract the $attribute from the given $object
     * It is a recursive function.
     * The $attribute will be exploded with the $separator delimiter, and then recursivly parsed to the last one
     *
     * @param mixed $object
     * @param string $attribute
     * @param string $separator
     *
     * @return mixed
     */
    public function extract($object, string $attribute, string $separator = '.')
    {
        if (null === $this->rootObject) {
            $this->rootObject = $object;
            $this->rootAttribute = $attribute;
        }

        $tmp = explode($separator, $attribute);

        $methodName = $this->constructGetter($tmp[0]);

        if ("__PHP_Incomplete_Class" === get_class($object)) {
            throw new PhpIncompleteClassException(
                'Cannot extract '.$attribute.' from Transaction #'. $this->rootObject->getId(),
                $this->rootObject,
                $this->rootAttribute
            );
        }

        if (false === method_exists($object, $methodName)) {
            return null;
        }

        if (count($tmp) > 1) {
            array_shift($tmp);

            return $this->extract($object->{$methodName}(), implode($separator, $tmp));
        }

        return $object->{$this->constructGetter($tmp[0])}();
    }

    private function constructGetter(string $attribute) : string
    {
        return 'get'.ucfirst($attribute);
    }
}
