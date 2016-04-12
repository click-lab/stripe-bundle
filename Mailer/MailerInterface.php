<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clab\StripeBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;

/**
 * @author Sacha Masson <sacha@click-eat.fr>
 */
interface MailerInterface
{
    public function sendSubscriptionUpdatedEmail(UserInterface $user);
    public function sendChargeSucceededEmail(UserInterface $user, $data);
    public function sendChargeFailedEmail(UserInterface $user, $data);
    public function sendInvoicePaymentSucceededEmail(UserInterface $user, $data);
    public function sendInvoicePaymentFailedEmail(UserInterface $user, $data);
    public function sendAccountConnectedEmail(UserInterface $user);
    public function sendAccountApplicationDeauthorizedEmail(UserInterface $user);
    public function sendEmail($renderedTemplate, $fromEmail, $toEmail, $attachment = false, $html = false);
}
