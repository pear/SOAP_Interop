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

class SOAPStruct {
    var $varString;
    var $varInt;
    var $varFloat;
    function SOAPStruct($s=NULL, $i=NULL, $f=NULL) {
        $this->varString = $s;
        $this->varInt = $i;
        $this->varFloat = $f;
    }
    
    function &__to_soap($name = 'inputStruct', $header=false, $mustUnderstand=0, $actor='http://schemas.xmlsoap.org/soap/actor/next')
    {
        $inner = array( #push struct elements into one soap value
                new SOAP_Value('varString','string',$this->varString),
                new SOAP_Value('varInt','int',$this->varInt),
                new SOAP_Value('varFloat','float',$this->varFloat)
            );
        if ($header) {
            return new SOAP_Header($name,'{http://soapinterop.org/xsd}SOAPStruct',$inner,$mustUnderstand,$actor);
        }
        return new SOAP_Value($name,'{http://soapinterop.org/xsd}SOAPStruct',$inner);
        
    }
}

class SOAPStructStruct {
    var $varString;
    var $varInt;
    var $varFloat;
    var $varStruct;
    function SOAPStructStruct($s=NULL, $i=NULL, $f=NULL, $ss=NULL) {
        // XXX unfortunately, a copy of $ss will occure here
        // ze2 can fix this I think
        $this->varString = $s;
        $this->varInt = $i;
        $this->varFloat = $f;
        $this->varStruct = $ss;
    }
    
    function &__to_soap($name = 'inputStruct')
    {
        return new SOAP_Value($name,'{http://soapinterop.org/xsd}SOAPStructStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string',$this->varString),
                new SOAP_Value('varInt','int',$this->varInt),
                new SOAP_Value('varFloat','float',$this->varFloat),
                $this->varStruct->__to_soap('varStruct')
            ));
    }    
}

class SOAPArrayStruct {
    var $varString;
    var $varInt;
    var $varFloat;
    var $varArray;
    function SOAPArrayStruct($s=NULL, $i=NULL, $f=NULL, $ss=NULL) {
        // XXX unfortunately, a copy of $ss will occure here
        // ze2 can fix this I think
        $this->varString = $s;
        $this->varInt = $i;
        $this->varFloat = $f;
        $this->varArray = $ss;
    }
    
    function &__to_soap($name = 'inputStruct')
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

class Person {
    var $Age;
    var $ID;
    var $Name;
    var $Male;
    function Person($a=NULL, $i=NULL, $n=NULL, $m=NULL) {
        $this->Age = $a;
        $this->ID = $i;
        $this->Name = $n;
        $this->Male = $m;
    }

    function __set_attribute($key, $value)
    {
        $this->$key = $value;
    }
    
    function &__to_soap($name = 'x_Person',$ns = 'http://soapinterop.org/xsd', $compound2=false)
    {
        if (!$compound2)
            return new SOAP_Value("\{$ns}$name",'Person',
                array( #push struct elements into one soap value
                    new SOAP_Value("\{$ns}Age",'double',$this->Age),
                    new SOAP_Value("\{$ns}ID",'float',$this->ID),
                ),array('Name'=>$this->Name,'Male'=>$this->Male));
        else
            return new SOAP_Value("\{$ns}$name",'Person',
                array( #push struct elements into one soap value
                    new SOAP_Value("\{$ns}Name",'string',$this->Name),
                    new SOAP_Value("\{$ns}Male",'boolean',$this->Male),
                ));        
    }        
}

class x_Person extends Person {
    function x_Person($a=NULL, $i=NULL, $n=NULL, $m=NULL) {
        $parent->Person($a,$i,$n,$m);
    }
}

class Employee {
    var $ID;
    var $salary;
    var $person; // class person2
    function Employee($person=NULL,$id=NULL,$salary=NULL) {
        $this->person = $person;
        $this->ID = $id;
        $this->salary = $salary;
    }
    
    function &__to_soap($name = 'x_Employee', $ns='http://soapinterop.org/employee')
    {
        $person = $this->person->__to_soap('person','http://soapinterop.org/person',true);
        $person->namespace = $ns;
        return new SOAP_Value("\{$ns}$name",'Employee',
            array( #push struct elements into one soap value
                &$person,
                new SOAP_Value("\{$ns}salary",'double',$this->salary),
                new SOAP_Value("\{$ns}ID",'int',$this->ID),
            ));
    }    
}

class x_Employee extends Employee {
    function x_Employee($person=NULL,$id=NULL,$salary=NULL) {
        $parent->Employee($person,$id,$salary);
    }
}
?>