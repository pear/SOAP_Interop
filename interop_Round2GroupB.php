<?
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
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id$
//
require_once 'params_classes.php';

class SOAP_Interop_GroupB {
    var $__dispatch_map = array();
    
    function SOAP_Interop_GroupB() {
	$this->__dispatch_map['echoStructAsSimpleTypes'] =
		array('in' => array('inputStruct' => 'SOAPStruct'),
		      'out' => array('outputString' => 'string', 'outputInteger' => 'int', 'outputFloat' => 'float')
		      );
    }
    
    /* this private function is called on by SOAP_Server to determine any
        special dispatch information that might be necessary.  This, for example,
        can be used to set up a dispatch map for functions that return multiple
        OUT parameters */
    function __dispatch($methodname) {
        if (isset($this->__dispatch_map[$methodname]))
            return $this->__dispatch_map[$methodname];
        return NULL;
    }
    
    function &echoStructAsSimpleTypes (&$struct)
    {
	# convert a SOAPStruct to an array
	return array(
	    new SOAP_Value('outputString','string',$struct->varString),
	    new SOAP_Value('outputInteger','int',$struct->varInt),
	    new SOAP_Value('outputFloat','float',$struct->varFloat)
	    );
    }

    function &echoSimpleTypesAsStruct(&$string, &$int, &$float)
    {
	# convert a input into struct
	return new SOAP_Value('return','{http://soapinterop.org/xsd}SOAPStruct',
		new SOAPStruct($string, $int, $float)
	    );
    }

    function &echoNestedStruct(&$struct)
    {
        if (array_key_exists('__to_soap',get_class_methods($struct)))
            return $struct->__to_soap();
        return $struct;
    }

    function &echo2DStringArray(&$ary)
    {
	$ret = new SOAP_Value('return','Array',$ary);
	$ret->options['flatten'] = TRUE;
	return $ret;
    }

    function &echoNestedArray(&$ary)
    {
	return $ary;
    }
}

?>