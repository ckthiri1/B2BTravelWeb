<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailAPI
{
    private $mailer;
    private $params;
    
    public function __construct(MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->params = $params;
    }
    
    public function sendEmail(string $recipientEmail, string $fullName, string $oldRank, string $newRank): bool
    {
        try {
            $email = (new Email())
                ->from($this->params->get('app.email_from'))
                ->to($recipientEmail)
                ->subject('Congratulations on Your Rank Upgrade!')
                ->html($this->getEmailTemplate($fullName, $oldRank, $newRank));
    
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("❌ Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    
    private function getEmailTemplate(string $fullName, string $oldRank, string $newRank): string
    {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Rank Upgrade Notification</h2>
                <p>Dear {$fullName},</p>
                <p>Congratulations! Your loyalty rank has been upgraded from <strong>{$oldRank}</strong> to <strong>{$newRank}</strong>.</p>
                <p>Thank you for your continued loyalty to our service.</p>
                <p>Best regards,<br>The Team</p>
            </div>
        ";
    }
}