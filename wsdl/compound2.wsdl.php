<?php
header('Content-Type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<definitions name="SoapInteropCompound" targetNamespace="http://soapinterop.org/" 
		xmlns:wsdlns="http://soapinterop.org/" 
		xmlns:emp="http://soapinterop.org/employee" 
		xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" 
		xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
		xmlns="http://schemas.xmlsoap.org/wsdl/">
	<types>
		<schema targetNamespace="http://soapinterop.org/person" 
			xmlns="http://www.w3.org/2001/XMLSchema" 
			elementFormDefault="qualified">
					
			<complexType name="Person">
				<sequence>
					<element minOccurs="1" maxOccurs="1" name="Name" type="string"/>
					<element minOccurs="1" maxOccurs="1" name="Male" type="boolean"/>					
				</sequence>
			</complexType>
		</schema>
		<schema targetNamespace = "http://soapinterop.org/employee" 
			xmlns:prs = "http://soapinterop.org/person"			 
			xmlns="http://www.w3.org/2001/XMLSchema" 
			elementFormDefault="qualified">
                        <import namespace="http://soapinterop.org/person" /> 
			<complexType name="Employee">
				<sequence>
					<element minOccurs="1" maxOccurs="1" name="person" type="prs:Person"/>
					<element minOccurs="1" maxOccurs="1" name="salary" type="double"/>					
					<element minOccurs="1" maxOccurs="1" name="ID" type="int"/>
				</sequence>
			</complexType>
			<element name="x_Employee" type="emp:Employee"/>	
			<element name="result_Employee" type="emp:Employee"/>
		</schema>
	</types>
	<message name="echoEmployee">
		<part name="x" element="emp:x_Employee"/>
	</message>
	<message name="echoEmployeeResponse">
		<part name="result" element="emp:result_Employee"/>
	</message>
	<portType name="SoapInteropCompound2PortType">
		<operation name="echoEmployee" parameterOrder="x">
			<input message="wsdlns:echoEmployee"/>
			<output message="wsdlns:echoEmployeeResponse"/>
		</operation>
	</portType>
	<binding name="SoapInteropCompound2Binding" type="wsdlns:SoapInteropCompound2PortType">
		<soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
		<operation name="echoEmployee">
			<soap:operation soapAction="#echoEmployee"/>
			<input>
				<soap:body use="literal"/>
			</input>
			<output>
				<soap:body use="literal"/>
			</output>
		</operation>		
	</binding>
	<service name="Compound2">
		<port name="SoapInteropCompound2Port" binding="wsdlns:SoapInteropCompound2Binding">
			<soap:address location="http://<?php echo $_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"];?>/soap_interop/server_Round3GroupD.php"/>
		</port>
	</service>
</definitions>
