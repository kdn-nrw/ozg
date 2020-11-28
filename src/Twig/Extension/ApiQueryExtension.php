<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2020 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Extension;

use App\Api\Consumer\ApiManager;
use App\Api\Consumer\Exception\ApiConsumerNotFoundException;
use App\Api\Consumer\InjectApiManagerTrait;
use App\Entity\Api\ServiceBaseResult;
use App\Entity\Service;
use App\Entity\StateGroup\Commune;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ApiQueryExtension extends AbstractExtension
{
    use InjectApiManagerTrait;

    /**
     * Store the accessibility state for the API keys
     * @var array|bool[]
     */
    private $apiAccessStorage = [];

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('app_get_api_arguments', [$this, 'getApiArguments']),
        ];
    }

    /**
     * Determine if the api is accessible depending on the mode (backend or frontend)
     * @param string $apiIdentifier The api identifier
     * @param bool $isBackendMode True for backend, false for frontend
     * @return bool
     */
    private function hasGeneralApiAccess(string $apiIdentifier, bool $isBackendMode): bool
    {
        $internalKey = $apiIdentifier . '_' . ($isBackendMode ? 'B' : 'F');
        if (!array_key_exists($internalKey, $this->apiAccessStorage)) {
            if ($isBackendMode) {
                $hasAccess = $this->authorizationChecker->isGranted('ROLE_VSM');
            } else {
                try {
                    $consumer = $this->apiManager->getConfiguredConsumer($apiIdentifier, null, false);
                    $hasAccess = null !== $consumer;
                } catch (ApiConsumerNotFoundException $e) {
                    $hasAccess = false;
                }
            }
            $this->apiAccessStorage[$internalKey] = $hasAccess;
        }
        return $this->apiAccessStorage[$internalKey];
    }

    /**
     * Returns the query parameter string for api calls
     *
     * @param string $apiIdentifier The api identifier
     * @param Commune|Service $parent
     * @param Service|null $service
     * @param bool $isBackendMode
     * @return array|null
     */
    public function getApiArguments(string $apiIdentifier, $parent, ?Service $service, bool $isBackendMode): ?array
    {
        if ($this->hasGeneralApiAccess($apiIdentifier, $isBackendMode)) {
            $query = null;
            if ($apiIdentifier === ApiManager::API_KEY_ZU_FI) {
                $query = null;
                $serviceBaseResult = $this->getServiceBaseResult($parent, $service);
                if (null !== $serviceBaseResult && null !== $refService = $serviceBaseResult->getService()) {
                    $query = $refService->getServiceKey() . '|' . $serviceBaseResult->getRegionalKey();
                }
            }
            if ($query) {
                return [
                    'consumerKey' => $apiIdentifier,
                    'query' => $query,
                ];
            }
        }
        return null;
    }

    /**
     * Returns the service base result for the given service
     *
     * @param Commune|Service $parent
     * @param Service|null $service
     * @return ServiceBaseResult|null
     */
    protected function getServiceBaseResult($parent, Service $service = null): ?ServiceBaseResult
    {
        $serviceBaseResult = null;
        if ($parent instanceof Commune) {
            $serviceBaseResult = $parent->getServiceBaseResult($service);
            if (null !== $serviceBaseResult && $regionalKey = $parent->getRegionalKey()) {
                $serviceBaseResult->setRegionalKey($regionalKey);
                $serviceBaseResult->setCommune($parent);
            }
        } elseif ($parent instanceof Service) {
            $serviceBaseResult = $parent->getServiceBaseResult();
        } elseif (null !== $service) {
            $serviceBaseResult = $service->getServiceBaseResult();
        }
        return $serviceBaseResult;
    }
}
