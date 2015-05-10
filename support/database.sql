CREATE DATABASE "Hoopay" WITH OWNER = postgres ENCODING = 'UTF8';

DROP SCHEMA IF EXISTS Products CASCADE;
CREATE SCHEMA Products AUTHORIZATION postgres;
SET search_path TO Products;

CREATE TABLE Merchants
(
	IdMerchant serial NOT NULL,
	Merchant character varying(255) NOT NULL,
	PRIMARY KEY (IdMerchant)
) WITH ( OIDS=FALSE );
ALTER TABLE Products OWNER TO postgres;

INSERT INTO Merchants (Merchant) VALUES ('markavip');
INSERT INTO Merchants (Merchant) VALUES ('namshi');
INSERT INTO Merchants (Merchant) VALUES ('souq');
INSERT INTO Merchants (Merchant) VALUES ('sukar');
INSERT INTO Merchants (Merchant) VALUES ('wysada');

CREATE TABLE Products
(
	IdProduct bigserial NOT NULL,
	IdMerchant integer NOT NULL
	Name character varying(255),
	Description text,
	OldPrice character varying(255),
	Price character varying(255),
	URL character varying(1024),
	Image character varying(1024) DEFAULT NULL,
	PRIMARY KEY (IdProduct),
	UNIQUE(URL)
) WITH ( OIDS=FALSE );
ALTER TABLE Products OWNER TO postgres;


./configure --prefix=/usr/local/php5 --with-apxs2=/usr/local/apache2/bin/apxs --enable-pcntl --with-pdo-pgsql --with-gd --with-openssl --enable-sockets --enable-zip --enable-sysvsem 
