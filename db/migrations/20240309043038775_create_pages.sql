CREATE TABLE Pages (
    id INT AUTO_INCREMENT,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL UNIQUE,
    sub_title VARCHAR(255),
    description VARCHAR(255),
    custom_css TEXT,
    custom_js TEXT,
    content TEXT,
    parent_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES Pages(id)
);