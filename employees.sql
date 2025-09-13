CREATE DATABASE employee_management;
USE employee_management;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name_fr VARCHAR(100) NOT NULL,
    last_name_fr VARCHAR(100) NOT NULL,
    first_name_ar VARCHAR(100) NOT NULL,
    last_name_ar VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    birth_place VARCHAR(100) NOT NULL,
    recruitment_date DATE NOT NULL,
    superior_position VARCHAR(100),
    grade VARCHAR(100) NOT NULL,
    grade_date DATE NOT NULL,
    echelon INT NOT NULL,
    echelon_date DATE NOT NULL,
    status ENUM('Actif', 'Inactif', 'Congé', 'Retraité') NOT NULL,
    department VARCHAR(100) NOT NULL,
    salary DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO employees (first_name_fr, last_name_fr, first_name_ar, last_name_ar, birth_date, birth_place, recruitment_date, superior_position, grade, grade_date, echelon, echelon_date, status, department, salary) VALUES
('Jean', 'Dupont', 'محمد', 'الفضيل', '1985-05-15', 'Casablanca', '2010-03-12', 'Directeur IT', 'Ingénieur', '2015-06-20', 3, '2020-03-10', 'Actif', 'IT', 25000),
('Marie', 'Martin', 'فاطمة', 'الزهراء', '1990-12-22', 'Rabat', '2015-08-05', 'Chef de département', 'Technicien', '2018-09-05', 2, '2021-07-15', 'Actif', 'Ressources Humaines', 18000);