/*! angular-rison - v0.0.12 - 2013-09-20 */
angular.module('rison',[])
	/**
	 * The $rison class/service provides a simple API for encoding and 
	 * decoding javascript objects into a compact and URI friendly format
	 * @example
	 * angular.module('foo',['rison'])
	 *     .run(['$rison',function($rison){
	 *         var object = {
	 *             foo:'bar'
	 *         };
	 *         var string = $rison.stringify(object);
	 *         // object === $rison.parse(string) will evalutate to true
	 * }]);
	 *
	 * @version v0.0.12
	 * @name $rison
	 * @class
	 */
	.factory('$rison',[

		function(){
			var publicMembers = {},
				privateMembers = {};


			privateMembers.not_idchar  = " '!:(),*@$";


			/**
			 * characters that are illegal as the start of an id
			 * this is so ids can't look like numbers.
			 */
			privateMembers.not_idstart = "-0123456789";

            privateMembers.idRegularExpression = '[^' + privateMembers.not_idstart + privateMembers.not_idchar +
                                                 '][^' + privateMembers.not_idchar + ']*';

            privateMembers.id_ok = new RegExp('^'+privateMembers.idRegularExpression+'$');
            privateMembers.next_id = new RegExp(privateMembers.idRegularExpression, 'g');

            privateMembers.sq = {
                "'":true,
                "!":true
            };

			(function () {
			    var sq = { // url-ok but quoted in strings
			               "'": true,  '!': true
			    },
			    s = {
			            array: function (x) {
			                var a = ['!('], b, f, i, l = x.length, v;
			                for (i = 0; i < l; i += 1) {
			                    v = x[i];
			                    f = s[typeof v];
			                    if (f) {
			                        v = f(v);
			                        if (typeof v == 'string') {
			                            if (b) {
			                                a[a.length] = ',';
			                            }
			                            a[a.length] = v;
			                            b = true;
			                        }
			                    }
			                }
			                a[a.length] = ')';
			                return a.join('');
			            },
			            'boolean': function (x) {
			                if (x)
			                    return '!t';
			                return '!f';
			            },
			            'null': function (x) {
			                return "!n";
			            },
			            number: function (x) {
			                if (!isFinite(x))
			                    return '!n';
			                // strip '+' out of exponent, '-' is ok though
			                return String(x).replace(/\+/,'');
			            },
			            object: function (x) {
			                if (x) {
			                    if (x instanceof Array) {
			                        return s.array(x);
			                    }
			                    // WILL: will this work on non-Firefox browsers?
			                    if (typeof x.__prototype__ === 'object' && typeof x.__prototype__.encode_rison !== 'undefined')
			                        return x.encode_rison();

			                    var a = ['('], b, f, i, v, ki, ks=[];
			                    for (i in x)
			                        ks[ks.length] = i;
			                    ks.sort();
			                    for (ki = 0; ki < ks.length; ki++) {
			                        i = ks[ki];
			                        v = x[i];
			                        f = s[typeof v];
			                        if (f) {
			                            v = f(v);
			                            if (typeof v == 'string') {
			                                if (b) {
			                                    a[a.length] = ',';
			                                }
			                                a.push(s.string(i), ':', v);
			                                b = true;
			                            }
			                        }
			                    }
			                    a[a.length] = ')';
			                    return a.join('');
			                }
			                return '!n';
			            },
			            string: function (x) {
			                if (x === '')
			                    return "''";

			                if (privateMembers.id_ok.test(x))
			                    return x;

			                x = x.replace(/(['!])/g, function(a, b) {
			                    if (sq[b]) return '!'+b;
			                    return b;
			                });
			                return "'" + x + "'";
			            },
			            undefined: function (x) {
			                throw new Error("rison can't encode the undefined value");
			            }
			        };

			    
			    /**
			     * This method Rison-encodes a javascript structure.
			     * @public
			     * @name $rison#stringify
			     * @param  {Object} object The object to encode
			     * @return {String}        Returns a Rison-encoded String
			     */
			    publicMembers.stringify = function (object) {
			        return s[typeof object](object);
			    };

			})();




			/**
			 * This method parses a rison string into a javascript object or primitive
			 * @public
			 * @name $rison#parse
			 * @param  {String} rison The string that will be parsed
			 * @return {Object|*}   An objectual, or primitive, representation of the rison string
			 */
			publicMembers.parse = function(r) {
			    var errcb = function(e) { throw Error('rison decoder error: ' + e); };
			    var p = new privateMembers.parser(errcb);
			    return p.parse(r);
			};

			
			privateMembers.parser = function (errcb) {
			    this.errorHandler = errcb;
			};

			
			privateMembers.parser.WHITESPACE = "";

			privateMembers.parser.prototype.setOptions = function (options) {
			    if (options.errorHandler)
			        this.errorHandler = options.errorHandler;
			};


			privateMembers.parser.prototype.parse = function (str) {
			    this.string = str;
			    this.index = 0;
			    this.message = null;
			    var value = this.readValue();
			    if (!this.message && this.next())
			        value = this.error("unable to parse string as rison: '" + publicMembers.encode(str) + "'");
			    if (this.message && this.errorHandler)
			        this.errorHandler(this.message, this.index);
			    return value;
			};

			privateMembers.parser.prototype.error = function (message) {
			    if (typeof(console) != 'undefined')
			        console.error('Rison parser error: ', message);
			    this.message = message;
			    return undefined;
			};
			    
			privateMembers.parser.prototype.readValue = function () {
			    var c = this.next();
			    var fn = c && this.table[c];

			    if (fn)
			        return fn.apply(this);

			    // fell through table, parse as an id

			    var s = this.string;
			    var i = this.index-1;

			    // Regexp.lastIndex may not work right in IE before 5.5?
			    // g flag on the regexp is also necessary
			    privateMembers.next_id.lastIndex = i;
			    var m = privateMembers.next_id.exec(s);

			    // console.log('matched id', i, r.lastIndex);

			    if (m.length > 0) {
			        var id = m[0];
			        this.index = i+id.length;
			        return id;  // a string
			    }

			    if (c) return this.error("invalid character: '" + c + "'");
			    return this.error("empty expression");
			};

			privateMembers.parser.parse_array = function (parser) {
			    var ar = [];
			    var c;
			    while ((c = parser.next()) != ')') {
			        if (!c) return parser.error("unmatched '!('");
			        if (ar.length) {
			            if (c != ',')
			                parser.error("missing ','");
			        } else if (c == ',') {
			            return parser.error("extra ','");
			        } else
			            --parser.index;
			        var n = parser.readValue();
			        if (typeof n == "undefined") return undefined;
			        ar.push(n);
			    }
			    return ar;
			};

			privateMembers.parser.bangs = {
			    t: true,
			    f: false,
			    n: null,
			    '(': privateMembers.parser.parse_array
			};

			privateMembers.parser.prototype.table = {
			    '!': function () {
			        var s = this.string;
			        var c = s.charAt(this.index++);
			        if (!c) return this.error('"!" at end of input');
			        var x = privateMembers.parser.bangs[c];
			        if (typeof(x) == 'function') {
			            return x.call(null, this);
			        } else if (typeof(x) == 'undefined') {
			            return this.error('unknown literal: "!' + c + '"');
			        }
			        return x;
			    },
			    '(': function () {
			        var o = {};
			        var c;
			        var count = 0;
			        while ((c = this.next()) != ')') {
			            if (count) {
			                if (c != ',')
			                    this.error("missing ','");
			            } else if (c == ',') {
			                return this.error("extra ','");
			            } else
			                --this.index;
			            var k = this.readValue();
			            if (typeof k == "undefined") return undefined;
			            if (this.next() != ':') return this.error("missing ':'");
			            var v = this.readValue();
			            if (typeof v == "undefined") return undefined;
			            o[k] = v;
			            count++;
			        }
			        return o;
			    },
			    "'": function () {
			        var s = this.string;
			        var i = this.index;
			        var start = i;
			        var segments = [];
			        var c;
			        while ((c = s.charAt(i++)) != "'") {
			            //if (i == s.length) return this.error('unmatched "\'"');
			            if (!c) return this.error('unmatched "\'"');
			            if (c == '!') {
			                if (start < i-1)
			                    segments.push(s.slice(start, i-1));
			                c = s.charAt(i++);
			                if ("!'".indexOf(c) >= 0) {
			                    segments.push(c);
			                } else {
			                    return this.error('invalid string escape: "!'+c+'"');
			                }
			                start = i;
			            }
			        }
			        if (start < i-1)
			            segments.push(s.slice(start, i-1));
			        this.index = i;
			        return segments.length == 1 ? segments[0] : segments.join('');
			    },
			    // Also any digit.  The statement that follows this table
			    // definition fills in the digits.
			    '-': function () {
			        var s = this.string;
			        var i = this.index;
			        var start = i-1;
			        var state = 'int';
			        var permittedSigns = '-';
			        var transitions = {
			            'int+.': 'frac',
			            'int+e': 'exp',
			            'frac+e': 'exp'
			        };
			        do {
			            var c = s.charAt(i++);
			            if (!c) break;
			            if ('0' <= c && c <= '9') continue;
			            if (permittedSigns.indexOf(c) >= 0) {
			                permittedSigns = '';
			                continue;
			            }
			            state = transitions[state+'+'+c.toLowerCase()];
			            if (state == 'exp') permittedSigns = '-';
			        } while (state);
			        this.index = --i;
			        s = s.slice(start, i);
			        if (s == '-') return this.error("invalid number");
			        return Number(s);
			    }
			};
			// copy table['-'] to each of table[i] | i <- '0'..'9':
			(function (table) {
			    for (var i = 0; i <= 9; i++)
			        table[String(i)] = table['-'];
			})(privateMembers.parser.prototype.table);

			// return the next non-whitespace character, or undefined
			privateMembers.parser.prototype.next = function () {
			    var s = this.string,
			        i = this.index,
			        c;
			    do {
			        if (i == s.length) return undefined;
			        c = s.charAt(i++);
			    } while (privateMembers.parser.WHITESPACE.indexOf(c) >= 0);
			    this.index = i;
			    return c;
			};

		return publicMembers;
}]);