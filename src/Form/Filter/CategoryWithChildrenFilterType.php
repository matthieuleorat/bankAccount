<?php declare(strict_types=1);

namespace App\Form\Filter;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryWithChildrenFilterType extends FilterType
{
    private $categoryRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->categoryRepository = $entityManager->getRepository(Category::class);
    }

    public function getParent() : string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'class' => Category::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata) : void
    {
        if (null !== $id = $form->getData()) {
            $children = $this->categoryRepository->children($id, false, null, 'asc', true);
            $queryBuilder->andWhere('entity.category IN (:categories)')
                ->setParameter('categories', $children);
        }
    }
}
