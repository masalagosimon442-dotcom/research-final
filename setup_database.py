# ============================================================
# HAZINA ASILI — Database Setup Script
# Run this in Google Colab to import the full database
# ============================================================

# Step 1: Install MySQL connector
import subprocess
subprocess.run(["pip", "install", "mysql-connector-python"], check=True)

import mysql.connector

# Step 2: Database credentials
DB_HOST = "sql113.infinityfree.com"
DB_PORT = 3306
DB_USER = "if0_42453789"
DB_PASS = "dmf9FuvblInQ"
DB_NAME = "if0_42453789_hazina_asili_db"

print("Connecting to database...")
conn = mysql.connector.connect(
    host=DB_HOST,
    port=DB_PORT,
    user=DB_USER,
    password=DB_PASS,
    database=DB_NAME
)
cursor = conn.cursor()
print("Connected successfully!")

# Step 3: Full schema SQL
schema_sql = """
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(180) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','researcher') NOT NULL DEFAULT 'researcher',
    `bio` TEXT DEFAULT NULL,
    `institution` VARCHAR(200) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `passport_document` VARCHAR(255) DEFAULT NULL,
    `api_key` VARCHAR(64) DEFAULT NULL,
    `reset_token` VARCHAR(64) DEFAULT NULL,
    `reset_expires` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
"""

schema_sql2 = """
CREATE TABLE IF NOT EXISTS `organisms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `kingdom` VARCHAR(100) NOT NULL,
    `phylum` VARCHAR(100) NOT NULL,
    `class` VARCHAR(100) NOT NULL,
    `order_name` VARCHAR(100) DEFAULT NULL,
    `family` VARCHAR(100) DEFAULT NULL,
    `genus` VARCHAR(100) DEFAULT NULL,
    `species` VARCHAR(100) DEFAULT NULL,
    `cell_type` ENUM('eukaryotic','prokaryotic') DEFAULT NULL,
    `habitat` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `structure_image` VARCHAR(255) DEFAULT NULL,
    `scientific_name` VARCHAR(200) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_organisms_name` (`scientific_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `references` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(400) NOT NULL,
    `author` VARCHAR(300) NOT NULL,
    `year` YEAR NOT NULL,
    `citation` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `compounds` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `formula` VARCHAR(100) NOT NULL,
    `molecular_weight` DECIMAL(10,4) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `structure_image` VARCHAR(255) DEFAULT NULL,
    `smiles` VARCHAR(1000) DEFAULT NULL,
    `inchikey` VARCHAR(27) DEFAULT NULL,
    `iupac_name` VARCHAR(500) DEFAULT NULL,
    `pubchem_cid` INT DEFAULT NULL,
    `biological_activities` TEXT DEFAULT NULL,
    `synonyms` TEXT DEFAULT NULL,
    `organism_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `version` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
"""

schema_sql3 = """
CREATE TABLE IF NOT EXISTS `compound_versions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `compound_id` INT UNSIGNED NOT NULL,
    `version` INT UNSIGNED NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `formula` VARCHAR(100) NOT NULL,
    `molecular_weight` DECIMAL(10,4) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `organism_id` INT UNSIGNED DEFAULT NULL,
    `changed_by` INT UNSIGNED DEFAULT NULL,
    `change_summary` VARCHAR(500) DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `compound_reference` (
    `compound_id` INT UNSIGNED NOT NULL,
    `reference_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`compound_id`, `reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `researcher_insights` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `compound_id` INT UNSIGNED NOT NULL,
    `insight_text` TEXT NOT NULL,
    `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `admin_comment` TEXT DEFAULT NULL,
    `reviewed_by` INT UNSIGNED DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `researcher_recommendations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `compound_id` INT UNSIGNED NOT NULL,
    `field_to_change` ENUM('name','formula','molecular_weight','description') NOT NULL,
    `suggested_value` TEXT NOT NULL,
    `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `admin_comment` TEXT DEFAULT NULL,
    `reviewed_by` INT UNSIGNED DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
"""

