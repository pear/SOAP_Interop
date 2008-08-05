CREATE TABLE clientinfo (
  id          VARCHAR(40),
  name        VARCHAR(100),
  version     VARCHAR(20),
  resultsURL  VARCHAR(255)
);

CREATE TABLE results (
  id          INTEGER NOT NULL auto_increment,
  client      VARCHAR(100),
  endpoint    INTEGER,
  stamp       INTEGER,
  class       VARCHAR(50),
  type        VARCHAR(10),
  wsdl        INTEGER,
  function    VARCHAR(255),
  result      VARCHAR(25),
  error       TEXT,
  wire        TEXT,
  PRIMARY KEY (id)
);

CREATE TABLE serverinfo (
  id          INTEGER NOT NULL auto_increment,
  service_id  VARCHAR(40),
  name        VARCHAR(100),
  version     VARCHAR(20),
  endpointURL VARCHAR(255),
  wsdlURL     VARCHAR(255),
  PRIMARY KEY (id)
);

CREATE TABLE services (
  id          VARCHAR(40) NOT NULL,
  name        VARCHAR(50),
  description VARCHAR(255),
  wsdlURL     VARCHAR(255),
  websiteURL  VARCHAR(255),
  PRIMARY KEY (id)
);
