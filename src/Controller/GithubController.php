<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GithubController extends AbstractController
{
	
	
	private HttpClientInterface $httpClient;
	
	public function __construct(HttpClientInterface $githubClient)
	{
		$this->httpClient = $githubClient;
	}
	
	/**
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws ClientExceptionInterface
	 */
	public function fetchUserInformations(): array {
		$response = $this->httpClient->request(
			'GET',
			'https://api.github.com/user'
		);
		return $response->toArray();
	}
	
	/**
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws ClientExceptionInterface
	 */
	public function fetchRepositories(): array {
		$response = $this->httpClient->request(
			'GET',
			'https://api.github.com/user/repos'
		);
		return $this->fetchCollaborators($response);
	}
	
	public function fetchGenericUser(string $user): array {
		$response = $this->httpClient->request(
			'GET',
			'https://api.github.com/users/' . $user
		);
		return $response->toArray();
	}
	
	/**
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws ClientExceptionInterface
	 */
	public function fetchGenericRepositories(string $user): array {
		$response = $this->httpClient->request(
			'GET',
			'https://api.github.com/users/' . $user . '/repos'
		);
		
		return $this->fetchCollaborators($response);
	}
	
	/**
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws ClientExceptionInterface
	 */
	#[Route('/github/{user}', name: 'app_github_user')]
	public function UserIndex(string $user): Response {
		return $this->render('github/index.html.twig', [
			'user' => $this->fetchGenericUser($user),
			'repositories' => $this->fetchGenericRepositories($user),
		]);
	}
	
	
	#[Route('/github/{user}/{repository}', name: 'app_github_user_repository')]
	public function RepoIndex(string $user, string $repository): Response {
		$response = $this->httpClient->request(
			'GET',
			'https://api.github.com/repos/' . $user . '/' . $repository
		);
		return $this->render('github/repository.html.twig', [
//			'user' => $this->fetchGenericUser($user),
			'repository' => $response->toArray(),
		]);
	}
	
	
	/**
	 * @throws RedirectionExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws ClientExceptionInterface
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 */
	#[Route('/github', name: 'app_github')]
    public function index(): Response
    {
        return $this->render('github/index.html.twig', [
			'user' => $this->fetchUserInformations(),
			'repositories' => $this->fetchRepositories(),
        ]);
    }
	
	/**
	 * @param ResponseInterface $response
	 * @return array
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	private function fetchCollaborators(ResponseInterface $response): array {
		$repositories = array();
		foreach ($response->toArray() as $repository) {
			$response = $this->httpClient->request(
				'GET',
				'https://api.github.com/repos/' . $repository['owner']['login'] . '/' . $repository['name'] . '/collaborators'
			);
			if ($response->getStatusCode() === 200) {
				$repository['collaborators'] = $response->toArray();
			} else {
				$repository['collaborators'] = [];
			}
			$repositories[] = $repository;
		}
		return $repositories;
	}
}
