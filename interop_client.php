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
require_once 'DB.php'; // PEAR/DB
require_once 'SOAP/Client.php';

require_once 'config.php';
require_once 'interop_test_functions.php';
//require_once 'interop_wrapper.php';
require_once 'params_Round2Base.php';
require_once 'params_Round2GroupB.php';
require_once 'params_Round2GroupC.php';
require_once 'params_Round3GroupD.php';
require_once 'registrationAndNotification.php';

error_reporting(E_ALL ^ E_NOTICE);
$INTEROP_LOCAL_SERVER = false;

class Interop_Client
{
    // database DNS
    var $DSN;

    // our central interop server, where we can get the list of endpoints
    var $registrationDB;
    
    // our local endpoint, will always get added to the database for all tests
    var $localEndpoint;
    
    // specify testing
    var $currentTest = 'Round 2 Base';      // see $tests above
    var $paramType = 'php';     // 'php' or 'soapval'
    var $useWSDL = 0;           // 1= do wsdl tests
    var $numServers = 0;        // 0 = all
    var $specificEndpoint = ''; // test only this endpoint
    var $testMethod = '';       // test only this method
    var $skipEndpointList = array(); // endpoints to skip
    var $nosave = 0;
    var $client_type = 'pear'; //  name of client
    
    // debug output
    var $show = 1;
    var $debug = 0;
    var $showFaults = 0; // used in result table output
    
    // PRIVATE VARIABLES
    var $dbc = NULL;
    var $totals = array();
    var $tests = array('Round 2 Base',
                       'Round 2 Group B', 
                       'Round 2 Group C', 
                       'Round 3 Group D Compound 1',
                       'Round 3 Group D Compound 2',
                       'Round 3 Group D DocLit',
                       'Round 3 Group D DocLitParams',
                       'Round 3 Group D Import 1',
                       'Round 3 Group D Import 2',
                       'Round 3 Group D Import 3',
                       'Round 3 Group D RpcEnc'
            );
    var $paramTypes = array('php', 'soapval');
    var $endpoints = array();
    
    function Interop_Client() {
        global $interopConfig;
        $this->DSN = $interopConfig['DSN'];
        $this->registrationDB = new SOAP_Interop_registrationDB();
        
        // XXX for now, share the database for results also
        $this->dbc = $this->registrationDB->dbc;
    }
    
    /**
    *  fetchEndpoints
    * retreive endpoints interop server
    *
    * @return boolean result
    * @access private
    */    
    function fetchEndpoints($name = 'Round 2 Base') {
        $service = $this->registrationDB->findService($name);
        $this->endpoints = $this->registrationDB->getServerList($service->id,TRUE);
        return TRUE;
    }
    
    /**
    *  getEndpoints
    * retreive endpoints from either database or interop server
    *
    * @param string name (see local var $tests)
    * @param boolean all (if false, only get valid endpoints, status=1)
    * @return boolean result
    * @access private
    */    
    function getEndpoints($name = 'Round 2 Base', $all = 0) {
        $service = $this->registrationDB->findService($name);
        $this->endpoints = $this->registrationDB->getServerList($service->id);
        return TRUE;
    }

    /**
    *  getResults
    * retreive results from the database, stuff them into the endpoint array
    *
    * @access private
    */
    function getResults($test = 'Round 2 Base', $type = 'php', $wsdl = 0) {
        // be sure we have the right endpoints for this test result
        $this->getEndpoints($test);
        
        // retreive the results and put them into the endpoint info
        $sql = "select * from results where class='$test' and type='$type' and wsdl=$wsdl";
        $results = $this->dbc->getAll($sql,NULL, DB_FETCHMODE_ASSOC );
        foreach ($results as $result) {
            // find the endpoint
            $c = count($this->endpoints);
            for ($i=0;$i<$c;$i++) {
                if ($this->endpoints[$i]->id == $result['endpoint']) {
                    // store the info
                    if (!isset($this->endpoints[$i]->methods))
                        $this->endpoints[$i]->methods = array();
                    $this->endpoints[$i]->methods[$result['function']] = $result;
                    break;
                }
            }
        }
    }
    
