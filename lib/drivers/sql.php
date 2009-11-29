<?php

/*
 +-----------------------------------------------------------------------+
 | lib/drivers/sql.php                                                   |
 |                                                                       |
 | Copyright (C) 2009 Boris HUISGEN <bhuisgen@hbis.fr>                   |
 | Licensed under the GNU GPL                                            |
 +-----------------------------------------------------------------------+
 */

/*
 * Read driver function.
 *
 * @param array $data the array of data to get and set.
 *
 * @return integer the status code.
 */
function vacation_read(array &$data)
{
	$rcmail = rcmail::get_instance();

	if ($dsn = $rcmail->config->get('vacation_db_dsn'))
	{
		if (is_array($dsn) && empty($dsn['new_link']))
		{
			$dsn['new_link'] = true;
		}
		else if (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn))
		{
			$dsn .= '?new_link=true';
		}
		
		$db = new rcube_mdb2($dsn, '', FALSE);
		$db->set_debug((bool)$rcmail->config->get('sql_debug'));
		$db->db_connect('w');
	}
	else
	{
		$db = $rcmail->get_dbh();
	}

	if ($err = $db->is_error())
	{
		return PLUGIN_ERROR_CONNECT;
	}

	foreach($rcmail->config->get('vacation_sql_read') as $query)
	{
		$search = array('%username', '%email_local', '%email_domain', '%email',
							'%vacation_enable', '%vacation_subject',
							'%vacation_message');
		$replace = array($db->quote($data['username']), $db->quote($data['email_local']),
		$db->quote($data['email_domain']), $db->quote($data['email']),
		$db->quote($data['vacation_enable'], 'boolean'), $db->quote($data['vacation_subject']),
		$db->quote($data['vacation_message'])
		);
		$query = str_replace($search, $replace, $query);

		$sql_result = $db->query($query);
		if ($err = $db->is_error())
		{
			return PLUGIN_ERROR_PROCESS;
		}
			
		$sql_arr = $db->fetch_assoc($sql_result);
		if (empty($sql_arr))
		{
			continue;
		}

		if (isset($sql_arr['email']))
		{
			$data['email'] = $sql_arr['email'];
		}

		if (isset($sql_arr['email_local']))
		{
			$data['email_local'] = $sql_arr['email_local'];
		}

		if (isset($sql_arr['email_domain']))
		{
			$data['email_domain'] = $sql_arr['email_domain'];
		}

		if (isset($sql_arr['vacation_enable']))
		{
			$data['vacation_enable'] = $sql_arr['vacation_enable'];
		}

		if (isset($sql_arr['vacation_subject']))
		{
			$data['vacation_subject'] = $sql_arr['vacation_subject'];
		}

		if (isset($sql_arr['vacation_message']))
		{
			$data['vacation_message'] = $sql_arr['vacation_message'];
		}
	}

	return PLUGIN_NOERROR;
}

/*
 * Write driver function.
 *
 * @param array $data the array of data to get and set.
 *
 * @return integer the status code.
 */
function vacation_write(array &$data)
{
	$rcmail = rcmail::get_instance();

	if ($dsn = $rcmail->config->get('vacation_db_dsn'))
	{
		if (is_array($dsn) && empty($dsn['new_link']))
		{
			$dsn['new_link'] = true;
		}
		else if (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn))
		{
			$dsn .= '?new_link=true';
		}
		
		$db = new rcube_mdb2($dsn, '', FALSE);
		$db->set_debug((bool)$rcmail->config->get('sql_debug'));
		$db->db_connect('w');
	}
	else
	{
		$db = $rcmail->get_dbh();
	}

	if ($err = $db->is_error())
	{
		return PLUGIN_ERROR_CONNECT;
	}

	foreach($rcmail->config->get('vacation_sql_write') as $query)
	{
		$search = array('%username', '%email_local', '%email_domain', '%email',
							'%vacation_enable', '%vacation_subject',
							'%vacation_message');
		$replace = array($db->quote($data['username']), $db->quote($data['email_local']),
		$db->quote($data['email_domain']), $db->quote($data['email']),
		$db->quote($data['vacation_enable'], 'boolean'), $db->quote($data['vacation_subject']),
		$db->quote($data['vacation_message'])
		);
		$query = str_replace($search, $replace, $query);

		$sql_result = $db->query($query);
		if ($err = $db->is_error())
		{
			return PLUGIN_ERROR_PROCESS;
		}
	}

	return PLUGIN_NOERROR;
}

?>
