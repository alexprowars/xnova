
var https = require('https');
var fs = require('fs');
var ini = require('ini');

var config = ini.parse(fs.readFileSync('../app/config/core.ini', 'utf-8'));

var options = {
    key:    fs.readFileSync(config.chat.ssl_key),
    cert:   fs.readFileSync(config.chat.ssl_cert),
    ca:     fs.readFileSync(config.chat.ssl_ca)
};

var app = https.createServer(options);

var io = require('socket.io').listen(app);

app.listen(config.chat.port, "0.0.0.0");

var Memcached = require('memcached');
var memcached = new Memcached(config.memcache.host+':'+config.memcache.port, {});

var crypto = require('crypto');

var connectedUsers = {};

io.sockets.on('connection', function (socket)
{
	var primaryKey = crypto.createHash('md5').update(socket.handshake.query.userId+'|'+decodeURIComponent(socket.handshake.query.userName)+config.chat.key, 'utf8').digest('hex');

	if (primaryKey !== socket.handshake.query.key)
		return false;

	connectedUsers[socket.handshake.query.userName] = socket.id;

	memcached.get(config.chat.cache, function (err, data)
	{
		try
		{
			var chat = JSON.parse(data);
			var message;

			for (var id in chat)
			{
				if (chat.hasOwnProperty(id))
				{
					message = getMessage(chat[id], socket.handshake.query.userName);

					if (message !== false)
						socket.json.send(message);
				}
			}
		} catch (e) {
			memcached.set(config.chat.cache, JSON.stringify([]), 86400, function (err) { console.log('connection get error: '+err) });
		}
	});

	socket.on('message', function (msg, userId, userName, color, key)
	{
		var primaryKey = crypto.createHash('md5').update(userId+'|'+userName+config.chat.key, 'utf8').digest('hex');

		if (primaryKey !== key)
			return false;

		if (msg === '')
			return false;

		msg = decodeURIComponent(msg);

		var mysql = require('mysql');

		var connection = mysql.createConnection({
			host     : config.database.host,
			user     : config.database.username,
			password : config.database.password,
			database : config.database.dbname
		});

		connection.connect();

		connection.query('INSERT INTO game_log_chat SET ?', {user: userId, time: Math.floor(Date.now() / 1000), text: msg}, function(err, result)
		{
		  	if (err)
			  	throw err;

			insertMessage (msg, userId, userName, color, result.insertId, socket);
		});

		connection.end();
	});

	socket.on('history', function (id, username)
	{
		var mysql = require('mysql');

		var connection = mysql.createConnection({
			host     : config.database.host,
			user     : config.database.username,
			password : config.database.password,
			database : config.database.dbname
		});

		connection.connect();

		connection.query("SELECT c.*, u.username FROM game_log_chat c, game_users u WHERE u.id = c.user AND c.id < "+id+" AND text NOT LIKE '%приватно [%' ORDER BY c.id DESC LIMIT 50", {}, function(err, result)
		{
			if (err)
	  			throw err;

			var list = [];

			result.forEach(function (item)
			{
				var regexp = /приватно \[(.*?)\]/gi;
				var priv = [], user = [];
				var matches;

				while (matches = regexp.exec(item['text']))
					priv.push(matches[1]);

				regexp = /для \[(.*?)\]/gi;

				while (matches = regexp.exec(item['text']))
					user.push(matches[1]);

				if (priv.length)
					item['text'] = item['text'].replace(/приватно \[(.*?)\]/g, ' ');
				else if (user.length)
				{
					item['text'] = item['text'].replace(/для \[(.*?)\]/g, ' ');

					if (priv.length > 0)
					{
						priv = priv.concat(user).unique();
						user = [];
					}
				}

				var isPrivate = false;

				if (priv.length)
				{
					user = priv;
					isPrivate = true;
				}

				if (isPrivate)
					return;

				if (user === null)
					user = [];

				item['text'] = item['text'].trim();

				var insert = [item['id'], item['time'], item['username'], user, isPrivate, item['text'], 0];

				list.push(getMessage(insert, username));
			});

			socket.emit('history', list);
		})

		connection.end();
	});
});

