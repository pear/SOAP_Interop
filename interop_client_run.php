<?php
// this script is usefull for quickly testing stuff, use the 'pretty' file for html output
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
set_time_limit(0);
require_once 'interop_client.php';
#$SOAP_RAW_CONVERT = TRUE;
$INTEROP_LOCAL_SERVER = TRUE;// add local server to endpoints

$iop = new Interop_Client();
// force a fetch of endpoints, this happens irregardless if no endpoints in database
#$iop->fetchEndpoints();

// set some options
$iop->client_type='pear'; // 'pear' or 'php-soap'
$iop->currentTest = 'Round 2 Group C';      // see $tests above
$iop->paramType = 'php';     // 'php' or 'soapval'
$iop->useWSDL = 1;           // 1= do wsdl tests
$iop->numServers = 0;        // 0 = all
$iop->specificEndpoint = ''; // test only this endpoint
$iop->testMethod = '';       // test only this method
$iop->skipEndpointList = array('Frontier'); // endpoints to skip
$this->nosave = 0; // 1= disable saving results to database
// debug output
$iop->show = 1;
$iop->debug = 0;
$iop->showFaults = 0; // used in result table output

// these are endpoints that are listed in the interop
// server, but do not realy exist any longer
$bad = array('Spheon JSOAP','Phalanx',
             'SilverStream','SOAPx4 (PHP)',
             'Virtuoso (development)',
             'Zolera SOAP Infrastructure');
$iop->skipEndpointList = array_merge($iop->skipEndpointList, $bad);


#$iop->tests = array(/*'Round 2 Base',
#                       'Round 2 Group B', 
#                       'Round 2 Group C', */
#                       'Round 3 Group D Compound 1',
#                       'Round 3 Group D Compound 2',
#                       'Round 3 Group D DocLit',
#                       'Round 3 Group D DocLitParams',
#                       'Round 3 Group D Import 1',
#                       'Round 3 Group D Import 2',
#                       'Round 3 Group D Import 3',
#                       'Round 3 Group D RpcEnc'
#            );
#$iop->doTest();  // run a single set of tests using above options
#$iop->doGroupTests(); // run a group of tests set in $currentTest
$iop->doTests();  // run all tests, ignore above options
#$iop->outputTables();
echo "done";

?>
