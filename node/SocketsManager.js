const redis = require('socket.io-redis');
const io = require('socket.io');
const fs = require('fs');
const os = require('os');
const dns = require('dns');
const jwt = require('jsonwebtoken');
const http = require('http');
const https = require('https');
const express = require('express');
const bodyParser = require("body-parser");
const cluster = require('cluster');
const Logger = require('./Logger');

require('dotenv').config({path: '../.env'});

class SocketsManager {
    constructor(port) {
        this.logger = new Logger();
        this.port = port || 8080;
        this.cores = 1;
        this.clients = {};
        //this.scraper = new Scraper(this.logger);
        
        this.debug = {
            server_verbose: true
        };
    }

    /**
     * Write the PID of the master process in the cluster.
     */
    writePid() {
        fs.writeFile(__dirname + '/../cluster_master.pid', process.pid, {}, err => {
            if (err) throw err;

            if (this.debug.server_verbose)
                this.logger.log('info', 'Saved cluster master pid.');
        });
    }


    /**
     * Start the nodejs cluster.
     */
    start() {

        this.logger.log('info', 'PID MASTER: ' + cluster.isMaster);
        this.logger.log('info', 'PID WORKER: ' + cluster.isWorker);

        // if (cluster.isMaster) {
           
        //     this.writePid();

        //     let server = process.platform != 'win32'
        //         ? https.createServer(this.https_config)
        //         : http.createServer();

        //     let socketio = io.listen(server);
      
        //     socketio.adapter(redis({host: 'localhost', port: 6379}));

        //     for (let i = 0; i < this.cores; i++)
        //         cluster.fork();

        //     cluster.on('exit', (worker, code, signal) => {
        //         if (this.debug.server_verbose)
        //             this.logger.log('warning', 'Worker ' + worker.process.pid + 'died.', code, signal);
        //     });

        //     if (this.debug.server_verbose)
        //         this.logger.log('info', 'Master started with pid: ' + process.pid);
        // }

        if (cluster.isMaster) {
            let config;
            let server;

            let app = express();

            app.use(bodyParser.urlencoded({
                extended: true
            }));

        
            if (process.env.APP_ENV_NODE == "dev") {

                this.logger.log('info', 'Mode developpeur');
                config = {
                    key: 'null',
                    cert: 'null',
                    ca: 'null'
                };

                server = http.createServer(app);
                server.listen(this.port, config.hostname, () => {
                    this.logger.log('info', `Server running at http://localhost:${this.port}`);
                });
            }
            if (process.env.APP_ENV_NODE == "prod") {

                this.logger.log('info', 'Mode production');
                config = {
                    key: fs.readFileSync(this.certs_path + 'privkey.pem', 'utf8'),
                    cert: fs.readFileSync(this.certs_path + 'cert.pem', 'utf8')
                    //ca: fs.readFileSync(this.certs_path + 'chain.pem', 'utf8')
                };
                server = https.createServer(config,app);
                server.listen(this.port, config.hostname, () => {
                    this.logger.log('info', `Server running at https://imagefake.com:${this.port}`);
                });
            }

          

            this.logger.log('info', 'Worker started with pid: ' + process.pid);

            // socket IO ne focntionne pas en production.
            let socketio = io.listen(server);
            socketio.adapter(redis({host: 'localhost', port: 6379}));
            

            this.listenEvents(socketio);
        
            
            
            // app.get('/test', (req, res) => {
            //     // res.send('test page');
            //     // this.logger.log('info','mode test'); 
            //     // return this.scraper.getImageTest();  

            // });


            // app.post('/', (req, res) => {
            //     this.logger.log('info', 'Worker #' + process.pid + ' received request from ' + req.connection.remoteAddress);
            //     if (req.connection.remoteAddress == '::1'
            //         || req.connection.remoteAddress == '::ffff:127.0.0.1'
            //         || req.connection.remoteAddress == '127.0.0.1') {

            //         if (typeof req.body.token == 'undefined') {
            //             this.logger.log('info','Token is missing');
            //             res.status(400);
            //             return res.send('The token is missing');
            //         } else if (req.body.token == null || req.body.token.length == 0) {
            //             this.logger.log('info','Token is invalid');
            //             res.status(400);
            //             return res.send('The token is invalid');
            //         }

            //         if (typeof req.body.url == 'undefined') {
            //             this.logger.log('info','Url is missing');
            //             res.status(400);
            //             return res.send('The url is missing');
            //         }

            //         jwt.verify(req.body.token, process.env.APP_SECRET, (err, decoded) => {
            //             if (err) {
            //                 this.logger.log('info','Error while decoding the token.');
            //                 console.log(err);
            //                 res.status(401);

            //                 return res.send('Error while decoding the token.');
            //             }
            //             else {
            //                 this.logger.log('info', 'Worker #' + process.pid + ' just received a task');

            //                 let socket = this.getSocketByUserId(decoded.user_id);

            //                 if (socket) {
            //                     res.status(200);
            //                     res.send('Success');
            //                     this.logger.log('info', 'do activate worker');
            //                     // return this.scraper.getImage(req.body.url, decoded.user_id, req.body.imageId, searchId => {
            //                     //     this.logger.log('info', 'Worker #' + process.pid + ' found results ! #' + searchId);
            //                     //     socket.emit('result', {search_id: searchId});
            //                     // })
            //                 }
            //                 else {
            //                     this.logger.log('info', 'pb');
            //                     res.status(401);
            //                     return res.send('Unable to find a user associated with this token');
            //                 }
            //             }
            //         });
            //     }
            //     else {
            //         res.send('Bad ip address');
            //     }
            //})
        }
    }

