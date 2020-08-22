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
        //$value = unserialize($value);

        if (false === is_object($value)) {
            return '';
        }

        $reflect = new \ReflectionClass($value);
        $props   = $reflect->getProperties();
        $datas = [];
        foreach($props as $prop) {
            $data = $value->{'get'.ucfirst($prop->getName())}();
            if ($data instanceof \DateTimeImmutable) {
                $data = $data->format('d/m/Y');
            }

            $datas[] = ucfirst($prop->getName()).': '.$data;
        }

        return implode("\n",$datas);
    }
}
