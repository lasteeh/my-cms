CREATE TABLE Cities (
    id INT AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    state VARCHAR(255),
    zip_codes TEXT,
    county_id INT,
    latitude DECIMAL(9, 6),
    longitude DECIMAL(9, 6),
    bound_nw VARCHAR(255),
    bound_se VARCHAR(255),
    viewport_nw VARCHAR(255),
    viewport_se VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (county_id) REFERENCES Counties (id)
);