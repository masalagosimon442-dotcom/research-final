-- ============================================================
-- HAZINA ASILI — Complete Sample Data Seed
-- Covers every table in the system
-- Run AFTER full schema + all migrations are applied
-- ============================================================

USE `natural_compounds_db`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. USERS
-- ============================================================
INSERT IGNORE INTO `users`
    (`name`, `email`, `password`, `role`, `bio`, `institution`, `created_at`)
VALUES
('System Admin',
 'admin@hazina-asili.com',
 '$2y$12$eImiTXuWVxfM37uY4JANjQ==.placeholder',
 'admin',
 'Platform administrator responsible for data quality and user management.',
 'Hazina Asili Research Institute',
 NOW()),
('Dr. Jane Smith',
 'researcher@hazina-asili.com',
 '$2y$12$eImiTXuWVxfM37uY4JANjQ==.placeholder',
 'researcher',
 'Phytochemist specialising in tropical plant bioactives and antimicrobial compounds.',
 'University of Nairobi – Department of Biochemistry',
 NOW()),
('Prof. Ahmed Hassan',
 'ahmed.hassan@hazina-asili.com',
 '$2y$12$eImiTXuWVxfM37uY4JANjQ==.placeholder',
 'researcher',
 'Ethnobotanist with 20 years experience studying East African medicinal plants.',
 'Kenyatta University – School of Pure and Applied Sciences',
 NOW()),
('Dr. Amina Osei',
 'amina.osei@hazina-asili.com',
 '$2y$12$eImiTXuWVxfM37uY4JANjQ==.placeholder',
 'researcher',
 'Microbiologist focusing on fungal secondary metabolites and antibiotic resistance.',
 'University of Ghana – Noguchi Memorial Institute',
 NOW());

-- ============================================================
-- 2. ORGANISMS  (full taxonomy)
-- ============================================================
INSERT IGNORE INTO `organisms`
    (`kingdom`,`phylum`,`class`,`order_name`,`family`,`genus`,`species`,
     `scientific_name`,`cell_type`,`habitat`,`description`)
VALUES
('Plantae','Tracheophyta','Magnoliopsida','Ericales','Theaceae','Camellia','sinensis',
 'Camellia sinensis','eukaryotic','Tropical & subtropical Asia',
 'The tea plant. Source of green, black, and white tea. Rich in polyphenols including EGCG and quercetin.'),

('Plantae','Tracheophyta','Magnoliopsida','Zingiberales','Zingiberaceae','Curcuma','longa',
 'Curcuma longa','eukaryotic','Tropical South Asia',
 'Turmeric. Rhizome widely used as a spice and in Ayurvedic medicine. Primary source of curcumin.'),

('Plantae','Tracheophyta','Magnoliopsida','Asparagales','Amaryllidaceae','Allium','sativum',
 'Allium sativum','eukaryotic','Central Asia, cultivated worldwide',
 'Garlic. One of the oldest cultivated plants. Source of allicin and other organosulfur bioactives.'),

('Plantae','Tracheophyta','Magnoliopsida','Zingiberales','Zingiberaceae','Zingiber','officinale',
 'Zingiber officinale','eukaryotic','Southeast Asia',
 'Ginger. Rhizome used globally as a spice and medicine. Contains gingerols and shogaols.'),

('Fungi','Ascomycota','Eurotiomycetes','Eurotiales','Aspergillaceae','Penicillium','chrysogenum',
 'Penicillium chrysogenum','eukaryotic','Soil, decaying organic matter, indoor environments',
 'Blue-green mold from which Fleming discovered penicillin in 1928. Industrial source of Penicillin G.'),

('Bacteria','Actinobacteria','Actinomycetia','Streptomycetales','Streptomycetaceae','Streptomyces','griseus',
 'Streptomyces griseus','prokaryotic','Soil worldwide',
 'Gram-positive soil bacterium producing streptomycin, the first antibiotic effective against tuberculosis.'),

