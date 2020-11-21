<?php declare(strict_types=1);

namespace App\Filtering;

class AttributeExtractor
{
    /**
     * Try to extract the $attribute from the given $object
     * It is a recursive function.
     * The $attribute will be exploded with the $separator delimiter, and then recursivly parsed to the last one
     *
     * @param object $object
     * @param string $attribute
     * @param string $separator
     *
     * @return mixed
     */
    public function extract(object $object, string $attribute, string $separator = '.')
    {
        $tmp = explode($separator, $attribute);

        $methodName = $this->constructGetter($tmp[0]);

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
