#!/usr/bin/env php

<?php $init_modules = array(); require __DIR__ . '/includes/init.php'; $ipv4 = "10.10.160."; $doesntexist = "doesn't exist!"; for ($hosts = 1; $hosts < 254; $hosts = $hosts + 1) { /*echo $ipv4.$hosts;*/ $host = $ipv4.$hosts; $id = getidbyname($host); if ($id) { echo delete_device($id)."\n"; } else { echo $host." - ".$doesntexist."\n"; } } ?>

