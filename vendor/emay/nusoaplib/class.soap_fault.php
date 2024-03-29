<?php

/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */
class nusoap_fault extends nusoap_base
{
    /**
     * The fault code (client|server)
     * @var string
     * @access private
     */
    public $faultcode;
    /**
     * The fault actor
     * @var string
     * @access private
     */
    public $faultactor;
    /**
     * The fault string, a description of the fault
     * @var string
     * @access private
     */
    public $faultstring;
    /**
     * The fault detail, typically a string or array of string
     * @var mixed
     * @access private
     */
    public $faultdetail;

    /**
     * constructor
     *
     * @param string $faultcode (SOAP-ENV:Client | SOAP-ENV:Server)
     * @param string $faultactor only used when msg routed between multiple actors
     * @param string $faultstring human readable error message
     * @param mixed $faultdetail detail, typically a string or array of string
     */
    public function nusoap_fault($faultcode, $faultactor = '', $faultstring = '', $faultdetail = '')
    {
        parent::nusoap_base();
        $this->faultcode = $faultcode;
        $this->faultactor = $faultactor;
        $this->faultstring = $faultstring;
        $this->faultdetail = $faultdetail;
    }

    /**
     * serialize a fault
     *
     * @return    string    The serialization of the fault instance.
     * @access   public
     */
    public function serialize()
    {
        $ns_string = '';

        foreach ($this->namespaces as $k => $v) {
            $ns_string .= "\n" . '  xmlns:' . $k . '="' . $v . '"';
        }

        $return_msg = '<?xml version="1.0" encoding="' . $this->soap_defencoding . '"?>' . '<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"' . $ns_string . '>' . "\n" . '<SOAP-ENV:Body>' . '<SOAP-ENV:Fault>' . $this->serialize_val($this->faultcode, 'faultcode') . $this->serialize_val($this->faultactor, 'faultactor') . $this->serialize_val($this->faultstring, 'faultstring') . $this->serialize_val($this->faultdetail, 'detail') . '</SOAP-ENV:Fault>' . '</SOAP-ENV:Body>' . '</SOAP-ENV:Envelope>';
        return $return_msg;
    }
}

class soap_fault extends nusoap_fault
{
}


?>