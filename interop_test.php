<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//
require_once 'SOAP/Value.php';

define('SOAP_TEST_ACTOR_NEXT','http://schemas.xmlsoap.org/soap/actor/next');
define('SOAP_TEST_ACTOR_OTHER','http://some/other/actor');

class SOAP_Interop_Test {
    var $type = 'php';
    var $test_name = NULL;
    var $method_name = NULL;
    var $method_params = NULL;
    var $expect = NULL;
    var $expect_fault = FALSE;
    var $headers = NULL;
    var $headers_expect = NULL;
    var $result = array();
    var $show = 1;
    var $debug = 0;
    var $encoding = SOAP_DEFAULT_ENCODING;
    
    function SOAP_Interop_Test($methodname, $params, $expect = NULL) {
        if (strchr($methodname,'(')) {
            preg_match('/(.*)\((.*)\)/',$methodname,$matches);
            $this->test_name = $methodname;
            $this->method_name = $matches[1];
        } else {
            $this->test_name = $this->method_name = $methodname;
        }
        $this->method_params = $params;
        $this->expect = $expect;
        
        // determine test type
        if ($params) {
        $v = array_values($params);
        if (gettype($v[0]) == 'object' && get_class($v[0]) == 'soap_value')
            $this->type = 'soapval';
        }
    }
    
    function setResult($ok, $result, $wire, $error = '', $fault = NULL)
    {
        $this->result['success'] = $ok;
        $this->result['result'] = $result;
        $this->result['error'] = $error;
        $this->result['wire'] = $wire;
        $this->result['fault'] = $fault;
    }

    /**
    *  showMethodResult
    * print simple output about a methods result
    *
    * @param array endpoint_info
    * @param string method
    * @access public
    */    
    function showTestResult($debug = 0) {
        // debug output
        if ($debug) $this->show = 1;
        if ($debug) {
            echo str_repeat("-",50)."<br>\n";
        }
        
        echo "testing $this->test_name : ";
        if ($this->headers) {
            foreach ($this->headers as $h) {
                if (get_class($h) == 'soap_header') {
                    
                    echo "\n    {$h->name},{$h->attributes['SOAP-ENV:actor']},{$h->attributes['SOAP-ENV:mustUnderstand']} : ";
                } else {
                    if (!$h[4]) $h[4] = SOAP_TEST_ACTOR_NEXT;
                    if (!$h[3]) $h[3] = 0;
                    echo "\n    $h[0],$h[4],$h[3] : ";
                }
            }
        }
        
        if ($debug) {
            print "method params: ";
            print_r($this->params);
            print "\n";
        }
        
        $ok = $this->result['success'];
        if ($ok) {
            print "SUCCESS\n";
        } else {
            $fault = $this->result['fault'];
            if ($fault) {
                print "FAILED: {$fault['faultcode']} {$fault['faultstring']}\n";
            } else {
                print "FAILED: ".$this->result['result']."\n";
            }
        }
        if ($debug) {
            echo "<pre>\n".htmlentities($this->result['wire'])."</pre>\n";
        }
    }
}

?>