function insertMessage (msg, userId, userName, color, lastId, socket)
{
	var now = Math.floor(Date.now() / 1000);
	var regexp = /приватно \[(.*?)\]/gi;
	var priv = [], user = [];
	var matches;

	while (matches = regexp.exec(msg))
		priv.push(matches[1]);

	regexp = /для \[(.*?)\]/gi;

	while (matches = regexp.exec(msg))
		user.push(matches[1]);

	if (priv.length)
		msg = msg.replace(/приватно \[(.*?)\]/g, ' ');
	else if (user.length)
	{
		msg = msg.replace(/для \[(.*?)\]/g, ' ');

		if (priv.length > 0)
		{
			priv = priv.concat(user).unique();
			user = [];
		}
	}

	msg = msg.trim();

	memcached.get(config.chat.cache, function (err, data)
	{
		var chat;

		try {
			chat = JSON.parse(data);
		}
		catch (e) {
			chat = [];
		}

		if (chat.length > 0)
		{
			for (var id in chat)
			{
				if (chat.hasOwnProperty(id) && chat[id][0] === now)
					now++;
			}
		}

		chat = chat.reverse();

		var tmp = [];

		for (id in chat)
		{
			if (chat.hasOwnProperty(id) && (id < 25 || chat[id][0] > (now - 120)))
				tmp.push(chat[id]);
		}

		chat = tmp;

		chat = chat.reverse();

		var isPrivate = false;

		if (priv.length)
		{
			user = priv;
			isPrivate = true;
		}

		if (user === null)
			user = [];

		var insert = [lastId, now, userName, user, isPrivate, msg, color];

		chat.push(insert);

		memcached.set(config.chat.cache, JSON.stringify(chat), 86400, function (err)
		{
			if (err !== undefined)
				console.log('insert message error: '+err)
		});

		socket.json.send(getMessage(insert, userName));

		for (var j in connectedUsers)
		{
			if (connectedUsers.hasOwnProperty(j) && connectedUsers[j] !== null)
			{
				var message = getMessage(insert, j);

				if (message !== false && message.me >= 0 && message.my >= 0)
				{
					if (io.sockets.connected[connectedUsers[j]] !== undefined)
						io.sockets.connected[connectedUsers[j]].send(message);
					else
						delete connectedUsers[j];
				}
			}
		}
	});
}

var color_massive = ['white', 'white', 'navy', 'blue', '#7397E1', '#009898', 'red', 'fuchsia', 'gray', 'lime', 'maroon', 'orange', 'сhocolate', 'darkkhaki'];

function getMessage (message, username)
{
	if (!Array.isArray(message[3]))
		message[3] = message[3] === false ? [] : [message[3]];

	if (message[4] && message[3].indexOf(username) === -1 && message[2] !== username)
		return false;

	message[5] = nl2br(message[5].replace("[\n\r]", ""));

	if (message[6] > 0)
		message[5] = "<font color=\""+color_massive[message[6]]+"\">"+message[5]+"</font>";

	var msg = {
		'id': message[0],
		'time': message[1],
		'user': message[2],
		'to': message[3],
		'text': message[5],
		'private': (message[4] ? 1 : 0),
		'me': -1,
		'my': -1
	};

	if (!message[4] && message[3].length > 0)
	{
		msg.me = message[3].indexOf(username) !== -1 ? 1 : 0;
		msg.my = (message[2] === username) ? 1 : 0;
	}
	else if (message[4] && message[3].length > 0 && (message[2] === username || message[3].indexOf(username) !== -1))
	{
		if (message[2] === '')
			msg.to = [];

		msg.me = message[2] === username ? 0 : 1;
		msg.my = msg.me ? 0 : 1;
	}
	else if (message[3].length === 0)
	{
		msg.me = 0;
		msg.my = message[2] === username ? 1 : 0;
	}

	return msg;
}

function nl2br (str, is_xhtml) 
{
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

if (!Array.isArray)
{
	Array.isArray = function(arg)
  	{
		return Object.prototype.toString.call(arg) === '[object Array]';
  	};
}

Array.prototype.unique = function()
{
    var a = this.concat();

    for (var i=0; i<a.length; ++i)
	{
        for (var j=i+1; j<a.length; ++j)
		{
            if (a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};

Array.prototype.remove = function(from, to)
{
  	var rest = this.slice((to || from) + 1 || this.length);
  	this.length = from < 0 ? this.length + from : from;

  	return this.push.apply(this, rest);
};