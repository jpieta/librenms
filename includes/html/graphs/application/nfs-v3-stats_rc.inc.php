<?php
require 'includes/html/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Reply cache';
$unitlen       = 15;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename  = get_rrd_dir($device['hostname']).'/app-nfs-stats-'.$app['app_id'].'.rrd';
$array = array(
    'rc_hits' => array('descr' => 'hits','colour' => '2B9220',),
    'rc_misses' => array('descr' => 'misses','colour' => 'B36326',),
    'rc_nocache' => array('descr' => 'nocache','colour' => 'B0262D',),
);

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
