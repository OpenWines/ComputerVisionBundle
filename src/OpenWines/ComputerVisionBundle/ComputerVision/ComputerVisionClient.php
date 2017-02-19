<?php

namespace OpenWines\ComputerVisionBundle\ComputerVision;

/**
 * ComputerVisionClient
 *
 * @author    Ronan Guilloux <ronan.guilloux@gmail.com>
 * @copyright 2017 Ronan Guilloux
 * @license   MIT
 */

use OpenWines\ComputerVisionBundle\Helper\XMLHelper;
use GuzzleHttp\Client as GuzzleHttpClient;
use Symfony\Component\HttpFoundation\File\File;

/**
 * ComputerVisionClient
 *
 * @author    Ronan Guilloux <ronan.guilloux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputerVisionClient
{
    /**
     * @var string API_URL
     * @link https://westus.dev.cognitive.microsoft.com/docs/services/56f91f2d778daf23d8ec6739/operations/56f91f2e778daf14a499e1fc
     */
    const API_URL = 'https://westus.api.cognitive.microsoft.com/vision/v1.0/ocr'; //[?language][&detectOrientation]

    /**
     * @var array INVALID_SOURCES: invalid file names while crawling folders
     */
    const INVALID_SOURCES = ['.', '..', 'Thumbs.db', '.DS_Store'];

    /**
     * @var GuzzleHttpClient
     */
    private $client;

    /**
     * @var string Api key
     */
    private $apiKey;

    /**
     * @var array
     */
    private $results = [];

    /**
     * ComputerVisionClient constructor.
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->client = new GuzzleHttpClient();
        $this->apiKey = $apiKey;
    }

    /**
     * Main, recursive processing function
     * @param string $source
     * @param string $language
     * @param string $detectOrientation
     * @return array
     */
    public function process($source, $language, $detectOrientation = 'false')
    {
        if(is_file($source)) {
            $this->results[] = $this->processImage($source, $language, $detectOrientation);
        }
        if (is_dir($source)) {
            $scanned_directory = array_diff(scandir($source), array('..', '.'));
            foreach($scanned_directory as $found) {
                $found = sprintf('%s/%s', $source, $found);
                if(is_file($found)) {
                    $this->results[] = $this->processImage($found, $language, $detectOrientation);
                }
                if(is_dir($found)) {
                    array_merge($this->results, $this->process($found, $language, $detectOrientation));
                }
            }
        }
        $this->results = array_filter($this->results, function($var){return !is_null($var);} );
        return $this->results;
    }

    /**
     * Image specific processing
     * @param string $source
     * @param string $language
     * @param string $detectOrientation
     * @return array|null
     * @throws \HttpException
     * @link https://westus.dev.cognitive.microsoft.com/docs/services/56f91f2d778daf23d8ec6739/operations/56f91f2e778daf14a499e1fc
     */
    public function processImage($source, $language, $detectOrientation)
    {
        $file = new File($source);
        if(in_array($file->getBasename(), self::INVALID_SOURCES)) {
            return null;
        }
        $texts = [];
        $xml = new \SimpleXMLElement('<root/>');
        $res = $this->client->request('POST', self::API_URL, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $this->apiKey,
                'Content-type' => 'application/octet-stream'
            ],
            'query' => [
                'language' => $language,
                'detectOrientation ' => $detectOrientation
            ],
            'body'=> fopen($file->getPathname(), 'r')
        ]);

        if(200 != $res->getStatusCode()) {
            throw new \HttpException(sprintf('Error: bad request or server issue: %s', $res->getBody()));
        }
        // Json2XML, then xpath, to quick-retrieve all texts:
        XMLHelper::arrayToXml(json_decode($res->getBody(), true), $xml);
        foreach($xml->xpath('//text') as $xml){
            $texts[] = (string)$xml;
        }

        return ['source'=>$source, 'text' => join(' ', $texts)];
    }
}