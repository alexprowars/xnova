var io = require('socket.io').listen(6677);

var Memcached = require('memcached');
var memcached = new Memcached('localhost:11211', {});

var crypto = require('crypto');

var connectedUsers = {};

var http = require("http");

http.createServer(function(request, response) {
  response.writeHead(200, {"Content-Type": "text/plain"});
  response.write("Hello World");
  response.end();
}).listen(8888);

io.sockets.on('connection', function (socket)
{
	var primaryKey = crypto.createHash('md5').update(socket.handshake.query.userId+'|'+decodeURIComponent(socket.handshake.query.userName)+'SuperPuperChat', 'utf8').digest('hex');

	if (primaryKey != socket.handshake.query.key)
		return false;

	connectedUsers[socket.handshake.query.userName] = socket.id;

	memcached.get('chat', function (err, data)
	{
		try
		{
			var chat = JSON.parse(data);

			for (var id in chat)
			{
				if (chat.hasOwnProperty(id))
				{
					socket.json.send(getMessage(chat[id], socket.handshake.query.userName));
				}
			}
		} catch (e) {}
	});

	socket.on('message', function (msg, userId, userName, key)
	{
		var primaryKey = crypto.createHash('md5').update(userId+'|'+userName+'SuperPuperChat', 'utf8').digest('hex');

		if (primaryKey != key)
			return false;

		if (msg == '')
			return false;

		msg = decodeURIComponent(msg);

		var mysql = require('mysql');

		var connection = mysql.createConnection({
			host     : 'localhost',
			user     : 'uni5',
			password : 'N9a0R7d4',
			database : 'uni5'
		});

		connection.connect();

		connection.query('INSERT INTO game_log_chat SET ?', {user: userId, time: Math.floor(Date.now() / 1000), text: msg}, function(err, result)
		{
		  	if (err)
			  	throw err;

			insertMessage (msg, userId, userName, result.insertId, socket);
		});

		connection.end();
	});
});

function insertMessage (msg, userId, userName, lastId, socket)
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

	memcached.get('chat', function (err, data)
	{
		var chat;

		try
		{
			chat = JSON.parse(data);
		}
		catch (e)
		{
			chat = [];
		}

		if (chat.length > 0)
		{
			for (var id in chat)
			{
				if (chat.hasOwnProperty(id) && chat[id][0] == now)
					now++;
			}
		}

		chat = chat.reverse();

		for (id in chat)
		{
			if (chat.hasOwnProperty(id) && id >= 25 && chat[id][0] < (now - 120))
				delete chat[id];
		}

		chat = chat.reverse();

		var isPrivate = false;

		if (priv.length)
		{
			user = priv;
			isPrivate = true;
		}

		if (user === null)
			user = [];

		var insert = [lastId, now, userName, user, isPrivate, msg, 0];

		chat.push(insert);

		memcached.set('chat', JSON.stringify(chat), 86400, function (err) { console.log(err) });

		socket.json.send(getMessage(insert, userName));

		for (var j in connectedUsers)
		{
			if (connectedUsers.hasOwnProperty(j))
			{
				var message = getMessage(insert, j);

				if (message.me >= 0 && message.my >= 0)
					io.sockets.connected[connectedUsers[j]].send(getMessage(insert, j));
			}
		}
	});
}

var color_massive = ['white', 'white'];

function getMessage (message, username)
{
	message[5] = nl2br(message[5].replace("[\n\r]", ""));
	
	if (message[6] > 0)
		message[5] = "<font color=\""+color_massive[message[6]][0]+"\">"+message[5]+"</font>";

	if (!Array.isArray(message[3]))
		message[3] = message[3] == false ? [] : [message[3]];

	var msg = {'id': message[0], 'time': message[1], 'user': message[2], 'to': message[3], 'text': message[5], 'private': (message[4] > 0 ? 1 : 0), 'me': -1, 'my': -1};

	if (message[4] == 0 && message[3].length > 0)
	{
		msg.me = message[3].indexOf(username) != -1 ? 1 : 0;
		msg.my = (message[2] == username) ? 1 : 0;
	}
	else if (message[4] > 0 && message[3].length > 0 && (message[2] == username || message[3].indexOf(username) != -1))
	{
		if (message[2] == '')
			msg.to = [];

		msg.me = message[2] == username ? 0 : 1;
		msg.my = msg.me ? 0 : 1;
	}
	else if (message[3].length == 0)
	{
		msg.me = 0;
		msg.my = message[2] == username ? 1 : 0;
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