    /**
    *  saveResults
    * save the results of a method test into the database
    *
    * @access private
    */
    function _saveResults($endpoint_id, &$soap_test) {
        if ($this->nosave) return;
        
        $result =& $soap_test->result;
        $wire =& $result['wire'];
        if ($result['success']) {
            $success = 'OK';
            $error = '';
        } else {
            $success = $result['fault']->faultcode;
            $error = $result['fault']->faultstring;
            if (!$wire) $wire= $result['fault']->faultdetail;
            if (!$wire) $wire= $result['fault']->faultstring;
        }
        
        $test_name = $soap_test->test_name;
        // add header info to the test name
        if ($soap_test->headers) {
            foreach ($soap_test->headers as $h) {
                $destination = 0;
                if (get_class($h) == 'soap_header') {
                    if ($h->attributes['SOAP-ENV:actor'] == 'http://schemas.xmlsoap.org/soap/actor/next') $destination = 1;
                    $test_name .= ":{$h->name},$destination,{$h->attributes['SOAP-ENV:mustUnderstand']}";
                } else {
                    if (!$h[3] || $h[3] == 'http://schemas.xmlsoap.org/soap/actor/next') $destination = 1;
                    if (!$h[2]) $h[2] = 0;
                    $qn = new QName($h[0]);
                    $test_name .= ":{$qn->name},$destination,".(int)$h[2];
                }
            }
        }
        
        $sql = "delete from results where endpoint=$endpoint_id ".
                    "and class='$this->currentTest' and type='$this->paramType' ".
                    "and wsdl=$this->useWSDL and client='$this->client_type' and function=".
                    $this->dbc->quote($test_name);
        #echo "\n".$sql;
        $res = $this->dbc->query($sql);
        if (DB::isError($res)) {
            die ($res->getMessage());
        }
        if (is_object($res)) $res->free();
        
        $sql = "insert into results (client,endpoint,stamp,class,type,wsdl,function,result,error,wire) ".
                    "values('$this->client_type',$endpoint_id,".time().",'$this->currentTest',".
                    "'$this->paramType',$this->useWSDL,".
                    $this->dbc->quote($test_name).",".
                    $this->dbc->quote($success).",".
                    $this->dbc->quote($error).",".
                    ($wire?$this->dbc->quote($wire):"''").")";
        #echo "\n".$sql;
        $res = $this->dbc->query($sql);
        
        if (DB::isError($res)) {
            die ($res->getMessage());
        }
        if (is_object($res)) $res->free();
    }

    /**
    *  compareResult
    * compare two php types for a match
    *
    * @param string expect
    * @param string test_result
    * @return boolean result
    * @access public
    */    
    function compareResult($expect, $result, $type = NULL)
    {
        $ok = 0;
        $expect_type = gettype($expect);
        $result_type = gettype($result);
        if ($expect_type == "array" && $result_type == "array") {
            # compare arrays
            $ok = array_compare($expect, $result);
        } else {
            if ($type == 'float')
                # we'll only compare to 3 digits of precision
                $ok = number_compare($expect, $result);
            if ($type == 'boolean')
                $ok = boolean_compare($expect, $result);
            else
                $ok = string_compare($expect, $result);
        }
        return $ok;
    }


