<?php
/**
 * @author Łukasz Marszałek <lmarszalek@divante.co>
 * @author Piotr Rugała <piotr@isedo.pl>
 * @copyright Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

declare(strict_types=1);

namespace DivanteTranslationBundle\Controller;

use DivanteTranslationBundle\Provider\ProviderFactory;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/object")
 */
final class ObjectController extends UserAwareController
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
     * @Route("/translate-field", methods={"GET"})
     */
    public function translateFieldAction(Request $request, ProviderFactory $providerFactory): JsonResponse
    {
        try {
            $object = DataObject::getById($request->get('sourceId'));

            $lang = $request->get('lang');
            $fieldName = 'get' . ucfirst($request->get('fieldName'));

            $data = $object->$fieldName($lang) ?: $object->$fieldName($this->sourceLanguage);

            if (!$data) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Data are empty',
                ]);
            }

            $provider = $providerFactory->get($this->provider);
            if ($request->get('formality') && ($this->provider === 'deepl' || $this->provider === 'deepl_free')) {
                $provider->setFormality($request->get('formality'));
            }

            $data = strip_tags($data);
            $data = $provider->translate($data, $lang);
        } catch (\Throwable $exception) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }
}
