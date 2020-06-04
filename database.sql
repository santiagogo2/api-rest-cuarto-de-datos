CREATE DATABASE IF NOT EXISTS api_rest_documents;
USE api_rest_documents;

CREATE TABLE users(
id 				int(255) auto_increment NOT NULL,
name 			varchar(100) NOT NULL,
surname 		varchar(100) NOT NULL,
role 			varchar(20) NOT NULL,
user_alias 		varchar(100) NOT NULL,
password		varchar(255) NOT NULL,
created_at 		datetime DEFAULT NULL,
updated_at 		datetime DEFAULT NULL,
remember_token 		varchar(255),
CONSTRAINT pk_users PRIMARY KEY(id)
) ENGINE=InnoDb;

CREATE TABLE documents(
id 				int(255) auto_increment NOT NULL,
name 			varchar(255) NOT NULL,
document_name	varchar(255) NOT NULL,
user_id 		int(255) NOT NULL,
folder_id		int(255) NOT NULL,
created_at 		datetime NOT NULL,
updated_at 		datetime NOT NULL, 
CONSTRAINT pk_documents PRIMARY KEY(id),
CONSTRAINT fk_documents_user FOREIGN KEY(user_id) REFERENCES users(id),
CONSTRAINT fk_folder FOREIGN KEY(folder_id) REFERENCES folder(id)
) ENGINE=InnoDb; 

CREATE TABLE income_record(
id 				int(255) auto_increment NOT NULL,
user 			varchar(255) NOT NULL,
document_name	varchar(255),
created_at 		datetime NOT NULL,
updated_at 		datetime NOT NULL, 
CONSTRAINT pk_income_record PRIMARY KEY(id)
) ENGINE=InnoDb; 

CREATE TABLE folder(
id 				int(255) auto_increment NOT NULL,
name 			varchar(255) NOT NULL,
created_at 		datetime NOT NULL,
updated_at 		datetime NOT NULL, 
CONSTRAINT pk_folder PRIMARY KEY(id)
) ENGINE=InnoDb; 