('Plantae','Tracheophyta','Magnoliopsida','Vitales','Vitaceae','Vitis','vinifera',
 'Vitis vinifera','eukaryotic','Mediterranean, warm temperate regions',
 'Common grape vine. Skin of red grapes is a primary source of resveratrol and other polyphenols.'),

('Plantae','Tracheophyta','Magnoliopsida','Gentianales','Rubiaceae','Coffea','arabica',
 'Coffea arabica','eukaryotic','Ethiopia, cultivated tropics worldwide',
 'Arabica coffee plant. Seeds (beans) are the most common source of caffeine and chlorogenic acids.'),

('Plantae','Tracheophyta','Magnoliopsida','Caryophyllales','Ranunculaceae','Nigella','sativa',
 'Nigella sativa','eukaryotic','Southwest Asia, Mediterranean, North Africa',
 'Black seed / black cumin. Seeds yield thymoquinone-rich oil used in traditional Islamic medicine.'),

('Plantae','Tracheophyta','Magnoliopsida','Lamiales','Lamiaceae','Rosmarinus','officinalis',
 'Rosmarinus officinalis','eukaryotic','Mediterranean scrubland',
 'Rosemary. Aromatic shrub; leaves are rich in ursolic acid, rosmarinic acid, and carnosic acid.');

-- ============================================================
-- 3. REFERENCES
-- ============================================================
INSERT IGNORE INTO `references` (`title`,`author`,`year`,`citation`) VALUES
('Quercetin: A Versatile Flavonoid',
 'Boots A.W., Haenen G.R., Bast A.',
 2008,
 'Boots AW, Haenen GR, Bast A. Health effects of quercetin: from antioxidant to nutraceutical. Eur J Pharmacol. 2008;585(2-3):325-37.'),

('Curcumin: Biological and Medicinal Properties',
 'Aggarwal B.B., Harikumar K.B.',
 2009,
 'Aggarwal BB, Harikumar KB. Potential therapeutic effects of curcumin. Int J Biochem Cell Biol. 2009;41(1):40-59.'),

('Allicin: Chemistry and Biological Properties',
 'Borlinghaus J., Albrecht F., Gruhlke M.C.',
 2014,
 'Borlinghaus J et al. Allicin: chemistry and biological properties. Molecules. 2014;19(8):12591-618.'),

('On the Antibacterial Action of Cultures of a Penicillium',
 'Fleming A.',
 1929,
 'Fleming A. On the antibacterial action of cultures of a Penicillium, with special reference to their use in the isolation of B. influenzae. Br J Exp Pathol. 1929;10(3):226-236.'),

('Resveratrol and Cardiovascular Protection',
 'Baur J.A., Sinclair D.A.',
 2006,
 'Baur JA, Sinclair DA. Therapeutic potential of resveratrol: the in vivo evidence. Nat Rev Drug Discov. 2006;5(6):493-506.'),

('Thymoquinone: Pharmacological and Clinical Evidence',
 'Darakhshan S., Pour A.B., Colagar A.H.',
 2015,
 'Darakhshan S et al. Thymoquinone and its therapeutic potentials. Pharmacol Res. 2015;95-96:138-158.'),

('Ursolic Acid: A Pentacyclic Triterpenoid with Diverse Pharmacological Activities',
 'Kashyap D., Tuli H.S., Sharma A.K.',
 2016,
 'Kashyap D et al. Ursolic acid: A versatile pentacyclic triterpenoid. Phytomedicine. 2016;23(14):1782-1798.'),

('Caffeine and Health: An Overview',
 'Fredholm B.B., Battig K., Holmen J.',
 1999,
 'Fredholm BB et al. Actions of caffeine in the brain with special reference to factors that contribute to its widespread use. Pharmacol Rev. 1999;51(1):83-133.');