schema_sql4 = """
CREATE TABLE IF NOT EXISTS `approval_comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_type` ENUM('insight','recommendation') NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `comment` TEXT NOT NULL,
    `action` ENUM('comment','approve','reject') NOT NULL DEFAULT 'comment',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(300) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'info',
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(400) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `error_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `level` ENUM('notice','warning','critical') NOT NULL DEFAULT 'notice',
    `message` TEXT NOT NULL,
    `file` VARCHAR(400) DEFAULT NULL,
    `line` INT UNSIGNED DEFAULT NULL,
    `trace` TEXT DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `url` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `site_visits` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT DEFAULT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `page_url` VARCHAR(500) DEFAULT NULL,
    `user_agent` VARCHAR(300) DEFAULT NULL,
    `session_id` VARCHAR(64) DEFAULT NULL,
    `visited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `external_searches` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `query` VARCHAR(500) NOT NULL,
    `search_type` VARCHAR(50) NOT NULL DEFAULT 'name',
    `sources_queried` TEXT DEFAULT NULL,
    `results_count` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `compound_cache` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `query_key` VARCHAR(200) NOT NULL,
    `source` VARCHAR(50) NOT NULL,
    `raw_data` LONGTEXT DEFAULT NULL,
    `cached_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cache` (`query_key`, `source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
"""

