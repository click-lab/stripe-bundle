<?php

namespace Clab\StripeBundle\Command;

use Stripe\Plan;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPlanCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clickeat:stripe:plan:import')
            ->setDescription('Import plans')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $this->getContainer()->getParameter('stripe_secret_key');
        Stripe::setApiKey($key);

        $plans = Plan::all();
        $plansArray = $plans->__toArray(true)['data'];
        foreach ($plansArray as $plan) {
            $ourPlan = new \Clab\RestaurantBundle\Entity\Plan();
            $ourPlan->setName($plan['name']);
            $ourPlan->setStripePlanId($plan['id']);
            $ourPlan->setPrice($plan['amount'] / 100);
            $ourPlan->setIsOnline(true);
            $this->getContainer()->get('doctrine.orm.entity_manager')->persist($ourPlan);
            $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
        }
    }
}
