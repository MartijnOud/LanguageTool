<?php
/**
 * Use the Public HTTP API of LanguageTool to check for spelling errors
 */
namespace MartijnOud;

class LanguageTool
{

    private $langCode = "nl";

    public function __construct($langCode)
    {
        $this->langCode = $langCode;
    }


    // Make POST request to API
    private function call($strInput)
    {

        $payload = 'language='.$this->langCode.'&text='.urlencode($strInput);
 
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://languagetool.org");
        curl_setopt($curl, CURLOPT_PORT, 8081);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($curl);
        $xml = simplexml_load_string($response);
    
        return $xml;

    }

    private function cleanReplacement($strReplacement) {

        if (strpos($strReplacement, '#') !== false) {
            $pieces = explode('#', $strReplacement);
            $strReplacement = $pieces[0];
        }

        return $strReplacement;
    }

    private function checkSpelling($xml)
    {

        $arrReplacements = array();
        foreach ($xml->error as $error) {

            if ($error['locqualityissuetype'] == "misspelling") {
                
                // Get first replacement
                $strReplacement = (string) $error['replacements'];
                $strReplacement = $this->cleanReplacement($strReplacement);

                $strError = (string)$error['context'];
                $strContext = (string)$error['context'];

                // handle replacement inside a full sentence
                if (!empty($error['fromx']) AND !empty($error['tox'])) {
                   
                    // get specific error word from complete sentence
                    $fromX = (int)$error['fromx'];
                    $len = strlen($strReplacement);
                    $strError = substr($strContext, $fromX, $len);

                }

                // Add replacement to array
                $arrReplacements[$strError] = $strReplacement;
               
            }

        }

        // Replace all occurences
        $strReplacement = $strContext;
        foreach ($arrReplacements as $key => $value) {
            $strReplacement = str_replace($key, $value, $strReplacement);
        }

        return array (
            'return' => $strReplacement,
            'count' => count($arrReplacements),
            'replacements' => $arrReplacements,
        );
    }

    /**
     * Make a call to the API
     * @param string Text with (potential) spelling mistakes
     * @return string Text with fixed spelling mistakes 
     */
    public function check($strInput)
    {
        if (!empty($strInput)) {

            $xml = $this->call($strInput);
            $strReplacement = $this->checkSpelling($xml);

            return $strReplacement['return'];
        }
    }
    
}