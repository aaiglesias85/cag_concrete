
 CREATE TABLE project_item_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_item_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    old_value VARCHAR(255) NULL,
    new_value VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    user_id INT NULL,
    FOREIGN KEY (project_item_id) REFERENCES project_item(id),
    FOREIGN KEY (user_id) REFERENCES user(user_id)
);