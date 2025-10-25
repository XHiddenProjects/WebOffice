<?php
namespace WebOffice;
class Data{
    public function __construct() {
        
    }
    /**
     * Get the data file from the data folder
     * @param string $file_or_string Data name
     * @return bool|\SimpleXMLElement Validate XML
     */
    public function get(string $file_or_string): bool|\SimpleXMLElement {
        $filePath = DATA_PATH . DS . preg_replace('/\.xml$/i', '', $file_or_string) . ".xml";
        if (file_exists($filePath)) return simplexml_load_file($filePath);
        else {
            // Attempt to parse as XML string
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($file_or_string);
            if ($xml === false) return false;
            return $xml;
        }
    }
    /**
     * Save Data as a XML file
     * @param string $name Data name
     * @param \SimpleXMLElement $xml XML object
     * @return bool TRUE on success, else FALSE
     */
    public function save(string $name, \SimpleXMLElement $xml): bool{
        $name = preg_replace('/\.xml$/i','',$name);
        return (bool)$xml->saveXML(DATA_PATH.DS."$name.xml");
    }
    /**
     * Add a new element to an existing XML file
     * @param string $name Data name
     * @param string $parentXPath XPath to parent node
     * @param string $elementName Name of the new element
     * @param array $attributes Associative array of attributes
     * @param string|null $value Text content for the new element
     * @return bool Success or failure
     */
    public function addElement(string $name, string $parentXPath, string $elementName, array $attributes = [], ?string $value = null): bool {
        $xml = $this->get($name);
        if ($xml === false) return false;
        $parent = $xml->xpath($parentXPath);
        if (empty($parent)) return false;
        $parentNode = $parent[0];
        $newElement = $parentNode->addChild($elementName, $value);
        foreach ($attributes as $attr => $val) $newElement->addAttribute($attr, $val);
        return $this->save($name, $xml);
    }

    /**
     * Remove an element from the XML file
     * @param string $name Data name
     * @param string $xpath XPath to the element to remove
     * @return bool Success or failure
     */
    public function removeElement(string $name, string $xpath): bool {
        $xml = $this->get($name);
        if ($xml === false) return false;
        
        $elements = $xml->xpath($xpath);
        if (empty($elements)) return false;
        
        foreach ($elements as $element) {
            $dom = dom_import_simplexml($element);
            $dom->parentNode->removeChild($dom);
        }
        return $this->save($name, $xml);
    }

    /**
     * Update the value of an element
     * @param string $name Data name
     * @param string $xpath XPath to the element
     * @param string $newValue New text value
     * @return bool Success or failure
     */
    public function updateElementValue(string $name, string $xpath, string $newValue): bool {
        $xml = $this->get($name);
        if ($xml === false) return false;
        $elements = $xml->xpath($xpath);
        if (empty($elements)) return false;
        foreach ($elements as $element) $element[0] = $newValue;
        return $this->save($name, $xml);
    }

    /**
     * Get all data as an array
     * @param string $name Data name
     * @return array|false Array representation or false if file not found
     */
    public function toArray(string $name): false|array {
        $xml = $this->get($name);
        if ($xml === false) return false;
        return json_decode(json_encode($xml), true);
    }
}