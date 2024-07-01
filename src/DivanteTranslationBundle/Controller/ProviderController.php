<?php

namespace DivanteTranslationBundle\Controller;

use DivanteTranslationBundle\Provider\ProviderFactory;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
final class ProviderController extends UserAwareController
{
    use JsonHelperTrait;

    private string $sourceLanguage;
    private string $provider;

    public function __construct(string $sourceLanguage, string $provider)
    {
        $this->sourceLanguage = $sourceLanguage;
        $this->provider = $provider;
    }

    /**
     * @Route("/translate-provider", methods={"GET"})
     */
    public function translationProviderInfoAction(): JsonResponse
    {
        return $this->jsonResponse([
            'provider' => $this->provider
        ]);
    }
}
