<?php

namespace Clab\StripeBundle\Manager;

use Clab\StripeBundle\Entity\Coupon;
use Doctrine\ORM\EntityManager;

class CouponManager
{
    protected $em;
    protected $class;
    protected $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClabStripeBundle:Coupon');
    }

    public function create($data)
    {
        $coupon = new Coupon();
        $coupon->setAmountOff($data['amount_off']);
        $coupon->setId(trim($data['id'], '"'));
        $coupon->setName(trim($data['name'], '"'));
        $coupon->setDuration(trim($data['duration'], '"'));
        $coupon->setDurationInMonth($data['duration_in_months']);
        $coupon->setPercentOff($data['percent_off']);
        $this->em->persist($coupon);
        $this->em->flush();

        return true;
    }

    public function delete(Coupon $coupon)
    {
        $this->em->remove($coupon);
        $this->em->flush();

        return true;
    }

    public function findActive()
    {
        return $this->repository->findBy(array('valid' => true));
    }

    public function findDeleted()
    {
        return $this->repository->findBy(array('valid' => false));
    }
}
