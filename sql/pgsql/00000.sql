

-- --------------------------------------------------------
--
-- Table structure for table `plugin`
--

CREATE TABLE IF NOT EXISTS plugin (
  id SERIAL PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  version VARCHAR(9) NOT NULL,
  created TIMESTAMP NOT NULL,
  CONSTRAINT plugin_name UNIQUE (name)
);

