<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 6/30/2015
 * Time: 5:29 PM
 */

namespace common\helpers;


class TXmlHelper {

    public $arroutput = array();
    public $resparser;
    public $strxmldata;

    /**
     * Convert a utf-8 string to html entities
     *
     * @param string $str The UTF-8 string
     * @return string
     */
    public function utf8_to_entities($str) {
        $entities = '';
        $values = array();
        $lookingfor = 1;

        return $str;
    }

    /**
     * Parse an XML text string and create an array tree that rapresent the XML structure
     *
     * @param string $strinputxml The XML string
     * @return array
     */
    public function parse($strinputxml) {
        $this->resparser = xml_parser_create ('UTF-8');
        xml_set_object($this->resparser, $this);
        xml_set_element_handler($this->resparser, "tagopen", "tagclosed");

        xml_set_character_data_handler($this->resparser, "tagdata");

        $this->strxmldata = xml_parse($this->resparser, $strinputxml );
        if (!$this->strxmldata) {
            die(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($this->resparser)),
                xml_get_current_line_number($this->resparser)));
        }

        xml_parser_free($this->resparser);

        return $this->arroutput;
    }

    public function tagopen($parser, $name, $attrs) {
        $tag = array("name" => $name, "attrs" => $attrs);
        array_push($this->arroutput, $tag);
    }

    public function tagdata($parser, $tagdata) {
        if (trim($tagdata)) {
            if (isset($this->arroutput[count($this->arroutput) - 1]['tagData'])) {
                $this->arroutput[count($this->arroutput) - 1]['tagData'] .= $this->utf8_to_entities($tagdata);
            } else {
                $this->arroutput[count($this->arroutput) - 1]['tagData'] = $this->utf8_to_entities($tagdata);
            }
        }
    }

    public function tagclosed($parser, $name) {
        $this->arroutput[count($this->arroutput) - 2]['children'][] = $this->arroutput[count($this->arroutput) - 1];
        array_pop($this->arroutput);
    }
}