# Step 4: Seed data
seed_sql = """
INSERT IGNORE INTO `users` (`name`,`email`,`password`,`role`,`institution`,`created_at`) VALUES
('System Admin','admin@hazina-asili.com','$2y$12$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi','admin','Hazina Asili Research Institute',NOW()),
('Dr. Jane Smith','researcher@hazina-asili.com','$2y$12$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi','researcher','University of Nairobi',NOW()),
('Prof. Ahmed Hassan','ahmed.hassan@hazina-asili.com','$2y$12$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi','researcher','Kenyatta University',NOW());

INSERT IGNORE INTO `organisms` (`kingdom`,`phylum`,`class`,`order_name`,`family`,`genus`,`species`,`scientific_name`,`cell_type`,`habitat`,`description`) VALUES
('Plantae','Tracheophyta','Magnoliopsida','Ericales','Theaceae','Camellia','sinensis','Camellia sinensis','eukaryotic','Tropical Asia','The tea plant. Source of EGCG and quercetin.'),
('Plantae','Tracheophyta','Magnoliopsida','Zingiberales','Zingiberaceae','Curcuma','longa','Curcuma longa','eukaryotic','Tropical South Asia','Turmeric. Primary source of curcumin.'),
('Plantae','Tracheophyta','Magnoliopsida','Asparagales','Amaryllidaceae','Allium','sativum','Allium sativum','eukaryotic','Central Asia','Garlic. Source of allicin.'),
('Plantae','Tracheophyta','Magnoliopsida','Zingiberales','Zingiberaceae','Zingiber','officinale','Zingiber officinale','eukaryotic','Southeast Asia','Ginger. Contains gingerols.'),
('Fungi','Ascomycota','Eurotiomycetes','Eurotiales','Aspergillaceae','Penicillium','chrysogenum','Penicillium chrysogenum','eukaryotic','Soil environments','Source of Penicillin G.'),
('Bacteria','Actinobacteria','Actinomycetia','Streptomycetales','Streptomycetaceae','Streptomyces','griseus','Streptomyces griseus','prokaryotic','Soil worldwide','Source of streptomycin.'),
('Plantae','Tracheophyta','Magnoliopsida','Vitales','Vitaceae','Vitis','vinifera','Vitis vinifera','eukaryotic','Mediterranean','Grape vine. Source of resveratrol.'),
('Plantae','Tracheophyta','Magnoliopsida','Gentianales','Rubiaceae','Coffea','arabica','Coffea arabica','eukaryotic','Ethiopia','Coffee plant. Source of caffeine.');

INSERT IGNORE INTO `references` (`title`,`author`,`year`,`citation`) VALUES
('Quercetin: A Versatile Flavonoid','Boots A.W., Haenen G.R., Bast A.',2008,'Boots AW et al. Eur J Pharmacol. 2008;585(2-3):325-37.'),
('Curcumin: Biological and Medicinal Properties','Aggarwal B.B., Harikumar K.B.',2009,'Aggarwal BB et al. Int J Biochem Cell Biol. 2009;41(1):40-59.'),
('Allicin: Chemistry and Biological Properties','Borlinghaus J. et al.',2014,'Borlinghaus J et al. Molecules. 2014;19(8):12591-618.'),
('Penicillin: Discovery and Development','Fleming A.',1929,'Fleming A. Br J Exp Pathol. 1929;10(3):226-236.');

INSERT IGNORE INTO `compounds` (`name`,`formula`,`molecular_weight`,`description`,`organism_id`,`smiles`,`inchikey`,`pubchem_cid`,`biological_activities`,`version`,`created_by`) VALUES
('Quercetin','C15H10O7',302.2357,'A plant flavonoid with antioxidant and anti-inflammatory properties.',1,'O=c1c(O)c(-c2ccc(O)c(O)c2)oc2cc(O)cc(O)c12','REOJLIXKJNPJEP-UHFFFAOYSA-N',5280343,'Antioxidant, anti-inflammatory, anticancer',1,1),
('Curcumin','C21H20O6',368.3799,'The principal curcuminoid of turmeric. Anti-inflammatory and antioxidant.',2,'COc1cc(/C=C/C(=O)CC(=O)/C=C/c2ccc(O)c(OC)c2)ccc1O','VFLDPWHFBUODDF-FCXRPNKRSA-N',969516,'Anti-inflammatory, antioxidant, anticancer',1,1),
('Allicin','C6H10OS2',162.2700,'Organosulfur compound from garlic with antimicrobial activity.',3,NULL,NULL,65036,'Antimicrobial, antifungal, antiviral',1,1),
('Gingerol','C17H26O4',294.3800,'Primary bioactive in fresh ginger. Anti-nausea and anti-inflammatory.',4,NULL,NULL,442495,'Anti-nausea, anti-inflammatory, analgesic',1,1),
('Penicillin G','C16H18N2O4S',334.3900,'First beta-lactam antibiotic. Inhibits bacterial cell wall synthesis.',5,NULL,NULL,5904,'Antibacterial, cell wall synthesis inhibitor',1,1),
('Streptomycin','C21H39N7O12',581.5700,'Aminoglycoside antibiotic effective against tuberculosis.',6,NULL,NULL,19649,'Antibacterial, antitubercular',1,1),
('Resveratrol','C14H12O3',228.2440,'Stilbenoid from red grapes with cardiovascular protection.',7,'Oc1ccc(/C=C/c2cc(O)cc(O)c2)cc1','LUKBXBAWPPAYHM-SHYZEUOFSA-N',445154,'Antioxidant, cardioprotective, anti-aging',1,1),
('Caffeine','C8H10N4O2',194.1900,'Purine alkaloid from coffee. Acts as CNS stimulant.',8,'Cn1cnc2c1c(=O)n(C)c(=O)n2C','RYYVLZVUVIJVGH-UHFFFAOYSA-N',2519,'CNS stimulant, adenosine antagonist',1,1);

INSERT IGNORE INTO `compound_reference` (`compound_id`,`reference_id`) VALUES (1,1),(2,2),(3,3),(5,4);
"""

# Step 5: Run all SQL statements
def run_sql_block(sql_block, label):
    statements = [s.strip() for s in sql_block.strip().split(';') if s.strip()]
    success = 0
    for stmt in statements:
        try:
            cursor.execute(stmt)
            conn.commit()
            success += 1
        except Exception as e:
            print(f"  ⚠️  {e}")
    print(f"✅ {label}: {success}/{len(statements)} statements executed")

print("\n📦 Creating tables...")
run_sql_block(schema_sql,  "Core schema (users, organisms)")
run_sql_block(schema_sql2, "Compounds & references tables")
run_sql_block(schema_sql3, "Versions, insights, recommendations")
run_sql_block(schema_sql4, "Logs, cache, visits, searches")

print("\n🌱 Inserting sample data...")
run_sql_block(seed_sql, "Sample data")

print("\n🎉 Done! Your database is ready.")
print("=" * 50)
print("Login credentials:")
print("  Admin:      admin@hazina-asili.com / Admin@1234")
print("  Researcher: researcher@hazina-asili.com / Admin@1234")
print("=" * 50)

cursor.close()
conn.close()
