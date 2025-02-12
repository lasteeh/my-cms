CREATE TABLE Leads (
    id INT AUTO_INCREMENT,
    vortex_id VARCHAR(255) NOT NULL UNIQUE,
    import_lead BOOLEAN DEFAULT TRUE,
    lead_assigned BOOLEAN DEFAULT FALSE,
    listing_status VARCHAR(255) NULL,
    name VARCHAR(255) NULL,
    name_2 VARCHAR(255) NULL,
    name_3 VARCHAR(255) NULL,
    name_4 VARCHAR(255) NULL,
    name_5 VARCHAR(255) NULL,
    name_6 VARCHAR(255) NULL,
    name_7 VARCHAR(255) NULL,
    mls_name VARCHAR(255) NULL,
    mls_name_2 VARCHAR(255) NULL,
    mls_name_3 VARCHAR(255) NULL,
    mls_name_4 VARCHAR(255) NULL,
    mls_name_5 VARCHAR(255) NULL,
    mls_name_6 VARCHAR(255) NULL,
    mls_name_7 VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    phone_status VARCHAR(255) NULL,
    phone_2 VARCHAR(20) NULL,
    phone_2_status VARCHAR(255) NULL,
    phone_3 VARCHAR(20) NULL,
    phone_3_status VARCHAR(255) NULL,
    phone_4 VARCHAR(20) NULL,
    phone_4_status VARCHAR(255) NULL,
    phone_5 VARCHAR(20) NULL,
    phone_5_status VARCHAR(255) NULL,
    phone_6 VARCHAR(20) NULL,
    phone_6_status VARCHAR(255) NULL,
    phone_7 VARCHAR(20) NULL,
    phone_7_status VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    email_2 VARCHAR(255) NULL,
    email_3 VARCHAR(255) NULL,
    email_4 VARCHAR(255) NULL,
    email_5 VARCHAR(255) NULL,
    email_6 VARCHAR(255) NULL,
    email_7 VARCHAR(255) NULL,
    address TEXT NULL,
    address_2 TEXT NULL,
    address_3 TEXT NULL,
    address_4 TEXT NULL,
    address_5 TEXT NULL,
    address_6 TEXT NULL,
    address_7 TEXT NULL,
    first_name VARCHAR(255) NULL,
    last_name VARCHAR(255) NULL,
    mailing_street VARCHAR(255) NULL,
    mailing_city VARCHAR(255) NULL,
    mailing_state VARCHAR(255) NULL,
    mailing_zip VARCHAR(20) NULL,
    list_date DATE NULL,
    list_price DECIMAL(20, 2) NULL,
    days_on_market INT NULL,
    lead_date DATE NULL,
    expired_date DATE NULL,
    withdrawn_date DATE NULL,
    status_date DATE NULL,
    listing_agent VARCHAR(255) NULL,
    listing_broker VARCHAR(255) NULL,
    mls_fsbo_id VARCHAR(255) NULL,
    standardized_mailing_street VARCHAR(255) NULL,
    absentee_owner BOOLEAN DEFAULT FALSE,
    standardized_property_street VARCHAR(255) NULL,
    property_address VARCHAR(255) NULL,
    property_city VARCHAR(255) NULL,
    property_state VARCHAR(255) NULL,
    property_zip VARCHAR(20) NULL,
    property_county INT NULL,
    assigned_area VARCHAR(255) NULL,
    source VARCHAR(255) NULL,
    pipeline VARCHAR(255) NULL,
    buyer_seller VARCHAR(255) NULL,
    agent_assigned VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (property_county) REFERENCES Counties(id)
);