<?php declare(strict_types=1);

/*
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ObjectType implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            // this template is used in 'index' and 'detail' pages
            ->setTemplatePath('admin/field/object.html.twig')
            // this is used in 'edit' and 'new' pages to edit the field contents
            // you can use your own form types too
            ->setFormType(TextareaType::class)
//            ->addCssClass('field-map')
            // these methods allow to define the web assets loaded when the
            // field is displayed in any CRUD page (index/detail/edit/new)
//            ->addCssFiles('js/admin/field-map.css')
//            ->addJsFiles('js/admin/field-map.js')
            ;
    }
}