    /**
    *  doEndpointMethod
    *  run a method on an endpoint and store it's results to the database
    *
    * @param array endpoint_info
    * @param SOAP_Test test
    * @return boolean result
    * @access public
    */    
    function doEndpointMethod(&$endpoint_info, &$soap_test) {
        $ok = FALSE;
        
        // prepare a holder for the test results
        $soap_test->result['class'] = $this->currentTest;
        $soap_test->result['type'] = $this->paramType;
        $soap_test->result['wsdl'] = $this->useWSDL;
        $opdata = NULL;
        
        if ($this->useWSDL) {
            if ($endpoint_info->wsdlURL) {
                if (!$endpoint_info->client) {
                    if (0 /* dynamic client */) {
                    $endpoint_info->wsdl = new SOAP_WSDL($endpoint_info->wsdlURL);
                    $endpoint_info->wsdl->trace=1;
                    $endpoint_info->client = $endpoint_info->wsdl->getProxy('',$endpoint_info->name);
                    } else {
                    $endpoint_info->client = new SOAP_Client($endpoint_info->wsdlURL,1);
                    }
                    $endpoint_info->client->_auto_translation = true;
                }
                if ($endpoint_info->client->_wsdl->__isfault()) {
                    $fault = $endpoint_info->client->_wsdl->fault->getFault();
                    $soap_test->setResult(0,'WSDL',
                                            $fault->faultstring."\n\n".$fault->faultdetail,
                                            $fault->faultstring,
                                            $fault
                                            );
                    return FALSE;
                }
                if ($soap_test->service) {
                    $endpoint_info->client->_wsdl->set_service($soap_test->service);
                }
                $soap =& $endpoint_info->client;
                #$port = $soap->_wsdl->getPortName($soap_test->method_name);
                #$opdata = $soap->_wsdl->getOperationData($port, $soap_test->method_name);
            } else {
                $fault = array(
                    'faultcode'=>'WSDL',
                    'faultstring'=>"no WSDL defined for $endpoint");
                $soap_test->setResult(0,'WSDL',
                                      $fault->faultstring,
                                      $fault->faultstring,
                                      $fault
                                      );
                return FALSE;
            }
            $options = array('trace'=>1);
        } else {
            $namespace = $soapaction = 'http://soapinterop.org/';
            // hack to make tests work with MS SoapToolkit
            // it's the only one that uses this soapaction, and breaks if
            // it isn't right.  Can't wait for soapaction to be fully depricated
            # 8/25/2002, seems this is fixed now
            if ($this->currentTest == 'Round 2 Base' &&
                strstr($endpoint_info->name,'MS SOAP ToolKit 2.0')) {
                $soapaction = 'urn:soapinterop';
            }
            if (!$endpoint_info->client) {
                $endpoint_info->client = new SOAP_Client($endpoint_info->endpointURL);
                $endpoint_info->client->_auto_translation = true;
            }
            $soap = &$endpoint_info->client;
            $options = array('namespace'=>$namespace, 
                         'soapaction'=>$soapaction,
                         'trace'=>1);
        }
        
        // add headers to the test
        if ($soap_test->headers) {
            // $header is already a SOAP_Header class
            $soap->headersOut = array();
            $soap->headersIn = array();
            foreach ($soap_test->headers as $header) {
                $soap->addHeader($header);
            }
        }
        $soap->setEncoding($soap_test->encoding);
        

        #if ($opdata) {
        #    if (isset($opdata['style'])) 
        #        $options['style'] = $opdata['style'];
        #    if (isset($opdata['soapAction'])) 
        #        $options['soapaction'] = $opdata['soapAction'];
        #    if (isset($opdata['input']) &&
        #        isset($opdata['input']['use']))
        #        $options['use'] = $opdata['input']['use'];
        #    if (isset($opdata['input']) &&
        #        isset($opdata['input']['namespace']))
        #        $options['namespace'] = $soap->_wsdl->namespaces[$opdata['input']['namespace']];
        #}
        #if ($this->useWSDL) {
        #    $wsdlcall = '$return = $soap->'.$soap_test->method_name.'(';
        #    $args = '';
        #    if ($soap_test->method_params) {
        #    $pnames = array_keys($soap_test->method_params);
        #    foreach ($pnames as $argname) {
        #        if ($args) $args .=',';
        #        $args .= '$soap_test->method_params[\''.$argname.'\']';
        #    }
        #    }
        #    $wsdlcall = $wsdlcall.$args.');';
        #    eval($wsdlcall);
        #} else {
            $return = $soap->call($soap_test->method_name,$soap_test->method_params, $options);
        #}
        
        if(!PEAR::isError($return)){
            if (is_array($soap_test->method_params) && count($soap_test->method_params) == 1) {
                $sent = array_shift(array_values($soap_test->method_params));
            } else {
                $sent = $soap_test->method_params;
            }

            // compare header results
            $header_result = array();
            $headers_ok = TRUE;
            if ($soap_test->headers) {
                // $header is already a SOAP_Header class
                foreach ($soap_test->headers as $header) {
                    if (get_class($header) != 'soap_header') {
                        // assume it's an array
                        $header = new SOAP_Header($header[0], NULL, $header[1], $header[2], $header[3], $header[4]);
                    }
                    $expect = $soap_test->headers_expect[$header->name];
                    $header_result[$header->name] = array();
                    // XXX need to fix need_result to identify the actor correctly
                    $need_result = $hresult ||
                        ($header->attributes['SOAP-ENV:actor'] == 'http://schemas.xmlsoap.org/soap/actor/next'
                         && $header->attributes['SOAP-ENV:mustUnderstand']);
                    if ($expect) {
                        $hresult = $soap->headersIn[key($expect)];
                        $ok = !$need_result || $this->compareResult($hresult ,$expect[key($expect)]);
                    } else {
                        $hresult = $soap->headersIn[$header->name];
                        $expect = $soap->_decode($header);
                        $ok = !$need_result || $this->compareResult($hresult ,$expect);
                    }
                    $header_result[$header->name]['ok'] = $ok;
                    if (!$ok) $headers_ok = FALSE;
                }
            }

            # we need to decode what we sent so we can compare!
            if (gettype($sent)=='object' && (get_class($sent)=='soap_value' ||
                            is_subclass_of($sent,'soap_value')))
                $sent_d = $soap->_decode($sent);
            else
                $sent_d =& $sent;
            
            $soap_test->result['sent'] = $sent;
            $soap_test->result['return'] = $return;
            // compare the results with what we sent
            $ok = $this->compareResult($sent_d,$return, $sent->type);
            if (!$ok && $soap_test->expect) {
                $ok = $this->compareResult($soap_test->expect,$return);
            }
            
            if($ok){
                if (!$headers_ok) {
                    $fault = new stdclass;
                    $fault->faultcode = 'HEADER';
                    $fault->faultstring = 'The returned result did not match what we expected to receive';
                    $soap_test->setResult(0,$fault->faultcode,
                                      $soap->__get_wire(),
                                      $fault->faultstring,
                                      $fault
                                      );
                } else {
                    $soap_test->setResult(1,'OK',$soap->__get_wire());
                    $success = TRUE;
                }
            } else {
                $fault = new stdclass();
                $fault->faultcode = 'RESULT';
                $fault->faultstring = 'The returned result did not match what we expected to receive';
                $fault->faultdetail = ''/*"SENT:\n".var_export($soap_test->result['sent']).
                                               "\n\nRECIEVED:\n".var_export($soap_test->result['return'])*/;
                $soap_test->setResult(0,$fault->faultcode,
                                  $soap->__get_wire(),
                                  $fault->faultstring,
                                  $fault
                                  );
            }
        } else {
            $fault = $return->getFault();
            if ($soap_test->expect_fault) {
                $ok = 1;
                $res = 'OK';
            } else {
                $ok = 0;
                $res =$fault->faultcode;
            }
            $soap_test->setResult($ok,$res, $soap->__get_wire(),$fault->faultstring, $fault);
        }
        return $ok;
    }
    

