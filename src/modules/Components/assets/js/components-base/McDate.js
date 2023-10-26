class McDate {
    constructor(date) {
        this.locale = $MAPAS.config.locale;
        if (date instanceof Date) {
            this._date = date;
        } else {
            if(date.length == '10') {
                this._date = new Date(`${date} 00:00:00`);
            } else {
                this._date = new Date(date);
            }
        }
    }

    format(config) {
        return Intl.DateTimeFormat(this.locale, config).format(this._date);
    }

    sql(options) {
        let year = this._date.getFullYear();
        let month = String(this._date.getMonth() + 1).padStart(2,0)
        let day = String(this._date.getDate()).padStart(2,0);
        if(options == 'date') {
            return `${year}-${month}-${day}`;
        } else if (options == 'time') {
            return this.time('full');
        } else {
            const time = this.time('full');
            return `${year}-${month}-${day} ${time}`;
        }
    }

    date(options) {
        if(options == 'sql') {
            return this.sql('date');
        }

        options = options?.split(' ') || ['long'];
        const config = {day:'numeric'};

        if (options.indexOf('year') >= 0) {
            config.year = 'numeric';
        }

        // https://tc39.es/ecma402/#sec-datetimeformat-abstracts
        for(let val of ["2-digit", "numeric", "narrow", "short", "long"]) {
            if(options.indexOf(val) >= 0) {
                config.month = val;
            }
        }

        if(config.month == '2-digit' || config.month == 'numeric') {
            config.day = config.month;
        }

        return this.format(config)
    }

    time(option) {
        if (option == 'sql') {
            return this.sql('time');
        }

        option = option || 'short';
        const config = {hour: '2-digit', minute: '2-digit'};
        if (option == 'long') {
            config.second = '2-digit';
        }
        return this.format(config);
    }

    year(format) {
        format = format || 'numeric';

        return this.format({year: format});
    }

    month(format) {
        format = format || 'long';

        return this.format({month: format});
    }

    weekday(format) {
        format = format || 'long';

        return this.format({weekday: format});
    }

    day(format) {
        format = format || 'numeric';

        return this.format({day: format});
    }

    hour(format) {
        format = format || 'numeric';

        return this.format({hour: format});
    }

    minute(format) {
        format = format || 'numeric';

        return this.format({minute: format});
    }

    second(format) {
        format = format || 'numeric';

        return this.format({second: format});
    }

    isToday() {
        const now = new Date();
        return now.toDateString() == this._date.toDateString();
    }

    isTomorrow() {
        const now = new Date();
        now.setDate(now.getDate() + 1);
        return now.toDateString() == this._date.toDateString();
    }

    isYesterday() {
        const now = new Date();
        now.setDate(now.getDate() - 1);
        return now.toDateString() == this._date.toDateString();
    }

    isFuture() {
        const now = new Date();
        return now < this._date;
    }

    isPast() {
        const now = new Date();
        return now > this._date;
    }

    addDays (days) {
        const now = this._date;
        now.setDate(now.getDate() + days);
        const config = { year: 'numeric', month: 'long', day: 'numeric' };
        return Intl.DateTimeFormat(this.locale, config).format(now);
    }
}