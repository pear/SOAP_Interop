<?
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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
	$this->__dispatch_map['echoSimpleTypesAsStruct'] =
		array('in' => array('inputString' => 'string', 'inputInteger' => 'int', 'inputFloat' => 'float'),
		      'out' => array('return' => 'SOAPStruct')
		      );
	$this->__dispatch_map['echoNestedStruct'] =
		array('in' => array('inputStruct' => 'SOAPStructStruct'),
		      'out' => array('return' => 'SOAPStructStruct')
		      );
	$this->__dispatch_map['echo2DStringArray'] =
		array('in' => array('input2DStringArray' => 'ArrayOfString2D'),
		      'out' => array('return' => 'ArrayOfString2D')
		      );
	$this->__dispatch_map['echoNestedArray'] =
		array('in' => array('inputString' => 'SOAPArrayStruct'),
		      'out' => array('return' => 'SOAPArrayStruct')
		      );
    }
    
    /* this private function is called on by SOAP_Server to determine any
        special dispatch information that might be necessary.  This, for example,
        can be used to set up a dispatch map for functions that return multiple
        OUT parameters */
    function __dispatch($methodname) {
        if (array_key_exists($methodname,$this->__dispatch_map))
            return $this->__dispatch_map[$methodname];
        return NULL;
    }
    
    function &echoStructAsSimpleTypes (&$struct)
    {
	# convert a SOAPStruct to an array
	$r[] =& new SOAP_Value('outputString','string',$struct->varString);
	$r[] =& new SOAP_Value('outputInteger','int',$struct->varInt);
	$r[] =& new SOAP_Value('outputFloat','float',$struct->varFloat);
	return $r;
    }

    function &echoSimpleTypesAsStruct(&$string, &$int, &$float)
    {
	# convert a input into struct
	$v =& new SOAPStruct($string, $int, $float);
	return new SOAP_Value('return','{http://soapinterop.org/xsd}SOAPStruct',$v);
    }

    function &echoNestedStruct(&$struct)
    {
        if (array_key_exists('__to_soap',get_class_methods($struct)))
            return $struct->__to_soap();
        return $struct;
    }

    function &echo2DStringArray(&$ary)
    {
	$ret =& new SOAP_Value('return','Array',$ary);
	$ret->options['flatten'] = TRUE;
	return $ret;
    }

    function &echoNestedArray(&$ary)
    {
	return $ary;
    }
}

?>