Round 2 Interop Test files

Resources:
http://www.soapbuilders.com/
http://www.whitemesa.com/interop.htm
http://www.whitemesa.com/r3/interop3.html
http://www.pocketsoap.com/registration/

Requires an SQL database, schema for MySQL is in database_round2.sql.

run interop_client_run.php to store test results.
view index.php to see test results

To setup an interop server:

1. Web server must alias url /soap_interop/ to the pear/SOAP_Interop 
   directory.
2. index.php should be set for the default document.
3. mySQL should be set up, with a database called interop, schema 
   is in interop_database.sql.
4. interop_client_run.php should not be 
   left under the web root, it is available for manual testing.
