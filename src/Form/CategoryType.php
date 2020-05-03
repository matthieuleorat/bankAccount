<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    /**
     * @var \Doctrine\Persistence\ObjectRepository|\Gedmo\Tree\Entity\Repository\NestedTreeRepository
     */
    private $repo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(Category::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = $this->repo->childrenHierarchy(null, false, []);
        dump($choices);exit;
        $resolver->setDefaults([
            'class' => Category::class,
            'choices' => $this->repo->getChildren(null, false, null, 'asc', true),
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'category_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'category_type';
    }
}
