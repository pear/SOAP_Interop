<?php
require_once 'DB.php'; // PEAR/DB
require_once 'SOAP/Client.php';

class clientInfo {
    var $name;
    var $version;
    var $resultsURL;

    function clientInfo($ar=NULL) {
        if (is_array($ar)) {
            foreach ($ar as $k=>$v) {
                $this->$k = $v;
            }
        }
    }
}

class serverInfo {
    var $id;
    var $service_id;
    var $name;
    var $version;
    var $endpointURL;
    var $wsdlURL;

    function serverInfo($ar=NULL) {
        if (is_array($ar)) {
            foreach ($ar as $k=>$v) {
                $this->$k = $v;
            }
        }
    }
}

class Service {
    var $id;
    var $name;
    var $description;
    var $wsdlURL;
    var $websiteURL;

    function Service($ar=NULL) {
        if (is_array($ar)) {
            foreach ($ar as $k=>$v) {
                $this->$k = $v;
            }
        }
    }
}

class subscriberInfo {
    var $notificationID;
    var $expires; /* dateTime */
}

class ChangeItem {
    var $id;
    var $timestamp; /* dateTime */
    var $headline;
    var $notes;
    var $url;
}

class SOAP_Interop_registrationAndNotificationService_ServicesPort extends SOAP_Client {
    function SOAP_Interop_registrationAndNotificationService_ServicesPort() {
        $this->SOAP_Client("http://soap.4s4c.com/registration/soap.asp", 0);
        $this->_auto_translation = true;
    }
    function ServiceList() {
        return $this->call("ServiceList", 
                        NULL, 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/services',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/services#ServiceList',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function Servers($serviceID) {
        return $this->call("Servers", 
                        array("serviceID"=>$serviceID), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/services',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/services#Servers',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function Clients($serviceID) {
        return $this->call("Clients", 
                        array("serviceID"=>$serviceID), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/services',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/services#Clients',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
}
class SOAP_Interop_registrationAndNotificationService_ClientsPort extends SOAP_Client {
    function SOAP_Interop_registrationAndNotificationService_ClientsPort() {
        $this->SOAP_Client("http://soap.4s4c.com/registration/soap.asp", 0);
        $this->_auto_translation = true;
    }
    function RegisterClient($serviceID, $clientInfo) {
        return $this->call("RegisterClient", 
                        array("serviceID"=>$serviceID, "clientInfo"=>$clientInfo), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/clients',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/clients#RegisterClient',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function UpdateClient($clientID, $clientInfo) {
        return $this->call("UpdateClient", 
                        array("clientID"=>$clientID, "clientInfo"=>$clientInfo), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/clients',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/clients#UpdateClient',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function RemoveClient($clientID) {
        return $this->call("RemoveClient", 
                        array("clientID"=>$clientID), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/clients',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/clients#RemoveClient',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
}
class SOAP_Interop_registrationAndNotificationService_ServersPort extends SOAP_Client {
    function SOAP_Interop_registrationAndNotificationService_ServersPort() {
        $this->SOAP_Client("http://soap.4s4c.com/registration/soap.asp", 0);
        $this->_auto_translation = true;
    }
    function RegisterServer($serviceID, $serverInfo) {
        return $this->call("RegisterServer", 
                        array("serviceID"=>$serviceID, "serverInfo"=>$serverInfo), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/servers',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/servers#RegisterServer',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function UpdateServer($serverID, $serverInfo) {
        return $this->call("UpdateServer", 
                        array("serverID"=>$serverID, "serverInfo"=>$serverInfo), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/servers',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/servers#UpdateServer',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function RemoveServer($serverID) {
        return $this->call("RemoveServer", 
                        array("serverID"=>$serverID), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/servers',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/servers#RemoveServer',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
}
class SOAP_Interop_registrationAndNotificationService_SubscriberPort extends SOAP_Client {
    function SOAP_Interop_registrationAndNotificationService_SubscriberPort() {
        $this->SOAP_Client("http://soap.4s4c.com/registration/soap.asp", 0);
        $this->_auto_translation = true;
    }
    function Subscribe($serviceID, $ServerChanges, $ClientChanges, $NotificationURL) {
        return $this->call("Subscribe", 
                        array("serviceID"=>$serviceID, "ServerChanges"=>$ServerChanges, "ClientChanges"=>$ClientChanges, "NotificationURL"=>$NotificationURL), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/subscriber',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/subscriber#Subscribe',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function Renew($notificationID) {
        return $this->call("Renew", 
                        array("notificationID"=>$notificationID), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/subscriber',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/subscriber#Renew',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
    function Cancel($notificationID) {
        return $this->call("Cancel", 
                        array("notificationID"=>$notificationID), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/subscriber',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/subscriber#Cancel',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
}
class SOAP_Interop_registrationAndNotificationService_ChangeLogPort extends SOAP_Client {
    function SOAP_Interop_registrationAndNotificationService_ChangeLogPort() {
        $this->SOAP_Client("http://soap.4s4c.com/registration/soap.asp", 0);
        $this->_auto_translation = true;
    }
    function RecentChanges($MaxEntries) {
        return $this->call("RecentChanges", 
                        array("MaxEntries"=>$MaxEntries), 
                        array('namespace'=>'http://soap.pocketsoap.com/registration/changeLog',
                            'soapaction'=>'http://soap.pocketsoap.com/registration/changeLog#RecentChanges',
                            'style'=>'rpc',
                            'use'=>'encoded')); 
    }
}

class SOAP_Interop_registrationDB {
    var $DSN = 'mysql://user@localhost/soapinterop';
    var $dbc = NULL;
    
    var $client; // soap_client
    var $services;
    var $currentServiceId;
    var $servers;
    var $clients;
    
    function SOAP_Interop_registrationDB()
    {
        $this->client = new SOAP_Interop_registrationAndNotificationService_ServicesPort();
        $this->connectDB();
        $this->getServiceList();
    }
    
    function connectDB()
    {
        if (!$this->dbc)
            $this->dbc = DB::connect($this->DSN, true);
        if (PEAR::isError($this->dbc)) {
            echo $this->dbc->getMessage();
            $this->dbc = NULL;
            return false;
        }
        return true;
    }
    
    function updateDB()
    {
        $this->updateServiceDb();
        $this->updateServerDb();
        $this->updateClientsDb();
    }
    
    function &retreiveServiceList()
    {
        if (!$this->services) {
            $this->services = $this->client->ServiceList();
        }
        return $this->services;
    }
    
    function &retreiveServerList($serviceID)
    {
        if (!$this->servers || $this->currentServiceId != $serviceID) {
            $this->currentServiceId = $serviceID;
            $this->servers = $this->client->Servers($serviceID);
        }
        return $this->servers;
    }
    
    function &retreiveClientList($serviceID)
    {
        if (!$this->clients || $this->currentServiceId != $serviceID) {
            $this->currentServiceId = $serviceID;
            $this->clients = $this->client->Clients($serviceID);
        }
        return $this->clients;
    }
    
    function updateServiceDb()
    {
        if (!$this->connectDB()) return false;
        $this->retreiveServiceList();
        echo "Updating Services<br>\n";
        foreach ($this->services as $service) {
            $res = $this->dbc->getRow("select id from services where id='{$service->id}'");
            if ($res && !PEAR::isError($res)) {
                $res = $this->dbc->query("update services set name='{$service->name}',".
                                         "description='{$service->description}',wsdlURL='{$service->wsdlURL}',".
                                         "websiteURL='{$service->websiteURL}' where id='{$service->id}'");
            } else {
                $res = $this->dbc->query("insert into services (id,name,description,wsdlURL,websiteURL) ".
                                         "values('{$service->id}','{$service->name}','{$service->description}','{$service->wsdlURL}','{$service->websiteURL}')");
            }
            
        }
    }

    function updateServerDb()
    {
        if (!$this->connectDB()) return false;
        $this->retreiveServiceList();
        $c = count($this->services);
        for ($i=0;$i<$c;$i++) {
            $this->retreiveServerList($this->services[$i]->id);
            echo "Updating Servers for {$this->services[$i]->name}<br>\n";
            if (!$this->servers) continue;
            foreach ($this->servers as $server) {
                $res = $this->dbc->getRow("select id from serverinfo where service_id='{$this->services[$i]->id}' and name='{$server->name}'");
                if ($res && !PEAR::isError($res)) {
                    $res = $this->dbc->query("update serverinfo set ".
                                             "version='{$server->version}', ".
                                             "endpointURL='{$server->endpointURL}', ".
                                             "wsdlURL='{$server->wsdlURL}' where id={$res['id']}");
                } else {
                    $res = $this->dbc->query("insert into serverinfo (service_id,name,version,endpointURL,wsdlURL) ".
                                             "values('{$this->services[$i]->id}','{$server->name}','{$server->version}','{$server->endpointURL}','{$server->wsdlURL}')");
                }
                
            }
        }
    }    

    function updateClientsDb()
    {
        if (!$this->connectDB()) return false;
        $this->retreiveServiceList();
        foreach ($this->services as $service) {
            $this->retreiveClientList($service->id);
            echo "Updating Clients for {$service->name}<br>\n";
            if (!$this->clients) continue;
            foreach ($this->clients as $client) {
                $res = $this->dbc->getRow("select id from clientinfo where id='{$service->id}' and name='{$client->name}'");
                if ($res && !PEAR::isError($res)) {
                    $res = $this->dbc->query("update clientinfo set ".
                                             "version='{$client->version}', ".
                                             "resultsURL='{$client->resultsURL}' where ".
                                             "id='{$service->id}',name='{$client->name}'");
                } else {
                    $res = $this->dbc->query("insert into clientinfo (id,name,version,resultsURL) ".
                                             "values('{$service->id}','{$client->name}','{$client->version}','{$client->resultsURL}')");
                }
                
            }
        }
    }
    
    function &getServiceList($forcefetch=FALSE)
    {
        if (!$forcefetch && !$this->services) {
            $this->dbc->setFetchMode(DB_FETCHMODE_OBJECT,'Service');
            $this->services = $this->dbc->getAll('select * from services',NULL, DB_FETCHMODE_OBJECT );
        }
        if ($forcefetch || !$this->services) {
            $this->updateServiceDb();
        }
        return $this->services;
    }
    
    function &getServerList($serviceID,$forcefetch=FALSE)
    {
        if (!$forcefetch && (!$this->servers || $this->currentServiceId != $serviceID)) {
            $this->dbc->setFetchMode(DB_FETCHMODE_OBJECT,'serverInfo');
            $this->servers = $this->dbc->getAll("select * from serverinfo where service_id = '$serviceID'",NULL, DB_FETCHMODE_OBJECT );
        }
        if ($forcefetch || !$this->servers) {
            $this->updateServerDb();
            return $this->getServerList($serviceID);
        }
        return $this->servers;        
    }    

    function &getClientList($serviceID,$forcefetch=FALSE)
    {
        if (!$forcefetch && (!$this->clients || $this->currentServiceId != $serviceID)) {
            $this->dbc->setFetchMode(DB_FETCHMODE_OBJECT,'clientInfo');
            $this->clients = $this->dbc->getAll("select * from clientinfo where id = '$serviceID'",NULL, DB_FETCHMODE_OBJECT );
        }
        if ($forcefetch || !$this->clients) {
            $this->updateClientDb();
            return $this->getClientList($servers);
        }
        return $this->clients;        
    }
    
    function &findService($serviceName)
    {
        $this->getServiceList();
        $c = count($this->services);
        for ($i=0 ; $i<$c; $i++) {
            if (strcmp($serviceName, $this->services[$i]->name)==0) return $this->services[$i];
        }
        return NULL;
    }
}

#$reg = new SOAP_Interop_registrationAndNotificationDB();
#$reg->updateDB();
//print_r($l);
?>