-- ============================================================
-- 4. COMPOUNDS  (with v4 external data columns)
-- ============================================================
INSERT IGNORE INTO `compounds`
    (`name`,`formula`,`molecular_weight`,`description`,`organism_id`,
     `smiles`,`inchikey`,`iupac_name`,`pubchem_cid`,
     `biological_activities`,`synonyms`,`version`,`created_by`)
VALUES
('Quercetin',
 'C15H10O7', 302.2357,
 'A plant flavonoid present abundantly in onions, apples, and green tea. Potent antioxidant and anti-inflammatory agent. Inhibits histamine release and acts as a natural antihistamine.',
 (SELECT id FROM organisms WHERE scientific_name='Camellia sinensis'),
 'O=c1c(O)c(-c2ccc(O)c(O)c2)oc2cc(O)cc(O)c12',
 'REOJLIXKJNPJEP-UHFFFAOYSA-N',
 '2-(3,4-dihydroxyphenyl)-3,5,7-trihydroxy-4H-chromen-4-one',
 5280343,
 'Antioxidant, anti-inflammatory, antihistamine, antiviral, anticancer',
 'Quercetin-3,3'',4'',5,7-pentahydroxyflavone; Sophoretin; Meletin',
 1, 1),

('Curcumin',
 'C21H20O6', 368.3799,
 'The principal curcuminoid of turmeric (Curcuma longa). Extensively studied for anti-inflammatory and antioxidant properties. Inhibits NF-κB pathway. Bioavailability is enhanced by piperine co-administration.',
 (SELECT id FROM organisms WHERE scientific_name='Curcuma longa'),
 'COc1cc(/C=C/C(=O)CC(=O)/C=C/c2ccc(O)c(OC)c2)ccc1O',
 'VFLDPWHFBUODDF-FCXRPNKRSA-N',
 '(1E,6E)-1,7-bis(4-hydroxy-3-methoxyphenyl)hepta-1,6-diene-3,5-dione',
 969516,
 'Anti-inflammatory, antioxidant, anticancer, neuroprotective, hepatoprotective',
 'Diferuloylmethane; Turmeric yellow; C.I. Natural Yellow 3',
 1, 1),

('Allicin',
 'C6H10OS2', 162.2700,
 'Organosulfur compound produced when garlic is crushed or chopped. Responsible for garlic pungent odor. Exhibits broad-spectrum antimicrobial activity against bacteria, fungi, and viruses.',
 (SELECT id FROM organisms WHERE scientific_name='Allium sativum'),
 'O=S(=O)(Cc1ccc[nH]1)Cc1ccc[nH]1',
 'LNQVTSROQFSFDN-DKWTVANSSA-N',
 '3-prop-2-enylsulfinylprop-1-ene',
 65036,
 'Antimicrobial, antifungal, antiviral, antioxidant, cardioprotective',
 'Diallyl thiosulfinate; Allyl 2-propenethiosulfinate',
 1, 1),

('Gingerol',
 'C17H26O4', 294.3800,
 'The primary bioactive compound in fresh ginger. Responsible for the characteristic pungent taste. Exhibits anti-nausea, anti-inflammatory, antioxidant, and analgesic properties.',
 (SELECT id FROM organisms WHERE scientific_name='Zingiber officinale'),
 'CCCCCC(O)CC(=O)CCc1ccc(O)c(OC)c1',
 'XOMOVKXKWUPQKU-UHFFFAOYSA-N',
 '(S)-5-hydroxy-1-(4-hydroxy-3-methoxyphenyl)decan-3-one',
 442495,
 'Anti-nausea, anti-inflammatory, analgesic, antioxidant, anticancer',
 '[6]-Gingerol; (S)-[6]-Gingerol',
 1, 1),

