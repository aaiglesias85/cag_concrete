INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('24', 'project_stage', 'Project Stages');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '24');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '24');

CREATE TABLE project_stage
(
    stage_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    color VARCHAR(50),
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

INSERT INTO `project_stage` (`stage_id`, `description`, `color`, `status`) VALUES
(1, 'Undecided', '#ADB5CA', 1),
(2, 'Accepted', '#34BFA3', 1),
(3, 'Material Quotes', '#9816F4', 1),
(4, 'Estimating', '#0EEB2B', 1),
(5, 'Curtis Review', '#FFB822', 1);


INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('25', 'project_type', 'Project Type');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '25');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '25');

CREATE TABLE project_type
(
    type_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

INSERT INTO `project_type` (`type_id`, `description`, `status`) VALUES
(1, 'Quick Response', 1),
(2, 'Traffic Signal', 1),
(3, 'Turnkey', 1),
(4, 'Roundabout', 1),
(5, 'Bridge & Approaches', 1);


INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('26', 'proposal_type', 'Proposal Type');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '26');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '26');

CREATE TABLE proposal_type
(
    type_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

INSERT INTO `proposal_type` (`type_id`, `description`, `status`) VALUES
(1, 'Bid', 1),
(2, 'Letting', 1),
(3, 'Add-On', 1),
(4, 'Re-Bid', 1),
(5, 'Prime', 1),
(6, 'Quick Response', 1);



INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('27', 'plan_status', 'Plan Status');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '27');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '27');

CREATE TABLE plan_status
(
    status_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;

INSERT INTO `plan_status` (`status_id`, `description`, `status`) VALUES
(1, 'Downloaded', 1),
(2, 'New Addenda', 1),
(3, 'No Plans', 1),
(4, 'Log Only', 1);


INSERT INTO `function` (`function_id`, `url`, `description`) VALUES ('28', 'district', 'District');

INSERT INTO `rol_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `rol_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '28');

INSERT INTO `user_permission` (`view_permission`, `add_permission`, `edit_permission`, `delete_permission`, `user_id`, `function_id`)
VALUES ('1', '1', '1', '1', '1', '28');

CREATE TABLE district
(
    district_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    status tinyint(1)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;