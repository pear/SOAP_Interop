<?php
// this script is usefull for quickly testing stuff, use the 'pretty' file for html output
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

if (isset($_SERVER['SERVER_NAME'])) {
    die("full test run cannot be done via webserver.");
}

set_time_limit(0);
error_reporting(E_ALL);

require 'Console/Getopt.php';
require_once 'interop_client.php';

#$SOAP_RAW_CONVERT = TRUE;
$INTEROP_LOCAL_SERVER = TRUE;// add local server to endpoints

$iop =& new Interop_Client();

// set some defaults
$iop->client_type='pear'; // 'pear' or 'php-soap'
$iop->currentTest = '';      // see $tests above
$iop->paramType = 'php';     // 'php' or 'soapval'
$iop->useWSDL = 1;           // 1= do wsdl tests
$iop->numServers = 0;        // 0 = all
$iop->specificEndpoint = ''; // test only this endpoint
$iop->testMethod = '';       // test only this method
$iop->skipEndpointList = array();#array('Frontier','CapeConnect','Apache Axis','Apache SOAP 2.2'); // endpoints to skip
$iop->nosave = 0; // 1= disable saving results to database
// debug output
$iop->show = 1;
$iop->debug = 0;
$iop->showFaults = 0; // used in result table output
$restrict = NULL;

$args = Console_Getopt::getopt($_SERVER['argv'], 'c:dehl:m:np:r:s:t:q');

function help() {
print <<<END
interop_client_run.php [options]
    -c pear|php-soap        client type (not implmented yet)
    -d                      turn on debug output
    -e                      fetch interop test information
    -h                      this help
    -l list                 comma seperated list of endpoint names to skip
    -m method_name          specific soap method to test
    -n                      do not save results to database
    -p t|e                  print list of [t]ests or [e]ndpoints
    -r string               restrict tests to those whose name starts with...
    -s server_name          test a specific server
    -t test_name            run a specific set of tests
    -q                      do not run tests
END;
}

function print_test_names()
{
    global $iop;
    print "Interop tests supported:\n";
    foreach ($iop->tests as $test) {
        print "  $test\n";
    }
}

function print_endpoint_names()
{
    global $iop;
    if (!$iop->getEndpoints($iop->currentTest)) {
        die("Unable to retrieve endpoints for $iop->currentTest");
    }
    print "Interop Servers for $iop->currentTest:\n";
    foreach ($iop->endpoints as $server) {
        print "  $server->name\n";
    }
}

foreach($args[0] as $arg) {
    switch($arg[0]) {
    case 'c':
        $iop->client_type = $arg[1];
        break;
    case 'd':
        $iop->debug = true;
        break;
    case 'e':
        $iop->fetchEndpoints();
        break;
    case 'h':
        help();
        exit(1);
        break;
    case 'l':
        $iop->skipEndpointList = split(',',$arg[1]);
        break;
    case 'm':
        $iop->testMethod = $arg[1];
        break;
    case 'n':
        $iop->nosave = true;
        break;
    case 'p':
        if ($arg[1] == 't') {
            print_test_names();
        } else if ($arg[1] == 'e') {
            if (!$iop->currentTest) {
                print "You need to specify a test with -t";
                exit(0);
            }
            print_endpoint_names();
        } else {
            die("invalid print argument");
        }
        exit(0);
        break;
    case 'r':
        $restrict = $arg[1];
        break;
    case 's':
        $iop->specificEndpoint = $arg[1];
        break;
    case 't':
        $iop->currentTest = $arg[1];
        break;
    case 'q':
        exit(0);
    }
}

// these are endpoints that are listed in the interop
// server, but do not realy exist any longer
$bad = array('Spheon JSOAP','Phalanx',
             'SilverStream','SOAPx4 (PHP)',
             'Virtuoso (development)',
             'Zolera SOAP Infrastructure');
$iop->skipEndpointList = array_merge($iop->skipEndpointList, $bad);

if ($restrict) {
    $tests = $iop->tests;
    $iop->tests = array();
    foreach ($tests as $test) {
        if (stristr($test, $restrict)) {
            $iop->tests[] = $test;
        }
    }
}

if ($iop->currentTest) {
    $iop->doTest();  // run a single set of tests using above options
} else {
#$iop->doGroupTests(); // run a group of tests set in $currentTest
    $iop->doTests();  // run all tests, ignore above options
}
echo "done";
?>
