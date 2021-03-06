<?php

/**
 * returns all users
 * for every user prints their username,id,role.
 */

function show_users()
{
	global $mysqli;
	$sql = 'select username,player_id,role from players';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

/**
 * prints specific user.
 * @param string $token 
 * @return json user data 
 */

function show_user($token)
{
	global $mysqli;
	$sql = 'select username,player_id,token,role,last_action from players where token=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s', $token);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

/**
 *  user login
 * @param array $input
 * checks if user is first to login or second 
 */

function set_user($input)
{
	if (!isset($input['username'])) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg' => "No username given."]);
		exit;
	}
	$username = $input['username'];
	global $mysqli;
	$sql = 'select count(*) as c from players ';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$count = $res->fetch_assoc();
	if ($count['c'] == 0) {
		register_first_player($input['username']);
	} elseif ($count['c'] == 1) {
		register_second_player($input['username']);
	}
	update_game_status();
}

/**
 * sets specific characteristics for first user
 * @param string $username
 * sets role pick
 */

function register_first_player($username)
{
	global $mysqli;
	$sql = 'INSERT INTO players(username,token,`role`) VALUES (?,md5(CONCAT( ?, NOW())),"pick");';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ss', $username, $username);
	$st->execute();

	$id = $mysqli->insert_id;
	$sql = 'select token as "key" from players where player_id=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('i', $id);
	$st->execute();
	$res = $st->get_result();
	$token = $res->fetch_assoc();

	set_current_turn($id);
	show_user($token['key']);
}

/**
 * sets player turn in game status table
 * @param string  $token
 *  called while there is only the first player 
 */

function set_current_turn($id)
{
	global $mysqli;
	$sql = 'UPDATE `game_status` SET p_turn=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('i', $id);
	$st->execute();
}

/**
 * sets specific characteristics for second user
 * @param string  $username
 * sets role place
 */

function register_second_player($username)
{
	global $mysqli;
	$sql = 'INSERT INTO players(username,token,role) VALUES (?,md5(CONCAT( ?, NOW())),"place");';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ss', $username, $username);
	$st->execute();

	$id = $mysqli->insert_id;
	$sql = 'select token as "key" from players where player_id=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('i', $id);
	$st->execute();
	$res = $st->get_result();
	$token = $res->fetch_assoc();
	show_user($token['key']);
}

/**
 * redirects HTTP request to the appropriate function call
 * @param string  $method
 * @param array  $input
 */

function handle_user($method, $input)
{
	if ($method == 'GET') {
		show_user($input['token']);
	} else if ($method == 'PUT') {
		set_user($input);
	}
}
?>