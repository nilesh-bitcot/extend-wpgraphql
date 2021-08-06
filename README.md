# extend-wpgraphql

in this plugin we add some sample graphql endpoints so that you can understand and make your own custom things.

please create following tables in db first.

CREATE TABLE `wpgraphql_custom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `wpgraphql_custom`
  ADD PRIMARY KEY (`id`);

