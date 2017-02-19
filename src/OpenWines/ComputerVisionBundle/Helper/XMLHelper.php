<?php

namespace OpenWines\ComputerVisionBundle\Helper;

/**
 * XMLHelper
 *
 * @author    Ronan Guilloux <ronan.guilloux@gmail.com>
 * @copyright 2017 Ronan Guilloux
 * @license   MIT
 */
class XMLHelper
{
    /**
     * Convert an array to XML
     * @param array $array
     * @param \SimpleXMLElement $xml
     */
    public static function arrayToXml($array, &$xml){
        foreach ($array as $key => $value) {
            if(is_array($value)){
                if(is_int($key)){
                    $key = "e";
                }
                $label = $xml->addChild($key);
                self::arrayToXml($value, $label);
            }
            else {
                $xml->addChild($key, $value);
            }
        }
    }
}
