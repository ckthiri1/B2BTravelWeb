<?php
namespace App\Service;

use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GeocodingService
{
    private $geocoder;
    private $cache;
    private $logger;

    public function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
        
        // Create PSR-18 compatible client
        $httpClient = new Psr18Client();
        
        $provider = Nominatim::withOpenStreetMapServer(
            $httpClient,
            'YourAppName/1.0 (your@email.com)'
        );
        
        $this->geocoder = new StatefulGeocoder($provider, 'en');
    }

    

public function getCityFromCoordinates(float $latitude, float $longitude): ?string
{
    $cacheKey = 'geocode_'.md5($latitude.','.$longitude);
    
    return $this->cache->get($cacheKey, function(ItemInterface $item) use ($latitude, $longitude) {
        $item->expiresAfter(86400); // Cache for 24 hours
        
        try {
            sleep(1);
            $result = $this->geocoder->reverseQuery(
                ReverseQuery::fromCoordinates($latitude, $longitude)
            );
            
            if ($result->isEmpty()) {
                return null;
            }
    
            $location = $result->first();
            $address = $location->toArray();
            
            
            return $address['locality'] 
                ?? $address['town'] 
                ?? $address['city']
                ?? $address['village']
                ?? $address['municipality']
                ?? $address['county']
                ?? $address['state']
                ?? $address['country'];
        } catch (\Exception $e) {
            $this->logger->error('Geocoding failed: '.$e->getMessage());
            return null;
        }
    });
}
}