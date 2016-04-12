<?php

namespace Clab\StripeBundle\Tests;

require __DIR__.'/../../../../app/autoload.php';
use Clab\RestaurantBundle\Entity\Restaurant;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Stripe;
use Stripe\Token;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;

class CustomerManagerTest  extends WebTestCase
{
    public function initPlan()
    {
        Plan::create(array(
                'amount' => 2000,
                'interval' => 'month',
                'name' => 'Amazing Gold Plan',
                'currency' => 'eur',
                'id' => 'gold', )
        );
    }
    public function init()
    {
        Stripe::setApiKey(static::$kernel->getContainer()->getParameter('sk_test_IHp7PtWAwQzMybApLcKXHFRy'));
        $this->initPlan();
        $restaurant = new Restaurant();
        $restaurant->setStripeCustomerId('cus_7k9PW8RD4WIolO');
        $token = Token::create(array(
            'card' => array(
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => 2017,
                'cvc' => '314',
            ),
        ));
        Customer::retrieve($restaurant->getStripeCustomerId())->sources->create(array('source' => $token));

        return $restaurant;
    }

    public function testAddSubscription()
    {
        $restaurant = $this->init();
        $plan = Plan::retrieve('gold');
        $manager = static::$kernel->getContainer()->get('clab_stripe.customer.manager');
        $result = $manager->create($plan, $restaurant);
        $this->assertTrue($result);
    }
    public function testRetrieveCustomer()
    {
        $customer = static::$kernel->getContainer()->get('clab_stripe.customer.manager');
        $restaurant = $this->init();
        $customer->retrieve($restaurant->getStripeCustomerId());
        $objectUser = json_decode($customer);
        $this->assertContains(
            'customer',
            $objectUser->getResponse()->getContent()
        );
    }

    public function testAddCardToCustomer()
    {
        $restaurant = $this->init();
        $token = Token::create(array(
            'card' => array(
                'number' => '5555555555554444',
                'exp_month' => 1,
                'exp_year' => 2018,
                'cvc' => '152',
            ),
        ));
        $object = Customer::retrieve($restaurant->getStripeCustomerId())->sources->create(array('source' => $token));
        $objectUser = json_decode($object);
        $this->assertContains(
            'Mastercard',
            $objectUser->getResponse()->getContent()
        );
    }

    public function testCancelSubscription()
    {
        $restaurant = $this->init();
        $manager = static::$kernel->getContainer()->get('clab_stripe.customer.manager');
        $result = $manager->cancelSubscription($restaurant);
        $this->assertTrue($result);
    }
}
