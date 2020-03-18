<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TransactionExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('displayType', [$this, 'displayTransactionType'], ['is_safe' => ['html']]),
        ];
    }

    public function displayTransactionType($value)
    {
        $reflect = new \ReflectionClass($value);
        $props   = $reflect->getProperties();
        $datas = [];
        foreach($props as $prop) {
            $datas[] = ucfirst($prop->getName()).': '.$value->{'get'.ucfirst($prop->getName())}();
        }

        return implode("\n",$datas);
    }
}