    /**
    *  doTest
    *  run a single round of tests
    *
    * @access public
    */    
    function doTest() {
        global $soap_tests;
        // get endpoints for this test
        $this->getEndpoints($this->currentTest);
        #clear totals
        $this->totals = array();
        
        $c = count($this->endpoints);
        for ($i=0; $i<$c; $i++) {
            $endpoint_info =& $this->endpoints[$i];
            // if we specify an endpoint, skip until we find it
            if ($this->specificEndpoint && $endpoint_info->name != $this->specificEndpoint) continue;
            if ($this->useWSDL && !$endpoint_info->wsdlURL) continue;
            
            $skipendpoint = FALSE;
            $this->totals['servers']++;
            #$endpoint_info['tests'] = array();
            
            if ($this->show) print "Processing {$endpoint_info->name} at {$endpoint_info->endpointURL}<br>\n";
            
            foreach($soap_tests[$this->currentTest] as $soap_test) {
            //foreach(array_keys($method_params[$this->currentTest][$this->paramType]) as $method)
            
                // only run the type of test we're looking for (php or soapval)
                if ($soap_test->type != $this->paramType) continue;
            
                // if this is in our skip list, skip it
                if (in_array($endpoint_info->name, $this->skipEndpointList)) {
                    $skipendpoint = TRUE;
                    $skipfault = new stdclass;
                    $skipfault->faultcode='SKIP';
                    $skipfault->faultstring='endpoint skipped';
                    $soap_test->setResult(0,$skipfault->faultcode, '',
                                  $skipfault->faultstring,
                                  $skipfault
                                  );
                    #$endpoint_info['tests'][] = &$soap_test;
                    #$soap_test->showTestResult($this->debug);
                    #$this->_saveResults($endpoint_info['id'], $soap_test->method_name);
                    $soap_test->result = NULL;
                    continue;
                }
                
                // if we're looking for a specific method, skip unless we have it
                if ($this->testMethod && !strstr($this->testMethod,$soap_test->test_name)) continue;
                if ($this->testMethod && $this->currentTest == 'Round 2 Group C') {
                    // we have to figure things out now
                    if (!preg_match('/(.*):(.*),(\d),(\d)/',$this->testMethod, $m)) continue;
                    
                    // is the header in the headers list?
                    $gotit = FALSE;
                    $thc = count($soap_test->headers);
                    for ($thi = 0; $thi < $thc; $thi++) {
                        $header =& $soap_test->headers[$thi];
                        if (get_class($header) == 'soap_header') {
                            if ($header->name == $m[2]) {
                                $gotit = $header->attributes['SOAP-ENV:actor'] == ($m[3]?SOAP_TEST_ACTOR_NEXT:SOAP_TEST_ACTOR_OTHER);
                                $gotit = $gotit && $header->attributes['SOAP-ENV:mustUnderstand'] == $m[4];
                            }
                        } else {
                            if ($header[0] == $m[2]) {
                                $gotit = $gotit && $header[3] == ($m[3]?SOAP_TEST_ACTOR_NEXT:SOAP_TEST_ACTOR_OTHER);
                                $gotit = $gotit && $header[4] == $m[4];
                            }
                        }
                    }
                    if (!$gotit) continue;
                }
            
                // if we are skipping the rest of the tests (due to error) note a fault
                if ($skipendpoint) {
                    $soap_test->setResult(0,$skipfault->faultcode, '',
                                  $skipfault->faultstring,
                                  $skipfault
                                  );
                    #$endpoint_info['tests'][] = &$soap_test;
                    $this->totals['fail']++;
                } else {
                    // run the endpoint test
                    unset($soap_test->result);
                    if ($this->doEndpointMethod($endpoint_info, $soap_test)) {
                        $this->totals['success']++;
                    } else {
                        $skipendpoint = $soap_test->result['fault']->faultcode=='HTTP'
                            && strstr($soap_test->result['fault']->faultstring,'Connect Error');
                        if ($skipendpoint) $skipfault = $soap_test->result['fault'];
                        else  $skipfault = NULL;
                        $this->totals['fail']++;
                    }
                    #$endpoint_info['tests'][] = &$soap_test;
                }
                $soap_test->showTestResult($this->debug);
                $this->_saveResults($endpoint_info->id, $soap_test);
                $soap_test->result = NULL;
                $this->totals['calls']++;
            }
            if ($this->numservers && ++$i >= $this->numservers) break;
        }
    }
    
