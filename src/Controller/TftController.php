<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TftController extends AbstractController
{
	private HttpClientInterface $httpClient;
	
	public function __construct(HttpClientInterface $riotClient)
	{
		$this->httpClient = $riotClient;
	}
	
	public function fetchUserInformations(): array {
		$response = $this->httpClient->request(
			'GET',
			'https://euw1.api.riotgames.com/tft/summoner/v1/summoners/by-name/zarchtrack?api_key=RGAPI-4b9b0b9a-4b1e-4b0e-8b0a-4b9b0b9a4b1e'
		);
		return $response->toArray();
	}
	
    #[Route('/tft', name: 'app_tft')]
    public function index(): Response
    {
		
        return $this->render('tft/index.html.twig', [
			'user' => $this->fetchUserInformations(),
        ]);
    }
}
