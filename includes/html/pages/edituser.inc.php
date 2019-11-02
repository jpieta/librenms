
<?php

use App\Models\User;
use LibreNMS\Authentication\LegacyAuth;

$no_refresh = true;

require 'includes/html/javascript-interfacepicker.inc.php';

echo "<div style='margin: 10px;'>";

$pagetitle[] = 'Edit user';

if (! Auth::user()->hasGlobalAdmin()) {
    include 'includes/html/error-no-perm.inc.php';
} else {
    if ($vars['user_id'] && !$vars['edit']) {
        /** @var User $user */
        $user = User::find($vars['user_id']);
        $user_data = $user->toArray(); // for compatibility with current code

        echo '<p><h2>'.$user_data['realname']."</h2></p>";
        // Perform actions if requested
        if ($vars['action'] == 'deldevperm') {
            if (dbFetchCell('SELECT COUNT(*) FROM devices_perms WHERE `device_id` = ? AND `user_id` = ?', array($vars['device_id'], $user_data['user_id']))) {
                dbDelete('devices_perms', '`device_id` =  ? AND `user_id` = ?', array($vars['device_id'], $user_data['user_id']));
            }
        }

        if ($vars['action'] == 'adddevperm') {
            if (!dbFetchCell('SELECT COUNT(*) FROM devices_perms WHERE `device_id` = ? AND `user_id` = ?', array($vars['device_id'], $user_data['user_id']))) {
                dbInsert(array('device_id' => $vars['device_id'], 'user_id' => $user_data['user_id']), 'devices_perms');
            }
        }

        if ($vars['action'] == 'delifperm') {
            if (dbFetchCell('SELECT COUNT(*) FROM ports_perms WHERE `port_id` = ? AND `user_id` = ?', array($vars['port_id'], $user_data['user_id']))) {
                dbDelete('ports_perms', '`port_id` =  ? AND `user_id` = ?', array($vars['port_id'], $user_data['user_id']));
            }
        }

        if ($vars['action'] == 'addifperm') {
            if (!dbFetchCell('SELECT COUNT(*) FROM ports_perms WHERE `port_id` = ? AND `user_id` = ?', array($vars['port_id'], $user_data['user_id']))) {
                dbInsert(array('port_id' => $vars['port_id'], 'user_id' => $user_data['user_id']), 'ports_perms');
            }
        }

        if ($vars['action'] == 'delbillperm') {
            if (dbFetchCell('SELECT COUNT(*) FROM bill_perms WHERE `bill_id` = ? AND `user_id` = ?', array($vars['bill_id'], $user_data['user_id']))) {
                dbDelete('bill_perms', '`bill_id` =  ? AND `user_id` = ?', array($vars['bill_id'], $user_data['user_id']));
            }
        }

        if ($vars['action'] == 'addbillperm') {
            if (!dbFetchCell('SELECT COUNT(*) FROM bill_perms WHERE `bill_id` = ? AND `user_id` = ?', array($vars['bill_id'], $user_data['user_id']))) {
                dbInsert(array('bill_id' => $vars['bill_id'], 'user_id' => $user_data['user_id']), 'bill_perms');
            }
        }

if ($vars['action'] == 'addgroupperm') 
{
   // Build array of existing entries
    foreach (dbFetchRows('SELECT * FROM `device_group_device` WHERE `device_group_id` = ?', array($vars['group_id'])) as $entry) 
	{
        $device_ids_db[$entry['device_id']] = $entry;
   	}

//	if (is_array($device_ids_db)) 
//	{

        foreach ($device_ids_db as $device_id => $device_id_db) 
		{
//            $device_id_db = array_shift($device_id_db);

            if (!dbFetchCell('SELECT COUNT(*) FROM devices_perms WHERE `device_id` = ? AND `user_id` = ?', array($device_id, $vars['user_id']))) 
			{
                dbInsert(array('device_id' => $device_id, 'user_id' => $vars['user_id']), 'devices_perms');
			}
		}
//	}
}

        echo '<div class="row">
           <div class="col-md-4">';

        // Display devices this users has access to
        echo '<h3>Device Access</h3>';

        echo "<div class='panel panel-default panel-condensed'>
            <table class='table table-hover table-condensed table-striped'>
              <tr>
                <th>Device</th>
                <th>Action</th>
              </tr>";

        $device_perms = dbFetchRows('SELECT * from devices_perms as P, devices as D WHERE `user_id` = ? AND D.device_id = P.device_id', array($user_data['user_id']));
        foreach ($device_perms as $device_perm) {
            echo '<tr><td><strong>'.format_hostname($device_perm)."</td><td> <a href='edituser/action=deldevperm/user_id=".$vars['user_id'].'/device_id='.$device_perm['device_id']."'><i class='fa fa-trash fa-lg icon-theme' aria-hidden='true'></i></a></strong></td></tr>";
            $access_list[] = $device_perm['device_id'];
            $permdone      = 'yes';
        }

        echo '</table>
          </div>';

        if (!$permdone) {
            echo 'None Configured';
        }

        // Display devices this user doesn't have access to
        echo '<h4>Grant access to new device</h4>';
        echo "<form class='form-inline' role='form' method='post' action=''>
            <input type='hidden' value='".$user_data['user_id']."' name='user_id'>
            <input type='hidden' value='edituser' name='page'>
            <input type='hidden' value='adddevperm' name='action'>
            <div class='form-group'>
              <label class='sr-only' for='device_id'>Device</label>
              <select name='device_id' id='device_id' class='form-control'>";

        $devices = dbFetchRows('SELECT * FROM `devices` ORDER BY hostname');
        foreach ($devices as $device) {
            unset($done);
            foreach ($access_list as $ac) {
                if ($ac == $device['device_id']) {
                    $done = 1;
                }
            }

            if (!$done) {
                echo "<option value='".$device['device_id']."'>".format_hostname($device, $device['hostname']).'</option>';
            }
        }

        echo "</select>
           </div>
           <button type='submit' class='btn btn-default' name='Submit'>Add</button></form>";

        echo "</div>
          <div class='col-md-4'>";
        echo '<h3>Interface Access</h3>';

        $interface_perms = dbFetchRows('SELECT * from ports_perms as P, ports as I, devices as D WHERE `user_id` = ? AND I.port_id = P.port_id AND D.device_id = I.device_id', array($user_data['user_id']));

        echo "<div class='panel panel-default panel-condensed'>
            <table class='table table-hover table-condensed table-striped'>
              <tr>
                <th>Interface name</th>
                <th>Action</th>
              </tr>";
        foreach ($interface_perms as $interface_perm) {
            echo '<tr>
              <td>
                <strong>'.$interface_perm['hostname'].' - '.$interface_perm['ifDescr'].'</strong>'.''.display($interface_perm['ifAlias'])."
              </td>
              <td>
                &nbsp;&nbsp;<a href='edituser/action=delifperm/user_id=".$user_data['user_id'].'/port_id='.$interface_perm['port_id']."'><i class='fa fa-trash fa-lg icon-theme' aria-hidden='true'></i></a>
              </td>
            </tr>";
            $ipermdone = 'yes';
        }

        echo '</table>
          </div>';

        if (!$ipermdone) {
            echo 'None Configured';
        }

        // Display devices this user doesn't have access to
        echo '<h4>Grant access to new interface</h4>';

        echo "<form action='' method='post' class='form-horizontal' role='form'>
        <input type='hidden' value='".$user_data['user_id']."' name='user_id'>
        <input type='hidden' value='edituser' name='page'>
        <input type='hidden' value='addifperm' name='action'>
        <div class='form-group'>
          <label for='device' class='col-sm-2 control-label'>Device: </label>
          <div class='col-sm-10'>
            <select id='device' class='form-control' name='device' onchange='getInterfaceList(this)'>
          <option value=''>Select a device</option>";

        foreach ($devices as $device) {
            unset($done);
            foreach ($access_list as $ac) {
                if ($ac == $device['device_id']) {
                    $done = 1;
                }
            }

            if (!$done) {
                echo "<option value='".$device['device_id']."'>".format_hostname($device, $device['hostname']).'</option>';
            }
        }

        echo "</select>
          </div>
          </div>
          <div class='form-group'>
            <label for='port_id' class='col-sm-2 control-label'>Interface: </label>
            <div class='col-sm-10'>
              <select class='form-control' id='port_id' name='port_id'>
              </select>
            </div>
         </div>
         <div class='form-group'>
           <div class='col-sm-12'>
             <button type='submit' class='btn btn-default' name='Submit' value='Add'>Add</button>
           </div>
         </div>
       </form>";

        echo "</div>
          <div class='col-md-4'>";



        echo '<h3>Group Access</h3>';

        $group_perms = dbFetchRows('SELECT * FROM `device_groups` ORDER BY `name`');
//        $group_perms = dbFetchRows('SELECT * from device_group_device AS D, device_groups AS P WHERE D.device_group_id = P.id', array($user_data['user_id']));
//        $group_perms = dbFetchRows('SELECT * from device_groups AS D, device_group_device AS P WHERE D.id = P.device_group_id', array($user_data['user_id']));

        echo "<div class='panel panel-default panel-condensed'>
            <table class='table table-hover table-condensed table-striped'>
            <tr>
              <th>Group name</th>
              <th>Action</th>
            </tr>";
        foreach ($group_perms as $group_perm) {
//		if(!$temp_group_id == $group_perm['id'])
//			{
//            echo '<tr>
//             <td>
//                <strong>'.$group_perm['name']."</strong></td><td width=50><i class='fa fa-trash fa-lg icon-theme' aria-hidden='true'></i></a>
//              </td>
//            </tr>";
            $group_access_list[] = $group_perm['id'];
            $bpermdone = 'yes';
//			}

//                  <strong>'.$group['name']."</strong></td><td width=50>&nbsp;&nbsp;<a href='edituser/action=delgroupperm/user_id=".$vars['user_id'].'/group_id='.$group['id']."'><i class='fa fa-trash fa-lg icon-theme' aria-hidden='true'></i></a>
//              "<option value='".$group['id']."'>".$group['name'].'</option>';

//$temp_group_id = $group_perm['id'];
        }
        echo '</table>
          </div>';
        if (!$bpermdone) {
            echo 'None Configured';
        }
        // Display devices this user doesn't have access to
        echo '<h4>Grant access to new group</h4>';
        echo "<form method='post' action='' class='form-inline' role='form'>
            " . csrf_field() . "
            <input type='hidden' value='".$user_data['user_id']."' name='user_id'>
            <input type='hidden' value='edituser' name='page'>
            <input type='hidden' value='addgroupperm' name='action'>
            <div class='form-group'>
              <label class='sr-only' for='group_id'>Group</label>
              <select name='group_id' class='form-control' id='group_id'>";
        $groups = dbFetchRows('SELECT * FROM `device_groups` ORDER BY `name`');
        foreach ($groups as $group) {
            unset($done);
            foreach ($group_access_list as $ac) {
//                if ($ac == $group['group_id']) {
//                   $done = 1;
//                }
            }
//            if (!$done) {
                echo "<option value='".$group['id']."'>".$group['name'].'</option>';
//            }
        }
        echo "</select>
          </div>
          <button type='submit' class='btn btn-default' name='Submit' value='Add'>Add</button>
        </form>
        </div>";
    } else {
        echo '<script>window.location.replace("' . url('users') . '");</script>';
    }//end if
}//end if



echo '</div>';