    function doGroupTests() {
        $dowsdl = array(0,1);
        foreach($dowsdl as $usewsdl) {
            $this->useWSDL = $usewsdl;
            foreach($this->paramTypes as $ptype) {
                // skip a pointless test
                if ($usewsdl && $ptype == 'soapval') break;
                if (stristr($this->currentTest, 'Round 3') && !$usewsdl) break;
                $this->paramType = $ptype;
                $this->doTest();
            }
        }
    }
    
    /**
    *  doTests
    *  go all out.  This takes time.
    *
    * @access public
    */    
    function doTests() {
        // the mother of all interop tests
        $dowsdl = array(0,1);
        foreach($this->tests as $test) {
            $this->currentTest = $test;
            foreach($dowsdl as $usewsdl) {
                $this->useWSDL = $usewsdl;
                foreach($this->paramTypes as $ptype) {
                    // skip a pointless test
                    if ($usewsdl && $ptype == 'soapval') break;
                    if (stristr($this->currentTest, 'Round 3') && !$usewsdl) break;
                    $this->paramType = $ptype;
                    $this->doTest();
                }
            }
        }
    }
    
    // ***********************************************************
    // output functions
    
    /**
    *  getResults
    * retreive results from the database, stuff them into the endpoint array
    *
    * @access private
    */
    function getMethodList($test = 'base') {
        $this->dbc->setFetchMode(DB_FETCHMODE_ORDERED);
        // retreive the results and put them into the endpoint info
        $sql = "select distinct(function) from results where client='$this->client_type' and class='$test' order by function";
        $results = $this->dbc->getAll($sql);
        $ar = array();
        foreach($results as $result) {
            $ar[] = $result[0];
        }
        return $ar;
    }
    
