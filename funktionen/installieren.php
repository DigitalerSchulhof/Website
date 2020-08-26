<?php

$DBS->anfrage("CREATE TABLE `website_einstellungen` (`id` bigint(255) unsigned NOT NULL,`inhalt` varbinary(500) NOT NULL,`wert` varbinary(5000) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
$DBS->anfrage("INSERT INTO `website_einstellungen` (`id`, `inhalt`, `wert`) VALUES ('0', ['Standardsprache'], ['DE']);");

?>