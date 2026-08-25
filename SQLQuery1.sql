CREATE TABLE	CUSTOMER(
	 customer_id    NVARCHAR PRIMARY KEY,
	 first_name     NVARCHAR(100) NOT NULL,
	 last_name      NVARCHAR(100) NOT NULL,
	 email          NVARCHAR(255) NOT NULL,
	 phone          NVARCHAR(20),
	 adress         NVARCHAR(255),
	 date_of_birth  DATE,
	 password_hash  NVARCHAR(255) NOT NULL,
	 status         NVARCHAR(20)  NOT NULL DEFAULT 'active',
	 );


	
CREATE TABLE staff (
	staff_id       NVARCHAR PRIMARY KEY,
	first_name     NVARCHAR(100) NOT NULL,
	last_name      NVARCHAR(100) NOT NULL,
	email          NVARCHAR(255) NOT NULL,
	password_hash  NVARCHAR(255) NOT NULL,
	role           NVARCHAR(50)  NOT NULL,
	status         NVARCHAR(20)  NOT NULL DEFAULT 'active', 
	hire_date DATE NOT NULL DEFAULT GETDATE(),




	);