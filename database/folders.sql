use FileSheild;
CREATE TABLE folders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,            -- Matches users.id exactly
    folder_name VARCHAR(255) NOT NULL,
    parent_id INT UNSIGNED DEFAULT NULL,  -- Matches this table's id type
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Performance Indexes
    INDEX idx_user_folder (user_id, parent_id),

    -- Relationships
    CONSTRAINT fk_folder_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_folder_parent FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
) ENGINE=InnoDB;


ALTER TABLE folders 
ADD COLUMN share_token VARCHAR(64) UNIQUE;