

-- --------------------------------------------------------
--
-- Table structure for table `plugin`
--

CREATE TABLE IF NOT EXISTS plugin (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(128) NOT NULL,
  version VARCHAR(9) NOT NULL,
  created TIMESTAMP NOT NULL,
  UNIQUE (name)
);