('Penicillin G',
 'C16H18N2O4S', 334.3900,
 'The first beta-lactam antibiotic used in clinical medicine. Produced by Penicillium chrysogenum. Inhibits bacterial cell wall synthesis by binding penicillin-binding proteins. Still used for streptococcal and syphilis infections.',
 (SELECT id FROM organisms WHERE scientific_name='Penicillium chrysogenum'),
 'CC1(C)SC2C(NC(=O)Cc3ccccc3)C(=O)N2C1C(=O)O',
 'JGSARLDLIJGVTE-KKUMJFAQSA-N',
 '(2S,5R,6R)-3,3-dimethyl-7-oxo-6-(2-phenylacetamido)-4-thia-1-azabicyclo[3.2.0]heptane-2-carboxylic acid',
 5904,
 'Antibacterial (gram-positive), cell wall synthesis inhibitor',
 'Benzylpenicillin; Penicillin; Pen G',
 1, 1),

('Streptomycin',
 'C21H39N7O12', 581.5700,
 'Aminoglycoside antibiotic produced by Streptomyces griseus. Discovered in 1943 by Waksman. First antibiotic effective against tuberculosis. Inhibits protein synthesis by binding the 30S ribosomal subunit.',
 (SELECT id FROM organisms WHERE scientific_name='Streptomyces griseus'),
 'CNC1C(O)CC(OC2OC(CO)(OC3OCC(N=C(N)N)C3O)C(O)C2NC(N)=N)C(O1)OC4C(N=C(N)N)CC(O)C4O',
 'UCSJCTRKOZZVIO-KNVOCUESNA-N',
 'N-methyl-L-glucosamine; streptidine; streptobiosamine',
 19649,
 'Antibacterial, antitubercular, aminoglycoside antibiotic',
 'Streptomycin sulfate; Agrept; Streptomycin A',
 1, 1),

('Resveratrol',
 'C14H12O3', 228.2440,
 'A stilbenoid polyphenol found in grape skins, berries, and peanuts. Activates sirtuins (SIRT1), mimicking caloric restriction. Associated with cardiovascular protection, anti-aging, and anticancer effects.',
 (SELECT id FROM organisms WHERE scientific_name='Vitis vinifera'),
 'Oc1ccc(/C=C/c2cc(O)cc(O)c2)cc1',
 'LUKBXBAWPPAYHM-SHYZEUOFSA-N',
 '5-[(E)-2-(4-hydroxyphenyl)ethenyl]benzene-1,3-diol',
 445154,
 'Antioxidant, cardioprotective, anti-aging, anticancer, anti-inflammatory',
 'trans-Resveratrol; 3,4'',5-Trihydroxystilbene; Resvida',
 1, 1),

('Caffeine',
 'C8H10N4O2', 194.1900,
 'A purine alkaloid (methylxanthine) found in coffee, tea, cacao, and guarana. Acts as an adenosine receptor antagonist, producing CNS stimulation. Most widely consumed psychoactive substance in the world.',
 (SELECT id FROM organisms WHERE scientific_name='Coffea arabica'),
 'Cn1cnc2c1c(=O)n(C)c(=O)n2C',
 'RYYVLZVUVIJVGH-UHFFFAOYSA-N',
 '1,3,7-trimethyl-3,7-dihydro-1H-purine-2,6-dione',
 2519,
 'CNS stimulant, adenosine antagonist, bronchodilator, diuretic',
 '1,3,7-Trimethylxanthine; Guaranine; Mateine; Theine',
 1, 1),

('Thymoquinone',
 'C10H12O2', 164.2010,
 'The primary bioactive component of Nigella sativa (black seed) volatile oil. Exhibits potent anti-inflammatory, antioxidant, anticancer, and antimicrobial properties. Used in traditional Unani and Islamic medicine.',
 (SELECT id FROM organisms WHERE scientific_name='Nigella sativa'),
 'CC1=CC(=O)C(C(C)C)=CC1=O',
 'MGSRCZKZVOBKFT-UHFFFAOYSA-N',
 '2-isopropyl-5-methyl-1,4-benzoquinone',
 10281,
 'Anti-inflammatory, antioxidant, anticancer, antimicrobial, immunomodulatory',
 'TQ; 2-Methyl-5-isopropyl-1,4-benzoquinone',
 1, 1),

