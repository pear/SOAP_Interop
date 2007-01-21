<?php
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
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//
require_once 'SOAP/Value.php';
require_once 'params_classes.php';

$soap_test_null = NULL;

$string = 'Hello World';
$string_soapval =& new SOAP_Value('inputString','string',$string);
$string_null = NULL;
$string_null_soapval =& new SOAP_Value('inputString','string',$string_null);
$string_entities = "this is a test <hello>\n";
$string_entities_soapval =& new SOAP_Value('inputString','string',$string_entities);
$string_utf8 = utf8_encode('ỗÈéóÒ₧⅜ỗỸ');
$string_utf8_soapval =& new SOAP_Value('inputString','string',$string_utf8);
$string_array = array('good','bad');
$v[] =& new SOAP_Value('item','string','good');
$v[] =& new SOAP_Value('item','string','bad');
$string_array_soapval =& new SOAP_Value('inputStringArray','Array',$v);

$string_array_one = array('good');
$v = array();
$v[] =& new SOAP_Value('item','string','good');
$string_array_one_soapval =& new SOAP_Value('inputStringArray','Array',$v);

$string_array_null = NULL;
$string_array_null_soapval =& new SOAP_Value('inputStringArray','Array',NULL);
$string_array_null_soapval->arrayType='{http://www.w3.org/2001/XMLSchema}string';

$integer = 12345;
$integer_soapval =& new SOAP_Value('inputInteger','int',$integer);
$integer_array = array(1,234324324,2);
$v = array();
$v[] =& new SOAP_Value('item','int',1);
$v[] =& new SOAP_Value('item','int',234324324);
$v[] =& new SOAP_Value('item','int',2);
$integer_array_soapval =& new SOAP_Value('inputIntegerArray','Array',$v);

$integer_array_null = NULL;
$integer_array_null_soapval =& new SOAP_Value('inputIntegerArray','Array',NULL);
$integer_array_null_soapval->arrayType='{http://www.w3.org/2001/XMLSchema}int';

$float = 123.45;
$float_soapval =& new SOAP_Value('inputFloat','float',$float);
$float_array = array(1.0,2343.24324,-2.5);
$v = array();
$v[] =& new SOAP_Value('item','float',1.0);
$v[] =& new SOAP_Value('item','float',2343.24324);
$v[] =& new SOAP_Value('item','float',-2.5);
$float_array_soapval =& new SOAP_Value('inputFloatArray','Array',$v);

$float_array_null = NULL;
$float_array_null_soapval =& new SOAP_Value('inputFloatArray','Array',NULL);
$float_array_null_soapval->arrayType='{http://www.w3.org/2001/XMLSchema}float';

$soapstruct =& new SOAPStruct('arg',34,325.325);
$soapstruct_soapval = $soapstruct->__to_soap();
$soapstruct_header_soapval = $soapstruct->__to_soap('{http://soapinterop.org/echoheader/}echoMeStructRequest');
$soapstruct_array = array(&$soapstruct, &$soapstruct, &$soapstruct);
$soapstruct_array_soapval =& new SOAP_Value('inputStructArray','Array',
    array(&$soapstruct_soapval,&$soapstruct_soapval,&$soapstruct_soapval));

$soapstructstruct =& new SOAPStructStruct('arg',34,325.325,$soapstruct);
$soapstructstruct_soapval = $soapstructstruct->__to_soap();
$soapstructstruct_array = array(&$soapstructstruct, &$soapstructstruct, &$soapstructstruct);
$soapstructstruct_array_soapval =& new SOAP_Value('inputStructArray','Array',
    array(&$soapstructstruct_soapval,&$soapstructstruct_soapval,&$soapstructstruct_soapval));

$soaparraystruct =& new SOAPArrayStruct('arg',34,325.325,array('good','bad','ugly'));
$soaparraystruct_soapval = $soaparraystruct->__to_soap();
$soaparraystruct_array = array(&$soaparraystruct, &$soaparraystruct, &$soaparraystruct);
$soaparraystruct_array_soapval =& new SOAP_Value('inputStructArray','Array',
    array(&$soaparraystruct_soapval,&$soaparraystruct_soapval,&$soaparraystruct_soapval));

$simpletypes = array(
        'inputString'=>'arg',
        'inputInteger'=>34,
        'inputFloat'=>325.325
    );

$simpletypes_soapval = array();
$simpletypes_soapval[] =& new SOAP_Value('inputString','string','arg');
$simpletypes_soapval[] =& new SOAP_Value('inputInteger','int',34);
$simpletypes_soapval[] =& new SOAP_Value('inputFloat','float',325.325);

$base64 = 'TmVicmFza2E=';
$base64_soapval =& new SOAP_Value('inputBase64','base64Binary',$base64);

$hexBin = '736F61707834';
$hexBin_soapval =& new SOAP_Value('inputHexBinary','hexBinary',$hexBin);

$decimal = 12345.67890;
$decimal_soapval =new SOAP_Value('inputDecimal','decimal',$decimal);

$dateTime = '2001-05-24T17:31:41Z';
$dateTime_soapval =& new SOAP_Value('inputDate','dateTime',$dateTime);

$boolean_true = TRUE;
$boolean_true_soapval =& new SOAP_Value('inputBoolean','boolean',TRUE);
$boolean_false = FALSE;
$boolean_false_soapval =& new SOAP_Value('inputBoolean','boolean',FALSE);
$boolean_one = 1;
$boolean_one_soapval =& new SOAP_Value('inputBoolean','boolean',1);
$boolean_zero = 0;
$boolean_zero_soapval =& new SOAP_Value('inputBoolean','boolean',0);

# XXX I know this isn't quite right, need to deal with this better
function &make_2d($x, $y)
{
    $a = array();
    for ($_x = 0; $_x < $x; $_x++) {
        $a[$_x] = array();
        for ($_y = 0; $_y < $y; $_y++) {
            $a[$_x][$_y] = "x{$_x}y{$_y}";
        }
    }
    return $a;
}

$multidimarray = make_2d(3,3);
$v = array();
$v[0][] =& new SOAP_Value('item','string','row0col0');
$v[0][] =& new SOAP_Value('item','string','row0col1');
$v[0][] =& new SOAP_Value('item','string','row0col2');
$v[1][] =& new SOAP_Value('item','string','row1col0');
$v[1][] =& new SOAP_Value('item','string','row1col1');
$v[1][] =& new SOAP_Value('item','string','row1col2');
$multidimarray_soapval =&
    new SOAP_Value('input2DStringArray','Array',$v);

$multidimarray_soapval->options['flatten'] = TRUE;

// Round2GroupC values
$_person =& new Person(32,12345,'Shane',TRUE);
$person = $_person->__to_soap();
$_employee =& new Employee($_person,12345,1000000.00);
$employee = $_employee->__to_soap();
