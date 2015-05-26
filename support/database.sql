-- Create database only if it doesn't exists.
CREATE DATABASE "Hoopay" WITH OWNER = postgres ENCODING = 'UTF8';

-- Drop the schema (clear all that was there before) and create a new one.
DROP SCHEMA IF EXISTS Products CASCADE;
CREATE SCHEMA Products AUTHORIZATION postgres;
SET search_path TO Products;

-- Tables and data for merchants (webshops).
CREATE TABLE Merchants
(
	IdMerchant integer NOT NULL,
	Merchant character varying(255) NOT NULL,
	PRIMARY KEY (IdMerchant)
) WITH ( OIDS=FALSE );
ALTER TABLE Products OWNER TO postgres;

INSERT INTO Merchants (IdMerchant,Merchant) VALUES (1,'markavip');
INSERT INTO Merchants (IdMerchant,Merchant) VALUES (2,'namshi');
INSERT INTO Merchants (IdMerchant,Merchant) VALUES (3,'souq');
INSERT INTO Merchants (IdMerchant,Merchant) VALUES (4,'wysada');

-- Tables for the products.
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
	FOREIGN KEY (IdMerchant) REFERENCES Merchants(IdMerchant) ON DELETE SET NULL,
	UNIQUE(URL)
) WITH ( OIDS=FALSE );
ALTER TABLE Products OWNER TO postgres;

