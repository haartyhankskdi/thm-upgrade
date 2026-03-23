<?php

namespace Ebizmarts\SagePaySuite\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Laminas\Uri\UriFactory;

class AcsUrl
{
    private $resourceConnection;
    private $request;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Request $request
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Request $request
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->request = $request;
    }

    public function saveAcsUrlDomain($acsUrl)
    {
        $domain = $this->extractHostFromUrl($acsUrl);

        if ($domain) {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('sagepay_acsurl_domains');

            // Check if the domain already exists
            $select = $connection->select()
                ->from($tableName, ['domain'])
                ->where('domain = ?', $domain);
            $existingDomain = $connection->fetchOne($select);

            if (!$existingDomain) {
                $connection->insert($tableName, ['domain' => $domain]);
            }
        }
    }

    public function shouldAllowAcsUrl($acsUrl)
    {
        $domain = $this->extractHostFromUrl($acsUrl);

        if ($domain) {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('sagepay_acsurl_domains');

            $select = $connection->select()
                ->from($tableName, ['domain'])
                ->where('domain = ?', $domain);

            return (bool) $connection->fetchOne($select);
        }

        return false;
    }

    /**
     * Extract hostname from URL using Magento's URL utilities
     *
     * @param string $url
     * @return string|null
     */
    private function extractHostFromUrl($url)
    {
        // Create a temporary zend request to parse the URL
        $uri = UriFactory::factory($url);

        return $uri->getHost();
    }

    /**
     * Check if ACS URL domain is saved
     *
     * @param string $acsUrl
     * @return bool
     */
    public function isAcsUrlDomainSaved($acsUrl)
    {
        return $this->shouldAllowAcsUrl($acsUrl);
    }
}
