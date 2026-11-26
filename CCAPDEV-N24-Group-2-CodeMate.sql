-- CodeMate database (CCAPDEV N24 Group 2)
-- mysql -u root -p < CCAPDEV-N24-Group-2-CodeMate.sql
-- Sample account password: password123

DROP DATABASE IF EXISTS codemate;
CREATE DATABASE codemate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE codemate;

-- Tables

CREATE TABLE users (
  user_id        INT AUTO_INCREMENT PRIMARY KEY,
  full_name      VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL UNIQUE,
  password       VARCHAR(255) NOT NULL,
  role           ENUM('tutee', 'tutor', 'admin') NOT NULL DEFAULT 'tutee',
  account_status ENUM('active', 'deactivated') NOT NULL DEFAULT 'active',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE courses (
  course_id   INT AUTO_INCREMENT PRIMARY KEY,
  course_code VARCHAR(10) NOT NULL UNIQUE,
  course_name VARCHAR(100) NOT NULL,
  status      ENUM('active', 'archived') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE tutor_profiles (
  tutor_id            INT PRIMARY KEY,
  bio                 TEXT,
  experience          TEXT,
  subjects            VARCHAR(255) NULL,
  verification_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
  applied_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at         DATETIME NULL,
  FOREIGN KEY (tutor_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tutor_courses (
  tutor_id  INT NOT NULL,
  course_id INT NOT NULL,
  PRIMARY KEY (tutor_id, course_id),
  FOREIGN KEY (tutor_id) REFERENCES tutor_profiles (tutor_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses (course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tutor_slots (
  slot_id    INT AUTO_INCREMENT PRIMARY KEY,
  tutor_id   INT NOT NULL,
  course_id  INT NOT NULL,
  slot_date  DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time   TIME NOT NULL,
  mode       ENUM('Online', 'Face-to-face') NOT NULL DEFAULT 'Online',
  status     ENUM('open', 'booked', 'closed') NOT NULL DEFAULT 'open',
  FOREIGN KEY (tutor_id) REFERENCES tutor_profiles (tutor_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses (course_id),
  CHECK (end_time > start_time)
) ENGINE=InnoDB;

CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  slot_id    INT NOT NULL,
  tutee_id   INT NOT NULL,
  notes      TEXT,
  status     ENUM('pending', 'confirmed', 'declined', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (slot_id) REFERENCES tutor_slots (slot_id) ON DELETE CASCADE,
  FOREIGN KEY (tutee_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
  review_id  INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL UNIQUE,
  rating     TINYINT NOT NULL,
  comment    TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings (booking_id) ON DELETE CASCADE,
  CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE discussions (
  discussion_id INT AUTO_INCREMENT PRIMARY KEY,
  author_id     INT NOT NULL,
  course_id     INT NOT NULL,
  title         VARCHAR(200) NOT NULL,
  content       TEXT NOT NULL,
  status        ENUM('visible', 'removed') NOT NULL DEFAULT 'visible',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users (user_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses (course_id)
) ENGINE=InnoDB;

CREATE TABLE discussion_replies (
  reply_id      INT AUTO_INCREMENT PRIMARY KEY,
  discussion_id INT NOT NULL,
  author_id     INT NOT NULL,
  content       TEXT NOT NULL,
  status        ENUM('visible', 'removed') NOT NULL DEFAULT 'visible',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (discussion_id) REFERENCES discussions (discussion_id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE conversations (
  conversation_id INT AUTO_INCREMENT PRIMARY KEY,
  user_one_id     INT NOT NULL,
  user_two_id     INT NOT NULL,
  subject         VARCHAR(150) NOT NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_one_id) REFERENCES users (user_id) ON DELETE CASCADE,
  FOREIGN KEY (user_two_id) REFERENCES users (user_id) ON DELETE CASCADE,
  CHECK (user_one_id <> user_two_id)
) ENGINE=InnoDB;

CREATE TABLE messages (
  message_id      INT AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT NOT NULL,
  sender_id       INT NOT NULL,
  content         TEXT NOT NULL,
  is_read         BOOLEAN NOT NULL DEFAULT FALSE,
  sent_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversation_id) REFERENCES conversations (conversation_id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE resources (
  resource_id   INT AUTO_INCREMENT PRIMARY KEY,
  uploader_id   INT NOT NULL,
  course_id     INT NOT NULL,
  title         VARCHAR(200) NOT NULL,
  description   TEXT,
  resource_type ENUM('PDF', 'Video', 'Link', 'Notes') NOT NULL,
  url           VARCHAR(500) NOT NULL,
  status        ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploader_id) REFERENCES users (user_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses (course_id)
) ENGINE=InnoDB;

-- target_id refers to the table named by target_type
CREATE TABLE reports (
  report_id   INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  target_type ENUM('User', 'Discussion', 'Reply', 'Message', 'Resource') NOT NULL,
  target_id   INT NOT NULL,
  reason      VARCHAR(255) NOT NULL,
  details     TEXT,
  status      ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_by INT NULL,
  resolved_at DATETIME NULL,
  FOREIGN KEY (reporter_id) REFERENCES users (user_id) ON DELETE CASCADE,
  FOREIGN KEY (resolved_by) REFERENCES users (user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  message         VARCHAR(255) NOT NULL,
  link            VARCHAR(50),
  is_read         BOOLEAN NOT NULL DEFAULT FALSE,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample data

INSERT INTO users (user_id, full_name, email, password, role, account_status, created_at) VALUES
  (1,  'Alexa Canaria',          'alexa_sophia_canaria@dlsu.edu.ph', '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'active', '2026-09-01 08:00:00'),
  (2,  'Juan Miguel Rivera',     'juan_rivera@dlsu.edu.ph',          '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-09-01 08:05:00'),
  (3,  'Justin Adrian Cayabyab', 'justin_cayabyab@dlsu.edu.ph',      '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-09-01 08:10:00'),
  (4,  'Job Carlo Velasquez',    'job_velasquez@dlsu.edu.ph',        '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-09-01 08:15:00'),
  (5,  'Patricia Lim',           'patricia_lim@dlsu.edu.ph',         '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-09-02 09:00:00'),
  (6,  'Kevin Tan',              'kevin_tan@dlsu.edu.ph',            '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-10-25 14:00:00'),
  (7,  'Russel Jay Chavez',      'russel_chavez@dlsu.edu.ph',        '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'active', '2026-09-03 10:00:00'),
  (8,  'Miguel Santos',          'miguel_santos@dlsu.edu.ph',        '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'active', '2026-09-04 11:00:00'),
  (9,  'Bianca Reyes',           'bianca_reyes@dlsu.edu.ph',         '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'active', '2026-09-05 12:00:00'),
  (10, 'promo_account22',        'promo_account22@mail.com',         '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'active', '2026-10-08 23:00:00'),
  (11, 'Admin',                  'admin@codemate.local',             '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'admin', 'active', '2026-09-01 07:00:00'),
  (12, 'Carla Mendoza',          'carla_mendoza@dlsu.edu.ph',        '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-10-27 16:30:00'),
  (13, 'Paolo Garcia',           'paolo_garcia@dlsu.edu.ph',         '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'deactivated', '2026-09-10 13:00:00'),
  (14, 'Grace Villanueva',       'grace_villanueva@codemate.local',  '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'admin', 'active', '2026-09-01 07:10:00'),
  (15, 'Marco Dizon',            'marco_dizon@codemate.local',       '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'admin', 'active', '2026-09-01 07:20:00'),
  (16, 'Isabel Cruz',            'isabel_cruz@codemate.local',       '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'admin', 'active', '2026-09-01 07:30:00'),
  (17, 'Rafael Aquino',          'rafael_aquino@codemate.local',     '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'admin', 'active', '2026-09-01 07:40:00'),
  (18, 'Leo Bautista',           'leo_bautista@dlsu.edu.ph',         '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'deactivated', '2026-09-12 09:00:00'),
  (19, 'Nina Ramos',             'nina_ramos@dlsu.edu.ph',           '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'deactivated', '2026-09-15 14:20:00'),
  (20, 'spam_offers99',          'spam_offers99@mail.com',           '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutee', 'deactivated', '2026-10-05 02:10:00'),
  (21, 'Daniel Ong',             'daniel_ong@dlsu.edu.ph',           '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'deactivated', '2026-09-20 10:00:00'),
  (22, 'Sofia Navarro',          'sofia_navarro@dlsu.edu.ph',        '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-09-08 08:30:00'),
  (23, 'Ethan Go',               'ethan_go@dlsu.edu.ph',             '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-11-02 11:00:00'),
  (24, 'Hannah Lopez',           'hannah_lopez@dlsu.edu.ph',         '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-11-10 15:45:00'),
  (25, 'Paula Fernandez',        'paula_fernandez@dlsu.edu.ph',      '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-11-18 19:30:00'),
  (26, 'Vincent Yu',             'vincent_yu@dlsu.edu.ph',           '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-09-25 13:00:00'),
  (27, 'Andrea Castillo',        'andrea_castillo@dlsu.edu.ph',      '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-10-01 16:10:00'),
  (28, 'Ryan Torres',            'ryan_torres@dlsu.edu.ph',          '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-10-06 09:40:00'),
  (29, 'Mark Javier',            'mark_javier@dlsu.edu.ph',          '$2y$12$Ps3KOkD54tVxcMa9/mWFreu2dzV9GNAA7Y1wRSqlE8X4TveplD8Re', 'tutor', 'active', '2026-10-12 20:00:00');

INSERT INTO courses (course_id, course_code, course_name, status) VALUES
  (1, 'CCPROG3', 'Object-Oriented Programming',  'active'),
  (2, 'CCDSALG', 'Data Structures & Algorithms', 'active'),
  (3, 'CCINFOM', 'Database Systems',             'active'),
  (4, 'CCDSTRU', 'Discrete Mathematics',         'active'),
  (5, 'CSARCH1', 'Computer Architecture',        'active'),
  (6, 'CSAUTOM', 'Automata Theory',              'active'),
  (7, 'CCAPDEV', 'Web Application Development',  'active'),
  (8, 'CCPROG1', 'Logic Formulation',            'archived'),
  (9,  'CCPROG2', 'Programming with Structured Data Types', 'archived'),
  (10, 'CCICOMP', 'Introduction to Computing',              'archived'),
  (11, 'CSADPRG', 'Advanced Programming',                   'archived'),
  (12, 'STALGCM', 'Advanced Algorithms and Complexity',     'archived');

INSERT INTO tutor_profiles (tutor_id, bio, experience, verification_status, applied_at, reviewed_at) VALUES
  (2, '3rd-year CS student who likes breaking tricky algorithms into small, easy steps. I work through examples on a whiteboard and check understanding with short practice problems.',
      '2 terms of peer tutoring in Data Structures & Algorithms and Object-Oriented Programming. Led study groups before joining CodeMate.',
      'verified', '2026-09-01 08:05:00', '2026-09-02 10:00:00'),
  (3, 'I help students make sense of proofs and automata by drawing diagrams first and writing the formal steps second.',
      '1 year of peer tutoring in Discrete Mathematics and Automata Theory.',
      'verified', '2026-09-01 08:10:00', '2026-09-02 10:05:00'),
  (4, 'Database-focused student who explains queries and design using real examples. Patient with beginners.',
      '3 terms of tutoring Database Systems, including help with exam prep and project defenses.',
      'verified', '2026-09-01 08:15:00', '2026-09-02 10:10:00'),
  (5, 'I focus on writing clean code and understanding why a design works, not just memorizing patterns.',
      '1 term of tutoring Object-Oriented Programming. Active in course discussion threads.',
      'verified', '2026-09-02 09:00:00', '2026-09-03 09:00:00'),
  (6, 'New tutor waiting for verification. Comfortable with the basics of automata and discrete math.',
      'Recently started tutoring friends in Automata Theory.',
      'pending', '2026-10-25 14:00:00', NULL),
  (12, 'Full-stack hobbyist who enjoys debugging web apps with students one step at a time.',
       'Teaching assistant for a high school coding club for one year.',
       'pending', '2026-10-27 16:30:00', NULL),
  (21, 'Happy to help with data structures.',
       'No tutoring experience yet.',
       'rejected', '2026-09-20 10:00:00', '2026-09-22 09:00:00'),
  (22, 'Web development student who likes explaining how the browser and server talk to each other.',
       '2 terms as a CCAPDEV and CCINFOM peer tutor. Helped groups set up PHP and MySQL on the CCS Cloud.',
       'verified', '2026-09-08 08:30:00', '2026-09-09 10:00:00'),
  (23, 'Second-year student who enjoys walking through code line by line with a debugger.',
       'Helped classmates with Data Structures & Algorithms drills last term.',
       'pending', '2026-11-02 11:00:00', NULL),
  (24, 'I explain Java concepts using small everyday examples before writing any code.',
       'Peer mentor for incoming freshmen in Object-Oriented Programming.',
       'pending', '2026-11-10 15:45:00', NULL),
  (25, 'Interested in how computers work under the hood, from logic gates to pipelines.',
       'Reviewed Computer Architecture with a study group for one term.',
       'pending', '2026-11-18 19:30:00', NULL),
  (26, 'I can teach anything about Java.',
       'None listed.',
       'rejected', '2026-09-25 13:00:00', '2026-09-27 08:30:00'),
  (27, 'Good at databases.',
       'Shared my old project files with juniors.',
       'rejected', '2026-10-01 16:10:00', '2026-10-03 14:00:00'),
  (28, 'Web dev freelancer.',
       'Offers paid project help outside the platform.',
       'rejected', '2026-10-06 09:40:00', '2026-10-08 11:15:00'),
  (29, 'Math enthusiast.',
       'Application left mostly blank.',
       'rejected', '2026-10-12 20:00:00', '2026-10-14 10:30:00');

INSERT INTO tutor_courses (tutor_id, course_id) VALUES
  (2, 2), (2, 1),
  (3, 4), (3, 6),
  (4, 3), (4, 5),
  (5, 1), (5, 2),
  (6, 6), (6, 4),
  (12, 7), (12, 1),
  (21, 2),
  (22, 7), (22, 3),
  (23, 2),
  (24, 1),
  (25, 5),
  (26, 1),
  (27, 3),
  (28, 7),
  (29, 4);

INSERT INTO tutor_slots (slot_id, tutor_id, course_id, slot_date, start_time, end_time, mode, status) VALUES
  (1,  2, 2, '2026-12-01', '14:00', '15:00', 'Online',       'booked'),
  (2,  4, 3, '2026-10-10', '10:00', '11:00', 'Face-to-face', 'booked'),
  (3,  3, 4, '2026-10-08', '16:00', '17:00', 'Online',       'closed'),
  (4,  2, 1, '2026-12-03', '09:00', '10:00', 'Online',       'booked'),
  (5,  2, 1, '2026-10-20', '15:00', '16:00', 'Online',       'booked'),
  (6,  3, 4, '2026-09-28', '13:00', '14:00', 'Online',       'booked'),
  (7,  2, 2, '2026-10-02', '15:00', '16:00', 'Face-to-face', 'booked'),
  (8,  5, 1, '2026-12-04', '15:00', '16:00', 'Face-to-face', 'booked'),
  (9,  4, 5, '2026-12-02', '10:00', '11:00', 'Online',       'booked'),
  (10, 5, 2, '2026-10-16', '09:00', '10:00', 'Online',       'booked'),
  (11, 2, 2, '2026-12-07', '14:00', '15:00', 'Online',       'open'),
  (12, 2, 1, '2026-12-09', '10:00', '11:00', 'Face-to-face', 'open'),
  (13, 3, 6, '2026-12-08', '13:00', '14:00', 'Online',       'open'),
  (14, 3, 4, '2026-12-10', '16:00', '17:00', 'Online',       'open'),
  (15, 4, 3, '2026-12-10', '09:00', '10:00', 'Face-to-face', 'open'),
  (16, 4, 5, '2026-12-12', '13:00', '14:00', 'Online',       'open'),
  (17, 5, 2, '2026-12-11', '09:00', '10:00', 'Online',       'open'),
  (18, 2,  2, '2026-10-22', '14:00', '15:00', 'Online',       'closed'),
  (19, 3,  6, '2026-10-23', '13:00', '14:00', 'Online',       'closed'),
  (20, 4,  3, '2026-10-27', '10:00', '11:00', 'Face-to-face', 'closed'),
  (21, 5,  1, '2026-11-03', '15:00', '16:00', 'Online',       'closed'),
  (22, 22, 7, '2026-11-05', '16:00', '17:00', 'Online',       'closed'),
  (23, 2,  1, '2026-11-10', '09:00', '10:00', 'Face-to-face', 'closed'),
  (24, 3,  4, '2026-11-12', '16:00', '17:00', 'Online',       'closed'),
  (25, 4,  5, '2026-11-17', '13:00', '14:00', 'Online',       'closed'),
  (26, 22, 3, '2026-11-19', '10:00', '11:00', 'Face-to-face', 'closed'),
  (27, 2,  2, '2026-12-05', '10:00', '11:00', 'Online',       'booked'),
  (28, 3,  4, '2026-12-03', '14:00', '15:00', 'Online',       'booked'),
  (29, 4,  3, '2026-12-04', '09:00', '10:00', 'Face-to-face', 'booked'),
  (30, 22, 7, '2026-12-02', '15:00', '16:00', 'Online',       'booked'),
  (31, 5,  1, '2026-12-08', '15:00', '16:00', 'Online',       'booked'),
  (32, 22, 3, '2026-12-09', '13:00', '14:00', 'Face-to-face', 'booked');

INSERT INTO bookings (booking_id, slot_id, tutee_id, notes, status, created_at) VALUES
  (1,  1,  1, 'I want to go over AVL tree rotations.',          'confirmed', '2026-10-28 09:00:00'),
  (2,  2,  1, 'Help with normalization up to 3NF.',             'completed', '2026-10-05 13:00:00'),
  (3,  3,  1, 'Proof by induction practice.',                   'cancelled', '2026-10-03 15:00:00'),
  (4,  4,  1, 'Abstract classes and interfaces for the MP.',    'pending',   '2026-10-29 20:00:00'),
  (5,  5,  7, 'Polymorphism examples, please.',                 'completed', '2026-10-15 10:00:00'),
  (6,  6,  8, 'Relations and functions review.',                'completed', '2026-09-24 08:30:00'),
  (7,  7,  9, 'Practice problems on heaps.',                    'completed', '2026-09-29 17:00:00'),
  (8,  8,  7, 'Reviewing design patterns before the long exam.', 'pending',  '2026-10-30 08:15:00'),
  (9,  9,  8, 'Pipelining and hazards.',                        'pending',   '2026-10-29 11:45:00'),
  (10, 10, 9, 'Graph traversal questions.',                     'completed', '2026-10-12 19:20:00'),
  (11, 18, 7, 'Need help with hash table collisions.',          'cancelled', '2026-10-18 10:00:00'),
  (12, 19, 9, 'Converting an NFA to a DFA.',                    'cancelled', '2026-10-20 13:30:00'),
  (13, 20, 8, 'SQL joins practice before the quiz.',            'cancelled', '2026-10-24 16:00:00'),
  (14, 21, 1, 'Exception handling in Java.',                    'cancelled', '2026-10-30 09:15:00'),
  (15, 22, 7, 'Routing in a PHP MVC project.',                  'declined',  '2026-11-01 18:00:00'),
  (16, 23, 9, 'Inheritance vs composition.',                    'declined',  '2026-11-06 12:00:00'),
  (17, 24, 1, 'Pigeonhole principle problems.',                 'declined',  '2026-11-08 20:45:00'),
  (18, 25, 8, 'Cache mapping exercises.',                       'declined',  '2026-11-13 08:00:00'),
  (19, 26, 7, 'ER diagram review for our project.',             'declined',  '2026-11-15 14:10:00'),
  (20, 27, 7, 'Dynamic programming basics.',                    'confirmed', '2026-11-20 10:30:00'),
  (21, 28, 9, 'Counting and combinatorics.',                    'confirmed', '2026-11-21 15:00:00'),
  (22, 29, 1, 'Indexes and query optimization.',                'confirmed', '2026-11-22 11:20:00'),
  (23, 30, 8, 'Sessions and cookies in PHP.',                   'confirmed', '2026-11-23 17:40:00'),
  (24, 31, 9, 'Generics and collections in Java.',              'pending',   '2026-11-24 09:00:00'),
  (25, 32, 1, 'Stored procedures vs app logic.',                'pending',   '2026-11-25 19:00:00');

INSERT INTO reviews (review_id, booking_id, rating, comment, created_at) VALUES
  (1, 2,  5, 'Very clear explanations and helpful examples.',                          '2026-10-10 12:00:00'),
  (2, 5,  5, 'Patient and easy to follow. The examples made interfaces click for me.', '2026-10-20 17:00:00'),
  (3, 6,  5, 'The diagrams helped a lot with proofs.',                                 '2026-09-28 15:00:00'),
  (4, 7,  4, 'Good pacing and useful practice problems.',                              '2026-10-02 17:30:00'),
  (5, 10, 4, 'Explained BFS and DFS well, though we ran a bit over time.',             '2026-10-16 11:00:00');

INSERT INTO discussions (discussion_id, author_id, course_id, title, content, status, created_at) VALUES
  (1, 1, 2, 'Why is my recursive merge sort stack overflowing?',
      'I''m implementing merge sort recursively in C. It works for small arrays, but it crashes with a stack overflow once the array has around 100,000 elements. Is my base case wrong, or should I be doing this iteratively?',
      'visible', '2026-10-08 09:30:00'),
  (2, 4, 4, 'Injective vs bijective, intuitively?',
      'I keep mixing up injective and bijective functions when writing proofs. Is there an intuitive way to remember the difference?',
      'visible', '2026-10-06 14:00:00'),
  (3, 8, 1, 'Full MP answer key, message me',
      'I have the complete solution for the CCPROG3 machine project. Message me if you want a copy before the deadline.',
      'visible', '2026-10-10 21:15:00'),
  (4, 7, 3, 'When should I denormalize a table?',
      'Our CCINFOM project has a table that gets joined in almost every query. Is it ever okay to break 3NF for performance?',
      'visible', '2026-10-15 16:40:00'),
  (5, 9, 7, 'PHP or Node.js for the MP backend?',
      'Our group is choosing a backend stack for Phase 2. What are the trade-offs when deploying on the CCS Cloud server?',
      'visible', '2026-10-28 19:05:00'),
  (6, 1, 1, 'Abstract class vs interface in Java',
      'When should I pick an abstract class over an interface? Both seem to work for my MP.',
      'visible', '2026-10-12 08:50:00'),
  (7, 10, 7, 'Cheap MP commissions, DM me',
      'I will code your whole machine project for a fee. Fast turnaround, message me for rates.',
      'removed', '2026-10-18 22:30:00'),
  (8, 20, 2, 'FREE gift cards, click here',
      'Get free gift cards today, just sign up at the link in my profile!!!',
      'removed', '2026-10-05 02:30:00'),
  (9, 18, 3, 'Leaked CCINFOM quiz answers',
      'Posting the answers to tomorrow''s quiz, got them from someone in the other section.',
      'removed', '2026-10-21 23:00:00'),
  (10, 8, 4, 'Who wants to split the take-home exam answers?',
      'Let''s each answer two items of the take-home exam and share them in a group chat.',
      'removed', '2026-11-04 21:40:00'),
  (11, 19, 6, 'Prof''s personal number',
      'Posting the prof''s personal mobile number here so everyone can text complaints.',
      'removed', '2026-11-09 18:25:00');

INSERT INTO discussion_replies (reply_id, discussion_id, author_id, content, status, created_at) VALUES
  (1, 1, 2,  'Check your base case first. The recursion should stop when the subarray has 0 or 1 elements. If the range never shrinks, the recursion never ends.', 'visible', '2026-10-08 11:00:00'),
  (2, 1, 5,  'Also watch how you compute the middle index. Use left + (right - left) / 2 and make sure both halves are strictly smaller than the original.', 'visible', '2026-10-09 10:20:00'),
  (3, 2, 3,  'Injective means no two inputs share an output. Bijective means injective and every output is used, so the two sets pair up perfectly.', 'visible', '2026-10-07 09:00:00'),
  (4, 4, 4,  'Only when reads heavily outnumber writes and you have measured the join as the bottleneck. Keep the normalized design as the source of truth.', 'visible', '2026-10-16 10:15:00'),
  (5, 5, 2,  'Both work. The CCS Cloud guide sets up Apache, PHP and MariaDB, so a PHP stack needs the least extra setup.', 'visible', '2026-10-28 21:30:00'),
  (6, 6, 5,  'Use an abstract class when subclasses share state or code. Use an interface for a contract that unrelated classes can implement.', 'visible', '2026-10-12 12:00:00'),
  (7, 1, 1,  'Thanks! The range was not shrinking when the array had two elements.', 'visible', '2026-10-09 18:45:00'),
  (8, 1, 10, 'Skip the studying, get free premium accounts at the link in my profile!!!', 'visible', '2026-10-09 23:10:00'),
  (9,  2, 20, 'Buy followers and likes cheap, link in my profile!', 'removed', '2026-10-07 03:00:00'),
  (10, 4, 10, 'DM me for the full project files, 500 pesos only.', 'removed', '2026-10-16 22:00:00'),
  (11, 5, 18, 'Just copy last year''s repo, nobody checks anyway.', 'removed', '2026-10-29 08:10:00'),
  (12, 6, 19, 'Stupid question honestly, just read the slides.', 'removed', '2026-10-13 07:30:00'),
  (13, 1, 20, 'Win a free laptop, link in bio!!!', 'removed', '2026-10-10 01:15:00');

INSERT INTO conversations (conversation_id, user_one_id, user_two_id, subject, created_at) VALUES
  (1, 1, 4, 'Data Structures & Algorithms', '2026-10-14 14:15:00'),
  (2, 1, 5, 'Object-Oriented Programming',  '2026-10-13 09:45:00'),
  (3, 6, 4, 'Database Systems',             '2026-10-14 15:45:00'),
  (4, 3, 2, 'Discrete Mathematics',         '2026-10-14 11:10:00'),
  (5, 3, 5, 'Data Structures & Algorithms', '2026-10-12 17:15:00'),
  (6, 1, 2, 'General Inquiries',            '2026-10-11 12:45:00');

INSERT INTO messages (message_id, conversation_id, sender_id, content, is_read, sent_at) VALUES
  (1,  1, 1, 'Hi Carlo! Are you available to review AVL tree balance factors tomorrow?', TRUE, '2026-10-14 14:15:00'),
  (2,  1, 4, 'Hi Alexa! Yes, I can do 2 PM. We can trace left-right and right-left rotations step-by-step.', FALSE, '2026-10-14 14:22:00'),
  (3,  1, 1, 'See you at the consultation session tomorrow at 2 PM!', FALSE, '2026-10-14 14:30:00'),
  (4,  2, 1, 'Hi Patricia, when should I prefer abstract classes over interfaces in Java?', TRUE, '2026-10-13 09:45:00'),
  (5,  2, 5, 'Use abstract classes when subclasses need to share common base state or non-public methods. Interfaces are best for defining decoupled contracts across unrelated classes.', TRUE, '2026-10-13 10:02:00'),
  (6,  2, 1, 'Thanks for clarifying the polymorphism abstract class question.', TRUE, '2026-10-13 10:15:00'),
  (7,  3, 6, 'Hello Carlo! I am currently working on the ERD normalisation homework.', FALSE, '2026-10-14 15:45:00'),
  (8,  3, 6, 'Can you verify if this schema satisfies 3NF?', FALSE, '2026-10-14 16:00:00'),
  (9,  4, 3, 'Hi JM, something came up for Thursday''s study group.', TRUE, '2026-10-14 11:10:00'),
  (10, 4, 3, 'Can we reschedule our session to Friday afternoon?', TRUE, '2026-10-14 11:20:00'),
  (11, 5, 3, 'Hi Patricia, do you have extra recursion drills before next week''s exam?', TRUE, '2026-10-12 17:15:00'),
  (12, 5, 5, 'I have uploaded the practice recursion set to the shared resources tab.', TRUE, '2026-10-12 17:40:00'),
  (13, 6, 1, 'Good afternoon JM, I booked a 1-hour slot for Graph Traversal algorithms.', TRUE, '2026-10-11 12:45:00'),
  (14, 6, 2, 'The booking has been confirmed. Let me know which topics you want to prioritize.', FALSE, '2026-10-11 13:00:00');

INSERT INTO resources (resource_id, uploader_id, course_id, title, description, resource_type, url, status, created_at) VALUES
  (1, 2, 2, 'AVL Tree Rotation Cheat Sheet', 'One-page summary of the four rotation cases with diagrams.', 'PDF', 'https://example.com/codemate/avl-rotations.pdf', 'approved', '2026-10-01 10:00:00'),
  (2, 4, 3, 'Normalization Walkthrough', 'Video taking one table from 1NF to 3NF step by step.', 'Video', 'https://example.com/codemate/normalization', 'approved', '2026-10-03 15:30:00'),
  (3, 6, 3, 'CCINFOM Finals Questions (Term 1)', 'PDF upload described as "the actual finals questions from last term".', 'PDF', 'https://example.com/codemate/ccinfom-finals.pdf', 'pending', '2026-10-08 22:00:00'),
  (4, 5, 1, 'Java OOP Interfaces Notes', 'Student-made notes on interfaces and abstract classes with short examples.', 'Notes', 'https://example.com/codemate/java-interfaces', 'pending', '2026-10-29 09:10:00'),
  (5, 3, 4, 'Proof Techniques Primer', 'Direct proof, contradiction and induction with worked examples.', 'PDF', 'https://example.com/codemate/proof-primer.pdf', 'approved', '2026-09-20 13:00:00'),
  (6, 5, 2, 'Recursion Practice Set', 'Twenty recursion drills with answers, from easy to exam level.', 'PDF', 'https://example.com/codemate/recursion-set.pdf', 'approved', '2026-10-12 17:35:00'),
  (7, 2, 7, 'PHP MVC Starter Guide', 'How to split a small PHP site into models, views and controllers.', 'Link', 'https://example.com/codemate/php-mvc', 'approved', '2026-10-27 18:00:00'),
  (8,  3,  6, 'DFA Minimization Video', 'Step-by-step minimization of a five-state DFA.', 'Video', 'https://example.com/codemate/dfa-minimization', 'approved', '2026-10-24 14:00:00'),
  (9,  22, 7, 'Sessions and Cookies in PHP', 'Short video on how PHP sessions use a cookie to remember the user.', 'Video', 'https://example.com/codemate/php-sessions', 'approved', '2026-11-06 10:00:00'),
  (10, 4,  5, 'Pipelining Explained', 'Video on the five pipeline stages and common hazards.', 'Video', 'https://example.com/codemate/pipelining', 'pending', '2026-11-20 16:30:00'),
  (11, 26, 1, 'Full MP Solution Walkthrough', 'Screen recording of a complete machine project solution.', 'Video', 'https://example.com/codemate/mp-solution', 'rejected', '2026-09-28 22:00:00'),
  (12, 2,  2, 'Algorithm Visualizer', 'Website that animates sorting, trees and graph algorithms.', 'Link', 'https://example.com/codemate/visualizer', 'approved', '2026-10-19 09:30:00'),
  (13, 5,  1, 'Java Documentation Guide', 'How to read the official Java API docs quickly.', 'Link', 'https://example.com/codemate/java-docs', 'pending', '2026-11-21 13:00:00'),
  (14, 27, 3, 'Answer Key Drive Folder', 'Link to a folder of past exam answer keys.', 'Link', 'https://example.com/codemate/answer-keys', 'rejected', '2026-10-02 23:30:00'),
  (15, 28, 7, 'Free Hosting Promo', 'Sign up through my referral link for free hosting.', 'Link', 'https://example.com/codemate/hosting-promo', 'rejected', '2026-10-07 12:00:00'),
  (16, 22, 3, 'SQL Joins Notes', 'Inner, left and right joins with small example tables.', 'Notes', 'https://example.com/codemate/sql-joins', 'approved', '2026-11-08 08:45:00'),
  (17, 3,  4, 'Graph Theory Notes', 'Definitions and short proofs on paths, cycles and trees.', 'Notes', 'https://example.com/codemate/graph-notes', 'pending', '2026-11-23 20:00:00'),
  (18, 21, 2, 'Copied Lecture Slides', 'The professor''s full slide deck re-uploaded as notes.', 'Notes', 'https://example.com/codemate/copied-slides', 'rejected', '2026-09-21 18:00:00'),
  (19, 29, 4, 'Exam Cheat Notes', 'Notes formatted to be brought into the exam.', 'Notes', 'https://example.com/codemate/cheat-notes', 'rejected', '2026-10-13 21:10:00'),
  (20, 22, 7, 'HTTP Status Codes Cheat Sheet', 'The status codes a web app uses most, like 200, 302, 403, 404 and 419.', 'PDF', 'https://example.com/codemate/http-status.pdf', 'approved', '2026-11-12 09:00:00');

INSERT INTO reports (report_id, reporter_id, target_type, target_id, reason, details, status, created_at, resolved_by, resolved_at) VALUES
  (1, 1, 'User',      10, 'Spam account', 'Posted an advertising link as a reply in my merge sort discussion.', 'pending', '2026-10-09 23:35:00', NULL, NULL),
  (2, 2, 'Discussion', 3, 'Academic integrity: sharing answer keys', 'Post offers a full machine project solution.', 'pending', '2026-10-10 22:00:00', NULL, NULL),
  (3, 1, 'Reply',      8, 'Spam', 'Advertising link unrelated to the course.', 'pending', '2026-10-09 23:30:00', NULL, NULL),
  (4, 5, 'Resource',   3, 'Possible leaked exam material', 'Upload claims to be last term''s finals.', 'pending', '2026-10-09 08:00:00', NULL, NULL),
  (5, 4, 'User',       8, 'Offering to sell machine project answers', 'Posted "Full MP answer key, message me" in the CCPROG3 discussions.', 'pending', '2026-10-14 18:00:00', NULL, NULL),
  (6, 3, 'User',       1, 'Cancelled less than an hour before our session', 'Our Oct 8 CCDSTRU session on proof by induction was cancelled at the last minute.', 'resolved', '2026-10-09 12:00:00', 11, '2026-10-10 09:00:00'),
  (7, 5, 'User',       9, 'Left the consultation session early without explanation', NULL, 'pending', '2026-10-17 10:00:00', NULL, NULL),
  (8, 6, 'Discussion', 2, 'Off-topic question', 'Seems like general math, not a course question.', 'dismissed', '2026-10-07 11:00:00', 11, '2026-10-07 15:00:00'),
  (9,  1, 'User',       20, 'Spam account', 'Posts gift card and laptop scam links.', 'resolved', '2026-10-10 01:30:00', 11, '2026-10-10 08:00:00'),
  (10, 2, 'Discussion',  7, 'Selling machine project commissions', 'Offers to code the whole MP for a fee.', 'resolved', '2026-10-18 23:00:00', 14, '2026-10-19 09:00:00'),
  (11, 7, 'Discussion',  9, 'Leaked quiz answers', 'Claims to post tomorrow''s CCINFOM quiz answers.', 'resolved', '2026-10-21 23:20:00', 15, '2026-10-22 07:30:00'),
  (12, 9, 'Discussion',  4, 'Not a course question', NULL, 'dismissed', '2026-10-16 12:00:00', 14, '2026-10-16 15:00:00'),
  (13, 3, 'Reply',      10, 'Selling project files', 'Reply offers the full project for 500 pesos.', 'resolved', '2026-10-16 22:30:00', 11, '2026-10-17 08:15:00'),
  (14, 8, 'Reply',       4, 'I disagree with this advice', NULL, 'dismissed', '2026-10-17 09:00:00', 15, '2026-10-17 13:00:00'),
  (15, 1, 'Reply',       6, 'Might be copied from a website', 'The wording looks like it came from an online article.', 'pending', '2026-11-20 10:00:00', NULL, NULL),
  (16, 9, 'Reply',       5, 'Off-topic', NULL, 'dismissed', '2026-10-29 09:00:00', 16, '2026-10-29 14:00:00'),
  (17, 4, 'Message',     1, 'Messaged late at night', NULL, 'dismissed', '2026-10-14 22:00:00', 16, '2026-10-15 09:00:00'),
  (18, 2, 'Message',     9, 'Keeps rescheduling our sessions', 'Third time this month the session was moved.', 'pending', '2026-11-18 11:00:00', NULL, NULL),
  (19, 4, 'Message',     7, 'Asking me to answer their homework', 'Wants me to check the whole graded homework.', 'pending', '2026-11-19 16:00:00', NULL, NULL),
  (20, 5, 'Message',    11, 'Asking for exam answers', 'Asked for drills that look like the actual exam.', 'pending', '2026-11-22 18:30:00', NULL, NULL),
  (21, 1, 'Message',    14, 'Reported by mistake', NULL, 'dismissed', '2026-10-11 13:10:00', 17, '2026-10-11 16:00:00'),
  (22, 1, 'Resource',   11, 'Full MP solution', 'The video shows the complete machine project answer.', 'resolved', '2026-09-29 08:00:00', 11, '2026-09-29 12:00:00'),
  (23, 7, 'Resource',   14, 'Exam answer keys', 'Links to past exam answer keys.', 'resolved', '2026-10-03 07:45:00', 17, '2026-10-03 10:00:00'),
  (24, 2, 'Resource',   10, 'Video is blurry', 'Hard to read the diagrams.', 'pending', '2026-11-21 09:30:00', NULL, NULL),
  (25, 9, 'Resource',   13, 'Link might be broken', NULL, 'pending', '2026-11-24 14:00:00', NULL, NULL);

INSERT INTO notifications (notification_id, user_id, message, link, is_read, created_at) VALUES
  (1, 1,  'Your CCPROG3 booking request is pending.',                      'my-bookings',         FALSE, '2026-10-29 20:00:00'),
  (2, 1,  'Juan Miguel Rivera confirmed your CCDSALG booking for Dec 1.',  'my-bookings',         FALSE, '2026-10-28 10:00:00'),
  (3, 1,  'Juan Miguel Rivera sent you a message.',                        'messages',            TRUE,  '2026-10-11 13:00:00'),
  (4, 2,  'New booking request from Alexa Canaria.',                       'tutor-bookings',      FALSE, '2026-10-29 20:00:00'),
  (5, 2,  'Russel Jay Chavez left you a 5-star review.',                   'tutor-reviews',       TRUE,  '2026-10-20 17:00:00'),
  (6, 5,  'New booking request from Russel Jay Chavez.',                   'tutor-bookings',      FALSE, '2026-10-30 08:15:00'),
  (7, 11, 'Carla Mendoza submitted a tutor verification request.',         'admin-verify-tutors', FALSE, '2026-10-27 16:30:00'),
  (8, 11, 'A discussion post was flagged for moderation.',                 'admin-moderation',    FALSE, '2026-10-10 22:00:00'),
  (9, 11, 'A new report about a user was submitted.',                      'admin-reports',       TRUE,  '2026-10-17 10:00:00'),
  (10, 7,  'Juan Miguel Rivera confirmed your CCDSALG booking for Dec 5.',  'my-bookings',         TRUE,  '2026-11-20 12:00:00'),
  (11, 9,  'Your report about a reply was reviewed. No action was needed.', 'reports',             TRUE,  '2026-10-29 14:00:00');
