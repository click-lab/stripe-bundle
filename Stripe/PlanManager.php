<?php

namespace Clab\StripeBundle\Stripe;

use Stripe\Plan;
use Stripe\Stripe;

class PlanManager
{
    public function __construct($secretKey)
    {
        Stripe::setApiKey($secretKey);
    }

    /**
     * @param $id Plan id
     */
    public function retrieve($id)
    {
        try {
            return Plan::retrieve($id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Avro\StripeBundle\Model\PlanInterface
     */
    public function create(Plan $plan)
    {
        return $plan->create(array(
            'id' => $plan->getId(),
            'name' => $plan->getName(),
            'amount' => $plan->getAmountInCents(),
            'currency' => $plan->getCurrency(),
            'interval' => $plan->getInterval(),
        ));
    }

    /**
     * @param Avro\StripeBundle\Model\PlanInterface
     */
    public function update(Plan $plan)
    {
        $stripe_plan = $this->retrieve($plan->getId());

        if (!$stripe_plan) {
            $stripe_plan = $this->create($plan);
        } else {
            $stripe_plan->name = $plan->getName();

            $stripe_plan->save();
        }

        return true;
    }

    /**
     * @param Avro\StripeBundle\Model\PlanInterface
     */
    public function delete(Plan $plan)
    {
        $stripe_plan = $this->retrieve($plan->getId());

        if ($stripe_plan) {
            $stripe_plan->delete();
        }

        return true;
    }
}
