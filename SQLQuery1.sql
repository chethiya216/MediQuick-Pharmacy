CREATE TABLE	CUSTOMER(
	customer_id 	NVARCHAR(50)  NOT NULL,
	 first_name     NVARCHAR(100)  NOT NULL,
	 last_name      NVARCHAR(100)  NOT NULL,
	 email          NVARCHAR(255)  NOT NULL,
	 phone          NVARCHAR(20)   NULL,
	 address        NVARCHAR(255)  NULL,
	 date_of_birth  DATE         NULL,
	 password_hash  NVARCHAR(255) NOT NULL,
	 status         NVARCHAR(20)  NOT NULL DEFAULT 'active',
	 created_at     DATETIME2     NOT NULL DEFAULT GETDATE(),

	 CONSTRAINT chk_customer_status CHECK (status IN ('active', 'inactive', 'suspended'))
);
	 


	
CREATE TABLE staff (
	staff_id       NVARCHAR(50)  NOT NULL,
	first_name     NVARCHAR(100) NOT NULL,
	last_name      NVARCHAR(100) NOT NULL,
	email          NVARCHAR(255) NOT NULL,
	password_hash  NVARCHAR(255) NOT NULL,
	role           NVARCHAR(50)  NOT NULL,
	status         NVARCHAR(20)  NOT NULL DEFAULT 'active', 
	hire_date      DATE           NOT NULL DEFAULT CAST(GETDATE() AS DATE),
	created_at    DATETIME2     NOT NULL DEFAULT GETDATE(),


    CONSTRAINT chk_staff_status CHECK (status IN ('active', 'inactive', 'terminated'))
);




	