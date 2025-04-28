<?php

namespace App\EventCalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/mini-calendar-data', name: 'mini_calendar_data')]
    public function getCalendarData(): Response
    {
        $events = $this->getDoctrine()
            ->getRepository(Evennement::class)
            ->findAll();

        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'title' => $event->getNomE(),
                'start' => $event->getDateE()->format('Y-m-d\TH:i:s'),
              
                'extendedProps' => [
                    'description' => $event->getDesE(),
                    'location' => $event->getLocal()
                ]
            ];
        }

        return $this->json($formattedEvents);
    }

  
}