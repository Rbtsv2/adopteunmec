const SocketsManager = require('./SocketsManager');

let manager = new SocketsManager(3000);
manager.start();