    /**
     * Listen to socket.io events.
     * @param socketio
     */
    listenEvents(socketio) {
        
        socketio.on('connection', socket => {

           

            this.logger.log('info', 'socket listen');

            socket.on('notify', (msg) => {
                console.log('message: ' + msg);
            });
            socket.on('player', (msg) => {
                console.log('player : ' + msg);
            });


            if (typeof socket.handshake.query != 'undefined') {
               
                if (typeof socket.handshake.query.token != 'undefined') {

                    if (socket.handshake.query.token == null || socket.handshake.query.token.length == 0) {
                        this.onConnection(socket, null);

            

                        if (this.debug.server_verbose){
                            this.logger.log('info', 'Worker ' + process.pid + ': ' + this.getClientsCount() + ' clients connected');
                        }
                          
                    }
                    else {
                        jwt.verify(socket.handshake.query.token, process.env.APP_SECRET, (err, decoded) => {
                            if (err)
                                this.logger.log('warn', 'Socket error:  Can\'t decode token.' + err);
                            else {
                                this.onConnection(socket, decoded.user_id);

                                if (this.debug.server_verbose)
                                    this.logger.log('info', 'Worker ' + process.pid + ': ' + this.getClientsCount() + ' clients connected');
                            }
                        });
                    }

                    socket.on('disconnect', () => {
                        this.onDisconnect(socket);

                        if (this.debug.server_verbose)
                            this.logger.log('info', 'Worker ' + process.pid + ': ' + this.getClientsCount() + ' clients connected');
                    });
                } else {
                    this.logger.log('warn', 'socket.handshake.query.token is undefined');
                }
            } else {
                this.logger.log('warn', 'socket.handshake.query is undefined');
            }
        });
    }

    /**
     * Add the client to the list
     * @param socket
     * @param user_id
     */
    onConnection(socket, user_id) {
        this.clients[socket.id] = {
            user_id: user_id,
            socket: socket
        };
    }

    /**
     * Get a socket id via a given user id.
     * @param userId
     * @returns {*}
     */
    getSocketByUserId(userId) {

        for (let socketId in this.clients)
            if (this.clients[socketId].user_id == userId)
                return this.clients[socketId].socket;

        return null;
    }

    /**
     * Remove a client from the list.
     * @param socket
     */
    onDisconnect(socket) {
        let client = this.clients[socket.id];

        if (typeof client != 'undefined')
            delete this.clients[socket.id];
    }

    /**
     * Return the number of clients in the list.
     * @returns {*}
     */
    getClientsCount() {
        return Object.keys(this.clients).length;
    }
}

module.exports = SocketsManager;