<?php

namespace Clab\StripeBundle\Manager;

use Doctrine\ORM\EntityManager;
use Stripe\Plan;
use Stripe\Stripe;

class PlanManager
{
    protected $em;
    protected $class;
    protected $repository;

    public function __construct(EntityManager $em, $secretKey)
    {
        $this->em = $em;
        Stripe::setApiKey($secretKey);
    }

    public function listPlans()
    {
        $plans = Plan::all(array('limit' => 50));

        return $plans;
    }
    public function listPlan($id)
    {
        $p = Plan::retrieve($id);

        return $p;
    }
}
