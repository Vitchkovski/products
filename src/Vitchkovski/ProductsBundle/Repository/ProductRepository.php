<?php

namespace Vitchkovski\ProductsBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends EntityRepository
{
    public function getProductWithCategories($productId, $userId)
    {
        $qb = $this->getEntityManager()->createQuery('SELECT p, c FROM VitchkovskiProductsBundle:Product p 
        LEFT OUTER JOIN p.categories c  
        WHERE p.product_id = :productId
          and p.user = :user_id')
            ->setParameter('user_id', $userId)
            ->setParameter('productId', $productId);

        return $qb->getOneOrNullResult();

    }
}
