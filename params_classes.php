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

if (!class_exists('SOAPStruct')) {
class SOAPStruct {
    var $varString;
    var $varInt;
    var $varFloat;
    function SOAPStruct($s='arg', $i=34, $f=325.325) {
        $this->varString = $s;
        $this->varInt = $i;
        $this->varFloat = $f;
    }
    
    function &to_soap($name = 'inputStruct')
    {
        return new SOAP_Value($name,'{http://soapinterop.org/xsd}SOAPStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string',$this->varString),
                new SOAP_Value('varInt','int',$this->varInt),
                new SOAP_Value('varFloat','float',$this->varFloat)
            ));
    }
}
}

if (!class_exists('SOAPStructStruct')) {
class SOAPStructStruct {
    var $varString;
    var $varInt;
    var $varFloat;
    var $varStruct;
    function SOAPStructStruct($s='arg', $i=34, $f=325.325, $ss=NULL) {
        // XXX unfortunately, a copy of $ss will occure here
        // ze2 can fix this I think
        $this->varString = $s;
        $this->varInt = $i;
        $this->varFloat = $f;
        if (!$ss) $ss = new SOAPStruct();
        $this->varStruct = $ss;
    }
    
    function &to_soap($name = 'inputStruct')
    {
        return new SOAP_Value($name,'{http://soapinterop.org/xsd}SOAPStructStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string',$this->varString),
                new SOAP_Value('varInt','int',$this->varInt),
                new SOAP_Value('varFloat','float',$this->varFloat),
                $this->varStruct->to_soap('varStruct')
            ));
    }    
}
}

if (!class_exists('SOAPArrayStruct')) {
class SOAPArrayStruct {
    var $varString;
    var $varInt;
    var $varFloat;
    var $varArray;
    function SOAPArrayStruct($s='arg', $i=34, $f=325.325, $ss=array('good','bad','ugly')) {
        // XXX unfortunately, a copy of $ss will occure here
        // ze2 can fix this I think
        $this->varString = $s;
        $this->varInt = $i;
        $this->varFloat = $f;
        $this->varArray = $ss;
    }
    
    function &to_soap($name = 'inputStruct')
    {
        $ar = array();
        $c = count($this->varArray);
        for ($i=0; $i<$c; $i++) {
            $ar[] = new SOAP_Value('item','string',$this->varArray[$i]);
        }
        return new SOAP_Value($name,'{http://soapinterop.org/xsd}SOAPArrayStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string',$this->varString),
                new SOAP_Value('varInt','int',$this->varInt),
                new SOAP_Value('varFloat','float',$this->varFloat),
                new SOAP_Value('varArray',false,$ar)
            ));
    }    
}
}

if (!class_exists('Person')) {
class Person1 {
    var $Age;
    var $ID;
    var $Name;
    var $Male;
    function Person1($a=31, $i='123456', $n='Shane', $m=TRUE) {
        $this->Age = $a;
        $this->ID = $i;
        $this->Name = $n;
        $this->Male = $m;
    }
}

class Person2 {
    var $Name;
    var $Male;
    function Person2($n = 'Shane', $m = TRUE) {
        $this->Name = $n;
        $this->Male = $m;
    }
}

class Employee {
    var $ID;
    var $salary;
    var $person; // class person2
    function Employee(&$person,$id='12435',$salary='1000000000000') {
        $this->person = $person;
        $this->ID = $id;
        $this->salary = $salary;
    }
}
}

?>