<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;

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
                ->from(new Address($this->params->get('app.email_from'), 'B2B TRAVEL'))
                ->to($recipientEmail)
                ->subject("🎉 Congratulations $fullName! Your loyalty status has been upgraded")
                ->html($this->getEmailTemplate($fullName, $oldRank, $newRank));

            // Add logo
            $logoPath = $this->params->get('kernel.project_dir') . '/public/images/logo.png';
            if (file_exists($logoPath)) {
                $email->embed(fopen($logoPath, 'r'), 'logo.png');
            }

            // Add signature image
            $signaturePath = $this->params->get('kernel.project_dir') . '/public/images/signature.png';
            if (file_exists($signaturePath)) {
                $email->embed(fopen($signaturePath, 'r'), 'signature.png');
            }
    
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
        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; max-width: 600px; margin: 0 auto;'>
            <!-- Logo Header -->
            <div style='text-align: center; margin-bottom: 20px;'>
                <img src='cid:logo.png' alt='B2B TRAVEL Logo' style='max-height: 80px;'>
            </div>
            
            <h2 style='color: #1e90ff; text-align: center;'>🚀 Congratulations, $fullName!</h2>
            <p style='color: #333; font-size: 16px; text-align: center;'>Dear <strong>$fullName</strong>,</p>
            <p style='color: #555; font-size: 14px; text-align: center;'>
                We're thrilled to announce that your loyalty has earned you an upgraded status! 🎖️
            </p>
            
            <div style='text-align: center; margin: 20px auto; padding: 20px; background-color: #fff; 
                        border-radius: 12px; box-shadow: 2px 2px 10px rgba(0,0,0,0.15); width: 80%;'>
                <p style='font-size: 18px; color: #d9534f;'>🏅 Previous rank: <strong>$oldRank</strong></p>
                <p style='font-size: 18px; color: #28a745;'>🌟 New rank: <strong>$newRank</strong></p>
            </div>
            
            <p style='text-align: center; font-size: 16px;'>
                <strong style='color: #008000;'>Enjoy new exclusive benefits starting now!</strong>
            </p>
            
            <!-- Signature Section -->
            <div style='margin-top: 10px; text-align: center; border-top: 1px solid #ddd; padding-top: 20px;'>
                <img src='cid:signature.png' alt='B2B TRAVEL Signature' style='max-width: 200px;'>
                <p style='text-align: center; font-size: 14px;'>
                    ✈️ <em>B2B TRAVEL - Your travel partner</em> ✈️
                </p>
            </div>
        </div>
        ";
    }
}