<?php
header('Content-Type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<definitions name="SoapInterop" targetNamespace="http://soapinterop.org/" 
		xmlns:wsdlns1="http://soapinterop.org/definitions/" 
		xmlns:wsdlns="http://soapinterop.org/"
		xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" 
		xmlns="http://schemas.xmlsoap.org/wsdl/">
	<import location="imported/import1B.wsdl" namespace="http://soapinterop.org/definitions/" /> 

	<binding name="SoapInteropImport1Binding" type="wsdlns1:SoapInteropImport1PortType">
		<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
		<operation name="echoString">
			<soap:operation soapAction="http://soapinterop.org/"/>
			<input>
				<soap:body use="encoded" namespace="http://soapinterop/echoString/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body use="encoded" namespace="http://soapinterop/echoStringResponse/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
	</binding>
	<service name="Import1">
		<port name="SoapInteropImport1Port" binding="wsdlns:SoapInteropImport1Binding">
			<soap:address location="http://<?php echo $_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"];?>/soap_interop/server_Round3GroupD.php"/>
		</port>
	</service>
</definitions>
