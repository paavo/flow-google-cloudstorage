<?php
declare(strict_types=1);

namespace Flownative\Google\CloudStorage;

/*
 * This file is part of the Flownative.Google.CloudStorage package.
 *
 * (c) Robert Lemke, Flownative GmbH - www.flownative.com
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Google\Cloud\Core\ServiceBuilder;
use Google\Cloud\Storage\StorageClient;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Utility\Environment;

/**
 * Factory for the Google Cloud Storage service class
 *
 * @Flow\Scope("singleton")
 */
class StorageFactory
{
    /**
     * @Flow\InjectConfiguration("profiles")
     * @var array
     */
    protected $credentialProfiles;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * Creates a new Storage instance and authenticates against the Google API
     *
     * @param string $credentialsProfileName
     * @return StorageClient
     * @throws Exception
     */
    public function create(string $credentialsProfileName = 'default'): StorageClient
    {
        if (!isset($this->credentialProfiles[$credentialsProfileName])) {
            throw new Exception(sprintf('The specified Google Cloud Storage credentials profile "%s" does not exist, please check your settings.', $credentialsProfileName), 1446553024);
        }

        if (!empty($this->credentialProfiles[$credentialsProfileName]['credentials']['privateKeyJsonBase64Encoded'])) {
            $googleCloud = new ServiceBuilder([
                'keyFile' => json_decode(base64_decode($this->credentialProfiles[$credentialsProfileName]['credentials']['privateKeyJsonBase64Encoded']), true)
            ]);
        } else {
            if (strpos($this->credentialProfiles[$credentialsProfileName]['credentials']['privateKeyJsonPathAndFilename'], '/') !== 0) {
                $privateKeyPathAndFilename = FLOW_PATH_ROOT . $this->credentialProfiles[$credentialsProfileName]['credentials']['privateKeyJsonPathAndFilename'];
            } else {
                $privateKeyPathAndFilename = $this->credentialProfiles[$credentialsProfileName]['credentials']['privateKeyJsonPathAndFilename'];
            }

            if (!file_exists($privateKeyPathAndFilename)) {
                throw new Exception(sprintf('The Google Cloud Storage private key file "%s" does not exist. Either the file is missing or you need to adjust your settings.', $privateKeyPathAndFilename), 1446553054);
            }
            $googleCloud = new ServiceBuilder([
                'keyFilePath' => $privateKeyPathAndFilename
            ]);
        }

        return $googleCloud->storage();
    }
}
