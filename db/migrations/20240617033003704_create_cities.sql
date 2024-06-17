CREATE TABLE Cities (
    id INT AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    state VARCHAR(255) NULL,
    zip_codes TEXT NULL,
    county_id INT NULL,
    latitude DECIMAL(9, 6) NULL,
    longitude DECIMAL(9, 6) NULL,
    bound_nw VARCHAR(255) NULL,
    bound_se VARCHAR(255) NULL,
    viewport_nw VARCHAR(255) NULL,
    viewport_se VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (county_id) REFERENCES Counties (id)
);