('Ursolic Acid',
 'C30H48O3', 456.7000,
 'A pentacyclic triterpenoid found in apple peel, rosemary, basil, and thyme. Inhibits cancer cell growth, promotes skeletal muscle hypertrophy, and has anti-inflammatory and hepatoprotective effects.',
 (SELECT id FROM organisms WHERE scientific_name='Rosmarinus officinalis'),
 'CC1CCC2(CCC3C4CCC(O)C4(C)CCC3C2CC1C(=O)O)C',
 'JMJCJFCGVHPZQO-GEZDMFBZSA-N',
 '(1R,2R,4aS,6aR,6aS,6bR,8aR,10S,12aR,14bS)-10-hydroxy-1,2,6a,6b,9,9,12a-heptamethyl-2,3,4,5,6,6a,7,8,8a,10,11,12,13,14b-tetradecahydro-1H-picene-4a(4H)-carboxylic acid',
 64945,
 'Anti-inflammatory, anticancer, hepatoprotective, antimicrobial, muscle growth',
 'Prunol; Malol; Urson',
 1, 1);

-- ============================================================
-- 5. COMPOUND ↔ REFERENCE  (junction)
-- ============================================================
INSERT IGNORE INTO `compound_reference` (`compound_id`,`reference_id`)
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Quercetin'    AND r.author LIKE '%Boots%'
UNION ALL
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Curcumin'     AND r.author LIKE '%Aggarwal%'
UNION ALL
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Allicin'      AND r.author LIKE '%Borlinghaus%'
UNION ALL
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Penicillin G' AND r.author LIKE '%Fleming%'
UNION ALL
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Resveratrol'  AND r.author LIKE '%Baur%'
UNION ALL
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Thymoquinone' AND r.author LIKE '%Darakhshan%'
UNION ALL
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Ursolic Acid' AND r.author LIKE '%Kashyap%'
UNION ALL
SELECT c.id, r.id FROM compounds c, `references` r
WHERE c.name='Caffeine'     AND r.author LIKE '%Fredholm%';

-- ============================================================
-- 6. RESEARCHER INSIGHTS  (pending + approved + rejected)
-- ============================================================
INSERT IGNORE INTO `researcher_insights`
    (`user_id`,`compound_id`,`insight_text`,`status`,`admin_comment`,`reviewed_by`,`reviewed_at`,`created_at`)
VALUES
-- Approved insight
(2,
 (SELECT id FROM compounds WHERE name='Curcumin'),
 'Recent in-vivo studies suggest that nanoparticle-encapsulated curcumin achieves 30-fold higher plasma bioavailability compared to free curcumin. This formulation strategy could transform curcumin from a poorly bioavailable compound into a clinically viable anti-inflammatory agent.',
 'approved',
 'Excellent insight supported by current literature. Consider adding the Singh et al. 2019 nanoparticle review as a reference.',
 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),

