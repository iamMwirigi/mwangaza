-- Create the User table
CREATE TABLE `user` (
    id INT AUTO_INCREMENT NOT NULL, 
    email VARCHAR(180) NOT NULL, 
    roles JSON NOT NULL, 
    password VARCHAR(255) NOT NULL, 
    full_name VARCHAR(255) NOT NULL, 
    date_of_birth DATE NOT NULL, 
    national_id VARCHAR(20) NOT NULL, 
    staff_id VARCHAR(50) DEFAULT NULL, 
    work_email VARCHAR(180) DEFAULT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), 
    UNIQUE INDEX UNIQ_8D93D649C33F1D (national_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Optional: Create the migration versions table for future use
CREATE TABLE doctrine_migration_versions (
    version VARCHAR(191) NOT NULL, 
    executed_at DATETIME DEFAULT NULL, 
    execution_time INT DEFAULT NULL, 
    PRIMARY KEY(version)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
