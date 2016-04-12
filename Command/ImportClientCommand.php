<?php

namespace Clab\StripeBundle\Command;

use Clab\BoardBundle\Entity\Subscription;
use Stripe\Customer;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clickeat:stripe:client:import')
            ->setDescription('Import client and foodtruck')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $this->getContainer()->getParameter('stripe_secret_key');
        Stripe::setApiKey($key);
        $year = new \DateTime('now');
        $year->modify('+1 year');
        $planFT = $this->getContainer()->get('doctrine')->getRepository('ClabRestaurantBundle:Plan')->findOneBy(array(
           'stripePlanId' => 'old-truck',
        ));
        $planResto = $this->getContainer()->get('doctrine')->getRepository('ClabRestaurantBundle:Plan')->findOneBy(array(
            'stripePlanId' => 'old-restaurant',
        ));
        $restaurants = $this->getContainer()->get('doctrine')->getRepository('ClabRestaurantBundle:Restaurant')
            ->findBy(array('isMobile' => false, 'status' => 3000));
        $foodtrucks = $this->getContainer()->get('doctrine')->getRepository('ClabRestaurantBundle:Restaurant')
            ->findBy(array('isMobile' => true, 'status' => 3000));
        foreach ($restaurants as $restaurant) {
            $customer = Customer::create(array(
                'email' => $restaurant->getManagerEmail(),
                'plan' => 'old-restaurant', ));
            $customerArray = $customer->__toArray(true);
            $restaurant->setStripeCustomerId($customerArray['id']);
            $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
            $cu = Customer::retrieve($customerArray['id']);

            $subscription = $cu->subscriptions->retrieve($customerArray['subscriptions']['data'][0]['id']);
            $subscription->tax_percent = 20;
            $subscription->save();
            $subscriptionOwn = new Subscription();
            $subscriptionOwn->setRestaurant($restaurant);
            $subscriptionOwn->setIsOnline(1);
            $subscriptionOwn->setCommission(9);
            $subscriptionOwn->setCommissionExternal(7);
            $subscriptionOwn->setTransactionCommission(2);
            $subscriptionOwn->setType(0);
            $subscriptionOwn->setNextDueDate($year);
            $subscriptionOwn->setStripeSubscriptionId($subscription->id);
            $subscriptionOwn->setPlan($planResto);
            $this->getContainer()->get('doctrine')->getManager()->persist($subscriptionOwn);
            $this->getContainer()->get('doctrine')->getManager()->flush();
        }

        foreach ($foodtrucks as $foodtruck) {
            $customer = Customer::create(array(
                'email' => $foodtruck->getManagerEmail(),
                'plan' => 'old-truck', ));

            $customerArray = $customer->__toArray(true);
            $foodtruck->setStripeCustomerId($customerArray['id']);
            $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
            $cu = Customer::retrieve($customerArray['id']);

            $subscription = $cu->subscriptions->retrieve($customerArray['subscriptions']['data'][0]['id']);
            $subscription->tax_percent = 20;
            $subscription->save();
            $subscriptionOwn = new Subscription();
            $subscriptionOwn->setRestaurant($foodtruck);
            $subscriptionOwn->setIsOnline(1);
            $subscriptionOwn->setCommission(9);
            $subscriptionOwn->setCommissionExternal(7);
            $subscriptionOwn->setTransactionCommission(2);
            $subscriptionOwn->setType(0);
            $subscriptionOwn->setNextDueDate($year);
            $subscriptionOwn->setStripeSubscriptionId($subscription->id);
            $subscriptionOwn->setPlan($planFT);
            $this->getContainer()->get('doctrine')->getManager()->persist($subscriptionOwn);
            $this->getContainer()->get('doctrine')->getManager()->flush();
        }

        return true;
    }
}