-- Approved insight
(3,
 (SELECT id FROM compounds WHERE name='Quercetin'),
 'Quercetin demonstrates synergistic antimicrobial activity when combined with conventional antibiotics such as ciprofloxacin against resistant E. coli strains. The mechanism appears to involve quercetin inhibiting efflux pump activity, restoring antibiotic sensitivity in MDR strains.',
 'approved',
 'Well-supported observation. Recommend expanding the biological_activities field to include efflux pump inhibitor.',
 1, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Pending insight
(2,
 (SELECT id FROM compounds WHERE name='Resveratrol'),
 'Oral resveratrol is extensively metabolised by gut microbiota before absorption. The metabolites piceatannol and resveratrol-3-glucuronide may be the actual bioactive species responsible for the cardiovascular effects reported in human studies. This warrants re-evaluation of current bioavailability studies.',
 'pending', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- Rejected insight
(4,
 (SELECT id FROM compounds WHERE name='Caffeine'),
 'Caffeine should be classified as a controlled substance due to its psychoactive effects and potential for dependence.',
 'rejected',
 'While caffeine has psychoactive properties, the scientific consensus does not support controlled substance classification at normal dietary intake levels. Please revise with supporting peer-reviewed literature.',
 1, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),

-- Pending insight
(3,
 (SELECT id FROM compounds WHERE name='Allicin'),
 'Allicin is inherently unstable and degrades rapidly into diallyl disulfide (DADS) and diallyl trisulfide (DATS) in aqueous environments. Standardised garlic supplements should be evaluated for actual allicin release rather than relying on "allicin potential" labelling.',
 'pending', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- 7. RESEARCHER RECOMMENDATIONS
-- ============================================================
INSERT IGNORE INTO `researcher_recommendations`
    (`user_id`,`compound_id`,`field_to_change`,`suggested_value`,`status`,`admin_comment`,`reviewed_by`,`reviewed_at`,`created_at`)
VALUES
-- Approved recommendation
(2,
 (SELECT id FROM compounds WHERE name='Curcumin'),
 'description',
 'The principal curcuminoid of turmeric (Curcuma longa). Potent anti-inflammatory and antioxidant agent. Inhibits NF-κB and COX-2 pathways. Bioavailability is markedly enhanced by piperine (20x) and lipid-based nanoformulations. Extensively investigated in >120 clinical trials for cancer, Alzheimer''s, arthritis, and metabolic syndrome.',
 'approved',
 'Accepted. Updated description reflects current clinical trial landscape.',
 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Approved recommendation
(3,
 (SELECT id FROM compounds WHERE name='Quercetin'),
 'molecular_weight',
 '302.2357',
 'approved',
 'Molecular weight confirmed against PubChem CID 5280343. No change required — value was already correct.',
 1, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- Pending recommendation
(4,
 (SELECT id FROM compounds WHERE name='Streptomycin'),
 'description',
 'Aminoglycoside antibiotic produced by Streptomyces griseus. Discovered by Waksman et al. in 1943 (Nobel Prize 1952). First antibiotic clinically effective against tuberculosis. Inhibits protein synthesis by binding the 16S rRNA of the 30S ribosomal subunit. Also used for brucellosis, plague, and tularemia. Ototoxicity and nephrotoxicity are major adverse effects.',
 'pending', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- Rejected recommendation
(2,
 (SELECT id FROM compounds WHERE name='Caffeine'),
 'formula',
 'C8H10N4O3',
 'rejected',
 'The molecular formula C8H10N4O2 is correct per IUPAC and PubChem CID 2519. C8H10N4O3 is the formula for theophylline, a related but distinct compound.',
 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- Pending recommendation
(3,
 (SELECT id FROM compounds WHERE name='Resveratrol'),
 'name',
 'trans-Resveratrol',
 'pending', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- 8. APPROVAL COMMENTS  (workflow audit trail)
-- ============================================================
INSERT IGNORE INTO `approval_comments`
    (`entity_type`,`entity_id`,`user_id`,`comment`,`action`,`created_at`)
SELECT
    'insight', ri.id, 1,
    'Reviewed and approved. The nanoparticle bioavailability data is well-documented.',
    'approve', DATE_SUB(NOW(), INTERVAL 3 DAY)
FROM researcher_insights ri
WHERE ri.status='approved' AND ri.insight_text LIKE '%nanoparticle%'
LIMIT 1;

INSERT IGNORE INTO `approval_comments`
    (`entity_type`,`entity_id`,`user_id`,`comment`,`action`,`created_at`)
SELECT
    'recommendation', rr.id, 1,
    'Verified molecular formula against authoritative databases. Recommendation rejected — original value is correct.',
    'reject', DATE_SUB(NOW(), INTERVAL 3 DAY)
FROM researcher_recommendations rr
WHERE rr.status='rejected' AND rr.suggested_value='C8H10N4O3'
LIMIT 1;

-- ============================================================
-- 9. COMPOUND VERSIONS  (version history)
-- ============================================================
INSERT IGNORE INTO `compound_versions`
    (`compound_id`,`version`,`name`,`formula`,`molecular_weight`,`description`,
     `organism_id`,`changed_by`,`change_summary`,`old_values`,`new_values`,`created_at`)
SELECT
    c.id, 1, c.name, c.formula, c.molecular_weight,
    'Initial entry — compound added to database.',
    c.organism_id, 1, 'Initial creation',
    NULL,
    JSON_OBJECT('name',c.name,'formula',c.formula,'molecular_weight',c.molecular_weight),
    DATE_SUB(NOW(), INTERVAL 10 DAY)
FROM compounds c
WHERE c.name IN ('Quercetin','Curcumin','Allicin','Gingerol','Penicillin G');

-- Simulate a v2 edit on Curcumin
INSERT IGNORE INTO `compound_versions`
    (`compound_id`,`version`,`name`,`formula`,`molecular_weight`,`description`,
     `organism_id`,`changed_by`,`change_summary`,`old_values`,`new_values`,`created_at`)
SELECT
    c.id, 2, c.name, c.formula, c.molecular_weight,
    c.description,
    c.organism_id, 2,
    'Updated description with NF-κB pathway reference and bioavailability note.',
    JSON_OBJECT('description','The principal curcuminoid of turmeric.'),
    JSON_OBJECT('description', c.description),
    DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM compounds c WHERE c.name = 'Curcumin' LIMIT 1;

-- ============================================================
-- 10. ACTIVITY LOG
-- ============================================================
INSERT INTO `activity_log`
    (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`,`created_at`)
SELECT 1,'compound_create','compound',id,
    CONCAT('Created compound: ', name),
    '127.0.0.1', DATE_SUB(NOW(), INTERVAL 10 DAY)
FROM compounds WHERE name='Quercetin' LIMIT 1;

INSERT INTO `activity_log`
    (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`,`created_at`)
SELECT 1,'compound_create','compound',id,
    CONCAT('Created compound: ', name),
    '127.0.0.1', DATE_SUB(NOW(), INTERVAL 10 DAY)
FROM compounds WHERE name='Curcumin' LIMIT 1;

INSERT INTO `activity_log`
    (`user_id`,`action`,`entity_type`,`entity_id`,`old_values`,`new_values`,`details`,`ip_address`,`created_at`)
SELECT 1,'compound_update','compound',id,
    JSON_OBJECT('description','The principal curcuminoid of turmeric.'),
    JSON_OBJECT('description','Updated with NF-κB pathway reference.'),
    'Updated Curcumin description',
    '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM compounds WHERE name='Curcumin' LIMIT 1;

INSERT INTO `activity_log`
    (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`,`created_at`)
SELECT 2,'insight_submit','insight',id,
    'Submitted insight on nanoparticle bioavailability of curcumin.',
    '192.168.1.42', DATE_SUB(NOW(), INTERVAL 7 DAY)
FROM researcher_insights WHERE status='approved' AND insight_text LIKE '%nanoparticle%' LIMIT 1;

INSERT INTO `activity_log`
    (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`,`created_at`)
SELECT 2,'sequential_search',NULL,NULL,
    'Searched: "quercetin" [name] — 3 results from local → PubChem',
    '192.168.1.42', DATE_SUB(NOW(), INTERVAL 1 DAY);

INSERT INTO `activity_log`
    (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`,`created_at`)
VALUES
(1,'user_login',NULL,NULL,'Admin login','127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2,'user_login',NULL,NULL,'Researcher login','192.168.1.42', DATE_SUB(NOW(), INTERVAL 2 HOUR));

-- ============================================================
-- 11. NOTIFICATIONS
-- ============================================================
INSERT INTO `notifications`
    (`user_id`,`type`,`title`,`message`,`link`,`is_read`,`created_at`)
VALUES
(2,'success','Insight Approved',
 'Your insight on curcumin nanoparticle bioavailability has been approved by the admin.',
 'views/researcher/insights/index.php', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),

(2,'danger','Insight Rejected',
 'Your insight on caffeine classification was rejected. Please review the admin comment.',
 'views/researcher/insights/index.php', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),

(2,'success','Recommendation Approved',
 'Your recommendation to update the Curcumin description has been accepted.',
 'views/researcher/recommendations/index.php', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

(3,'info','New Compound Added',
 'A new compound (Ursolic Acid) has been added to the database.',
 'views/researcher/compounds/index.php', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),

(1,'warning','Pending Reviews',
 'You have 3 pending researcher submissions awaiting your review.',
 'views/admin/insights/index.php', 0, NOW());

-- ============================================================
-- 12. EXTERNAL SEARCHES  (hybrid search log)
-- ============================================================
INSERT INTO `external_searches`
    (`user_id`,`query`,`search_type`,`sources_queried`,`results_count`,`created_at`)
VALUES
(2, 'quercetin',        'name',     'local,PubChem',              5,  DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'curcumin',         'name',     'local',                      3,  DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 'Curcuma longa',    'organism', 'local,NCBI',                 2,  DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'C15H10O7',         'formula',  'local,PubChem',              4,  DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 'taxol',            'name',     'local,PubChem',              1,  DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'berberine',        'name',     'local,PubChem',              3,  DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 'Streptomyces',     'organism', 'local,NCBI',                 2,  DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 'resveratrol',      'name',     'local',                      1,  DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 'CC(=O)Oc1ccccc1C(=O)O', 'smiles', 'local,PubChem',          2,  DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(2, 'capsaicin',        'name',     'local,PubChem',              2,  DATE_SUB(NOW(), INTERVAL 10 MINUTE));

-- ============================================================
-- 13. COMPOUND CACHE  (external API cache)
-- ============================================================
INSERT IGNORE INTO `compound_cache`
    (`query_key`,`source`,`raw_data`,`cached_at`,`expires_at`)
VALUES
('pubchem_name_c4c59a9e2b94d5e9e5e4b7d8f0a1c2d3',
 'pubchem',
 '[{"source":"PubChem","cid":5280343,"name":"Quercetin","iupac_name":"2-(3,4-dihydroxyphenyl)-3,5,7-trihydroxy-4H-chromen-4-one","formula":"C15H10O7","molecular_weight":302.2,"smiles":"O=c1c(O)c(-c2ccc(O)c(O)c2)oc2cc(O)cc(O)c12","inchikey":"REOJLIXKJNPJEP-UHFFFAOYSA-N"}]',
 DATE_SUB(NOW(), INTERVAL 2 HOUR),
 DATE_ADD(NOW(), INTERVAL 22 HOUR)),

('pubchem_name_a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6',
 'pubchem',
 '[{"source":"PubChem","cid":969516,"name":"Curcumin","iupac_name":"(1E,6E)-1,7-bis(4-hydroxy-3-methoxyphenyl)hepta-1,6-diene-3,5-dione","formula":"C21H20O6","molecular_weight":368.38}]',
 DATE_SUB(NOW(), INTERVAL 1 HOUR),
 DATE_ADD(NOW(), INTERVAL 23 HOUR)),

('ncbi_f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8',
 'ncbi',
 '[{"source":"NCBI Taxonomy","tax_id":"42235","name":"Curcuma longa","kingdom":"Viridiplantae","phylum":"Streptophyta","class":"Magnoliopsida","order":"Zingiberales","family":"Zingiberaceae","genus":"Curcuma","species":"Curcuma longa"}]',
 DATE_SUB(NOW(), INTERVAL 1 DAY),
 DATE_ADD(NOW(), INTERVAL 23 HOUR)),

('pubmed_count_c4c59a9e2b94d5e9e5e4b7d8f0a1c2d3',
 'pubmed',
 '{"count":15823}',
 DATE_SUB(NOW(), INTERVAL 2 HOUR),
 DATE_ADD(NOW(), INTERVAL 22 HOUR));
