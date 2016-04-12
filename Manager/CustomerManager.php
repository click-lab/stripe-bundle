<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clab\StripeBundle\Manager;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityManager;
use Stripe\Card;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Error\Base;
use Stripe\Invoice;
use Stripe\Plan;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * Processes a Stripe token and adds the Stripe Customer to the User
 * as well as subscribe the user to a plan.
 *
 * @author Sacha Masson <sacha@click-eat.fr>
 */
class CustomerManager
{
    protected $request;
    protected $user;
    protected $userManager;
    protected $planManager;

    public function __construct(Request $request, EntityManager $em, UserManagerInterface $userManager, PlanManager $planManager, $secretKey)
    {
        $this->request = $request;
        $this->em = $em;
        $this->userManager = $userManager;
        $this->planManager = $planManager;

        Stripe::setApiKey($secretKey);
    }

    /**
     * Method that charge a customer card.
     */
    public function chargeCard(Restaurant $restaurant, Card $card, Plan $plan)
    {
        try {
            Charge::create(array(
                'amount' => $plan->amount * 1.2,
                'currency' => 'eur',
                'customer' => $restaurant->getStripeCustomerId(),
                'card' => $card->id,
            ));
            $cu = Customer::retrieve($restaurant->getStripeCustomerId());
            $subscription = $cu->subscriptions->create(array('plan' => $plan));
            $subscription->tax_percent = 20;
            $subscription->save();

            return $subscription;
        } catch (Base $e) {
            throw $e;
        }
    }
    /**
     * Process a Stripe token.
     */
    public function process(Plan $plan = null, Restaurant $restaurant)
    {
        if ($restaurant->getStripeCustomerId()) {
            $subscription = $this->update($plan, $restaurant);

            return $subscription;
        } else {
            $subscription = $this->create($plan, $restaurant);

            return $subscription;
        }
    }

    /**
     * Process a Stripe token.
     */
    public function upgrade(Restaurant $restaurant, $currentSubscription, Plan $newPlan)
    {
        if ($restaurant->getStripeCustomerId()) {
            $cus = Customer::retrieve($restaurant->getStripeCustomerId());
            $subscription = $cus->subscriptions->retrieve($currentSubscription);

            $subscription->plan = $newPlan;
            $subscription->tax_percent = 20;
            $subscription->save();

            return $subscription;
        }

        return false;
    }

    public function import()
    {
        Customer::create(array(
            'email' => 'test@click-eat.fr',
            'plan' => 'annuelnul', ));
    }
    public function create(Plan $plan, Restaurant $restaurant)
    {
        if ($plan) {
            try {
                $customer = Customer::create(array(
              'card' => $this->getToken(),
              'email' => $restaurant->getManagerEmail(),
            ));

                $subscription = $customer->subscriptions->create(array('plan' => $plan->__toArray()['id'], 'tax_percent' => 20));
                $subscription->save();

                return $subscription;
            } catch (\Error $e) {
                return false;
            }
        } else {
            try {
                $customer = Customer::create(array(
                    'card' => $this->getToken(),
                    'email' => $restaurant->getManagerEmail(),
                ));
            } catch (\Error $e) {
                return false;
            }
        }
        $restaurant->setStripeCustomerId($customer->id);

        $card = $customer->active_card;
        if ($card) {
            $restaurant->setIsStripeCustomerActive(true);
        }
        $this->em->flush();

        return true;
    }

    /*
     * Update a Stripe Customer
     */
    public function update(Plan $plan, Restaurant $restaurant)
    {
        $customer = $this->retrieve($restaurant->getStripeCustomerId());

        $token = $this->getToken();

        if ($token) {
            $customer->card = $token;
            $customer->save();

            $card = $customer->card;

            if ($card) {
                $restaurant->setIsStripeCustomerActive(true);
            }
        }

        if ($plan) {
            $customer->updateSubscription(array(
                'plan' => $plan,
            ));
        }

        $this->em->flush();

        return true;
    }

    /**
     * Disable Stripe customer.
     */
    public function disable(Restaurant $restaurant)
    {
        $stripeCustomerId = $restaurant->getStripeCustomerId();

        $customer = $this->retrieve($stripeCustomerId);

        $customer->active_card = null;
        $customer->save();

        $restaurant->setIsStripeCustomerActive(false);

        $this->userManager->updateUser($this->user);
        $this->em->flush();

        return true;
    }

    /**
     * Cancel Stripe customers subscription.
     */
    public function cancelSubscription(Restaurant $restaurant)
    {
        $stripeCustomerId = $restaurant->getStripeCustomerId();

        try {
            $customer = $this->retrieve($stripeCustomerId);
            $customer->cancelSubscription();
            $restaurant->setIsStripeCustomerActive(false);
            $this->em->flush();

            return true;
        } catch (Base $e) {
            return $e;
        }
    }

    /**
     * Retrieve a customer from Stripe.
     */
    public function retrieve($id)
    {
        try {
            return Customer::retrieve($id);
        } catch (Base $e) {
            throw new \Exception('Customer not found');
        }
    }

    /**
     * Get credit card token.
     */
    public function getToken()
    {
        return $this->request->request->get('stripeToken');
    }

    public function listCards(Restaurant $restaurant)
    {
        $stripeCustomerId = $restaurant->getStripeCustomerId();
        try {
            $cards = Customer::retrieve($stripeCustomerId)->sources->all(array('object' => 'card'));

            return $cards;
        } catch (Base $e) {
            throw $e;
        }
    }
    public function createCard(Restaurant $restaurant)
    {
        $stripeCustomerId = $restaurant->getStripeCustomerId();
        try {
            Customer::retrieve($stripeCustomerId)->sources->create(array('source' => $this->getToken()));

            return true;
        } catch (Base $e) {
            throw $e;
        }
    }

    public function deleteCard(Restaurant $restaurant, $card)
    {
        $stripeCustomerId = $restaurant->getStripeCustomerId();
        Customer::retrieve($stripeCustomerId)->sources->retrieve($card)->delete();
    }

    public function retrieveSubscriptions($id, $idSubscription)
    {
        $cu = Customer::retrieve($id);

        $subscription = $cu->subscriptions->retrieve($idSubscription);

        return $subscription;
    }

    public function retrieveInvoicesFromCustomer($id)
    {
        $cu = Customer::retrieve($id);
        $response = Invoice::all(array(
            'customer' => $cu,
        ));

        return $response;
    }

    public function retrieveOneInvoice($id)
    {
        $response = Invoice::retrieve($id);

        return $response;
    }

    public function retrieveInvoiceDetails($id)
    {
        $response = Invoice::retrieve($id)->lines->all();

        return $response;
    }
}
