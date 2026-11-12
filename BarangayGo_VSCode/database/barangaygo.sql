CREATE DATABASE barangaygo;
USE barangaygo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    address TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('resident', 'admin') DEFAULT 'resident',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_no VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    purpose VARCHAR(150) NOT NULL,
    details TEXT,
    id_file VARCHAR(255) NOT NULL,
    status ENUM(
        'Pending',
        'Under Review',
        'Approved',
        'Rejected',
        'Ready for Pickup',
        'Completed'
    ) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_id INT,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (request_id) REFERENCES requests(id)
);