    function outputTable()
    {
        $methods = $this->getMethodList($this->currentTest);
        if (!$methods) return;
        $this->getResults($this->currentTest,$this->paramType,$this->useWSDL);
        
        echo "<b>Testing $this->currentTest ";
        if ($this->useWSDL) echo "using WSDL ";
        else echo "using Direct calls ";
        echo "with $this->paramType values</b><br>\n";
        
        // calculate totals for this table
        $this->totals['success'] = 0;
        $this->totals['fail'] = 0;
        $this->totals['result'] = 0;
        $this->totals['wsdl'] = 0;
        $this->totals['connect'] = 0;
        $this->totals['servers'] = 0; #count($this->endpoints);
        $c = count ($this->endpoints);
        for ($i=0;$i<$c;$i++) {
            $endpoint_info =& $this->endpoints[$i];
            if (!$endpoint_info->name) continue;
            if (count($endpoint_info->methods) > 0) {
                $this->totals['servers']++;
                foreach ($methods as $method) {
                    $r = $endpoint_info->methods[$method]['result'];
                    if ($r == 'OK') $this->totals['success']++;
                    else if (stristr($r,'result')) $this->totals['result']++;
                    else if (stristr($r,'wsdlcache')) $this->totals['connect']++;
                    else if (stristr($r,'wsdl')) $this->totals['wsdl']++;
                    else if (stristr($r,'http')) $this->totals['connect']++;
                    else $this->totals['fail']++;
                }
            } else {
                //unset($this->endpoints[$i]);
            }
        }
        $this->totals['calls'] = count($methods) * $this->totals['servers'];

        #if ($this->totals['fail'] == $this->totals['calls']) {
        #    // assume tests have not run, skip outputing table
        #    print "No Data Available<br>\n";
        #    return;
        #}
        
        echo "\n\n<b>Servers: {$this->totals['servers']} Calls: {$this->totals['calls']} ".
            "Success: {$this->totals['success']} <br>\n".
            "System-Fail: {$this->totals['fail']} Result-Failure: {$this->totals['result']} ".
            "Connect-Failure: {$this->totals['connect']} WSDL-Failure: {$this->totals['wsdl']} </b><br>\n";
       
        echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"2\">\n";
        echo "<tr><td class=\"BLANK\">Endpoint</td>\n";
        foreach ($methods as $method) {
            $info = split(':', $method);
            echo "<td class='BLANK' valign='top'>";
            foreach ($info as $m) {
                $hi = split(',',$m);
                echo '<b>'.$hi[0]."</b><br>\n";
                if (count($hi) > 1) {
                    echo "&nbsp;&nbsp;Actor=".($hi[1]?'Target':'Not Target')."<br>\n";
                    echo "&nbsp;&nbsp;MustUnderstand=$hi[2]<br>\n";
                }
            }
            echo "</td>\n";
        }
        echo "</tr>\n";
        $faults = array();
        $fi = 0;
        $c = count ($this->endpoints);
        for ($i=0;$i<$c;$i++) {
            $endpoint_info =& $this->endpoints[$i];
            if (!$endpoint_info->name) continue;
            if ($endpoint_info->wsdlURL) {
                echo "<tr><td class=\"BLANK\"><a href=\"{$endpoint_info->wsdlURL}\">{$endpoint_info->name}</a></td>\n";
            } else {
                echo "<tr><td class=\"BLANK\">{$endpoint_info->name}</td>\n";
            }
            foreach ($methods as $method) {
                $id = $endpoint_info->methods[$method]['id'];
                $r = $endpoint_info->methods[$method]['result'];
                $e = $endpoint_info->methods[$method]['error'];
                if ($e) {
                    $faults[$fi++] = $e;
                }
                if ($r) {
                    echo "<td class='$r'><a href='$PHP_SELF?wire=$id'>$r</a></td>\n";
                } else {
                    echo "<td class='untested'>untested</td>\n";
                }
            }
            echo "</tr>\n";
        }
        echo "</table><br>\n";
        if ($this->showFaults && count($faults) > 0) {
            echo "<b>ERROR Details:</b><br>\n<ul>\n";
            # output more error detail
            foreach ($faults as $fault) {
                echo '<li>'.HTMLSpecialChars($fault)."</li>\n";
            }
        }
        echo "</ul><br><br>\n";
    }
    
    function outputTables() {
        // the mother of all interop tests
        $dowsdl = array(0,1);
        foreach($this->tests as $test) {
            $this->currentTest = $test;
            foreach($dowsdl as $usewsdl) {
                $this->useWSDL = $usewsdl;
                foreach($this->paramTypes as $ptype) {
                    // skip a pointless test
                    if ($usewsdl && $ptype == 'soapval') break;
                    if (stristr($this->currentTest, 'Round 3') && !$usewsdl) break;
                    $this->paramType = $ptype;
                    $this->outputTable();
                }
            }
        }
    }
    
    function showWire($id) {
        $results = $this->dbc->getAll("select * from results where id=$id",NULL, DB_FETCHMODE_ASSOC );
        #$wire = preg_replace("/>/",">\n",$results[0]['wire']);
        $wire = $results[0]['wire'];
        echo "<pre>\n".HTMLSpecialChars($wire)."</pre>\n";
    }

}

?>