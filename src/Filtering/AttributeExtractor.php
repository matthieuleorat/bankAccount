<?php declare(strict_types=1);

namespace App\Filtering;

class AttributeExtractor
{
    public function extract($object, string $attribute, string $separator = '.') : ? string
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
