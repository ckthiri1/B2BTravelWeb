<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;
use App\Entity\Reservation_voyage;
use App\Entity\Vol;

class MailApiReservation
{
    private $mailer;
    private $params;
    
    public function __construct(MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->params = $params;
    }
    
    public function sendReservationConfirmation(
        string $recipientEmail,
        string $recipientName,
        Reservation_voyage $reservation,
        Vol $flight,
        array $qrCodePaths = []
    ): bool {
        try {
            $email = (new Email())
                ->from(new Address($this->params->get('app.email_from'), 'B2B TRAVEL'))
                ->to(new Address($recipientEmail, $recipientName))
                ->subject("✈️ Your Flight Reservation Confirmation - {$reservation->getReservationCode()}")
                ->html($this->getReservationEmailTemplate($recipientName, $reservation, $flight, $qrCodePaths));

            // Add logo
            $logoPath = $this->params->get('kernel.project_dir') . '/public/images/LogoDashboard.jpg';
            if (file_exists($logoPath)) {
                $email->embed(fopen($logoPath, 'r'), 'LogoDashboard.jpg');
            }

            // Add signature image
            $signaturePath = $this->params->get('kernel.project_dir') . '/public/images/signatureMail.png';
            if (file_exists($signaturePath)) {
                $email->embed(fopen($signaturePath, 'r'), 'signatureMail.png');
            }
            
            // Add QR codes if provided
            foreach ($qrCodePaths as $index => $qrPath) {
                if (file_exists($qrPath)) {
                    $email->embed(fopen($qrPath, 'r'), "qrcode_{$index}.png");
                }
            }
    
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("❌ Reservation email failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function getReservationEmailTemplate(
        string $recipientName,
        Reservation_voyage $reservation,
        Vol $flight,
        array $qrCodePaths
    ): string {
        $departureDate = $flight->getDateDepart()->format('l, F j, Y');
        $arrivalDate = $flight->getDateArrival()->format('l, F j, Y');
        $departureTime = $flight->getDateDepart()->format('h:i A');
        $arrivalTime = $flight->getDateArrival()->format('h:i A');
        $paymentDate = $reservation->getPaymentDate()->format('F j, Y h:i A');
        
        // Get user details
        $user = $reservation->getId_user();
        $firstName = $user->getPrenom();
        $lastName = $user->getNom();
        
        // Get flight details
        $voyage = $flight->getIdVoyage();
        $departureCity = $voyage->getDepart();
        $arrivalCity = $voyage->getDestination();
        $duration = $flight->getDureeVol();
        $airline = $flight->getAirLine();
        $flightNumber = $flight->getFlightNumber();
        
        // Format price
        $totalPrice = number_format($reservation->getPrixTotal(), 2) . ' DT';
        
        $qrCodeHtml = '';
        foreach ($qrCodePaths as $index => $qrPath) {
            $qrCodeHtml .= '<img src="cid:qrcode_' . $index . '.png" alt="Boarding Pass QR Code" style="max-width: 180px; margin: 15px;">';
        }
        
        return <<<HTML
        <div style="font-family: 'Segoe UI', Arial, sans-serif; max-width: 700px; margin: 0 auto; background-color: #f8f9fa; padding: 0;">
            <!-- Email Header with Logo -->
            <div style="background-color: #13117D; padding: 25px 0; text-align: center;">
                <img src="cid:LogoDashboard.jpg" alt="B2B Travel Logo" style="max-height: 100px;">
            </div>
            
            <!-- Main Content -->
            <div style="padding: 30px; background-color: #ffffff;">
                <!-- Personalized Greeting -->
                <h1 style="color: #1a237e; margin-top: 0; font-size: 24px;">Dear {$firstName} {$lastName},</h1>
                <p style="font-size: 16px; color: #555; line-height: 1.6;">
                    Thank you for choosing B2B Travel! Your flight reservation has been successfully confirmed. 
                    Below you'll find all the details of your upcoming trip.
                </p>
                
                <!-- Reservation Summary Card -->
                <div style="background-color: #f5f7ff; border-radius: 10px; padding: 20px; margin: 25px 0; border-left: 4px solid #1a237e;">
                    <h2 style="color: #1a237e; margin-top: 0; font-size: 20px;">Reservation Summary</h2>
                    
                    <div style="display: flex; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <p style="margin: 5px 0; font-weight: bold;">Reservation Code:</p>
                            <p style="margin: 5px 0; font-weight: bold;">Booking Date:</p>
                            <p style="margin: 5px 0; font-weight: bold;">Payment Method:</p>
                            <p style="margin: 5px 0; font-weight: bold;">Transaction ID:</p>
                        </div>
                        <div style="flex: 1;">
                            <p style="margin: 5px 0;">{$reservation->getReservationCode()}</p>
                            <p style="margin: 5px 0;">{$paymentDate}</p>
                            <p style="margin: 5px 0;">{$reservation->getPaymentMethod()}</p>
                            <p style="margin: 5px 0;">{$reservation->getTransactionId()}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Flight Details -->
                <h2 style="color: #1a237e; font-size: 20px; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">Flight Details</h2>
                
                    
                <!-- Flight Info Table -->
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee; background-color: #f5f7ff;"><strong>Flight Number</strong></td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">{$flightNumber}</td>
                    </tr>


                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee; background-color: #f5f7ff;"><strong>Departure City</strong></td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">{$departureCity}</td>
                    </tr>

                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee; background-color: #f5f7ff;"><strong>Arrival City</strong></td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">{$arrivalCity}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee; background-color: #f5f7ff;"><strong>Airline</strong></td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">{$airline}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee; background-color: #f5f7ff;"><strong>Duration</strong></td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">{$duration} minutes</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee; background-color: #f5f7ff;"><strong>Seats Reserved</strong></td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">{$reservation->getPlace()}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; background-color: #f5f7ff;"><strong>Total Price</strong></td>
                        <td style="padding: 12px; font-weight: bold; color: #1a237e;">{$totalPrice}</td>
                    </tr>
                </table>
                
           
                <!-- Next Steps -->
                <div style="background-color: #f5f7ff; border-radius: 10px; padding: 20px; margin-top: 20px;">
                    <h3 style="color: #1a237e; margin-top: 0;">Next Steps</h3>
                    <ol style="padding-left: 20px; margin-bottom: 0;">
                        <li style="margin-bottom: 8px;">Check in online 24 hours before departure</li>
                        <li style="margin-bottom: 8px;">Arrive at the airport at least 2 hours before departure</li>
                        <li style="margin-bottom: 8px;">Have your ID/passport and this confirmation ready</li>
                        <li>Contact us immediately if any details are incorrect</li>
                    </ol>
                </div>
            </div>
            
            <!-- Footer with Signature -->
            <div style="background-color: #f5f7ff; color: #1a237e;; padding: 25px; text-align: center;">
                <img src="cid:signatureMail.png" alt="B2B Travel Signature" style="max-width: 200px; margin-bottom: 15px;">
                <p style="margin: 5px 0; font-size: 14px;">✈️ B2B Travel - Your Journey Begins Here ✈️</p>
                <p style="margin: 5px 0; font-size: 14px;">
                    <a href="mailto:contact@b2btravel.com" style="color: #1a237e; text-decoration: underline;">contact@b2btravel.com</a> | 
                    <a href="tel:+1234567890" style="color: #1a237e;; text-decoration: underline;">+123 456 7890</a>
                </p>
                <p style="margin: 15px 0 0; font-size: 12px; color: #1a237e;">
                    &copy; 2023 B2B Travel. All rights reserved.
                </p>
            </div>
        </div>
    HTML;
    }
}