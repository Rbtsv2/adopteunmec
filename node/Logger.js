const { createLogger, format, transports } = require('winston');
const { combine, timestamp, label, prettyPrint, colorize, printf} = format;

class Logger
{
    constructor() {
        this.instance = createLogger({
            format: combine(
                colorize(),
                timestamp(),
                printf(i => {
                    let date = new Date(i.timestamp);
                    return `${i.level} ${Logger.pad(date.getDate()) + '/'
                    + Logger.pad(date.getMonth() + 1) + '/'
                    + date.getFullYear() + ' '
                    + Logger.pad(date.getHours()) + ':'
                    + Logger.pad(date.getMinutes()) + ':'
                    + Logger.pad(date.getSeconds())}: ${i.message}`
                })
            ),
            transports: [
                new transports.Console()
            ]
        });
    }

    static pad(number, n = 1) {
        return number < 10 ? ('0'.repeat(n)) + number : number;
    }

    log(level, message) {
        this.instance.log({
            level: level,
            message: message
        })
    }
}

module.exports = Logger;