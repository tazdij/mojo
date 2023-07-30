/**
    JavaScript Display Library (JSDL)
    
    A Library/Framework for using Javascript to layout your website.
    
    Author: Don Duvall
    Date: 2019-08-07
    Email: shout@tazdij.com
    Reason: I thought it would be interesting to explore a psuedo replacement
        of CSS with a simple JavaScript library. Allowing you HTML to be super
        clean and not hacked together to make your layout work with CSS. The
        most interesting to me is seeing how this effects a websites SEO. To
        the Search Engine, it will look like bland HTML with minimal styles.
    Disclaimer: I DO NOT RECOMMEND THIS LIBRARY AS A SERIOUS REPLACEMENT TO
        ANY STANDARD WEB TECHNOLOGY! THIS IS JUST A RESEARCH PROJECT FOR FUN!
    License: MIT
*/

(function(funcName, baseObj) {
    // The public function name defaults to window.docReady
    // but you can pass in your own object and own function name and those will be used
    // if you want to put them in a different namespace
    funcName = funcName || "docReady";
    baseObj = baseObj || window;
    var readyList = [];
    var readyFired = false;
    var readyEventHandlersInstalled = false;

    // call this when the document is ready
    // this function protects itself against being called more than once
    function ready() {
        if (!readyFired) {
            // this must be set to true before we start calling callbacks
            readyFired = true;
            for (var i = 0; i < readyList.length; i++) {
                // if a callback here happens to add new ready handlers,
                // the docReady() function will see that it already fired
                // and will schedule the callback to run right after
                // this event loop finishes so all handlers will still execute
                // in order and no new ones will be added to the readyList
                // while we are processing the list
                readyList[i].fn.call(window, readyList[i].ctx);
            }
            // allow any closures held by these functions to free
            readyList = [];
        }
    }

    function readyStateChange() {
        if ( document.readyState === "complete" ) {
            ready();
        }
    }

    // This is the one public interface
    // docReady(fn, context);
    // the context argument is optional - if present, it will be passed
    // as an argument to the callback
    baseObj[funcName] = function(callback, context) {
        if (typeof callback !== "function") {
            throw new TypeError("callback for docReady(fn) must be a function");
        }
        // if ready has already fired, then just schedule the callback
        // to fire asynchronously, but right away
        if (readyFired) {
            setTimeout(function() {callback(context);}, 1);
            return;
        } else {
            // add the function and context to the list
            readyList.push({fn: callback, ctx: context});
        }
        // if document already ready to go, schedule the ready function to run
        if (document.readyState === "complete") {
            setTimeout(ready, 1);
        } else if (!readyEventHandlersInstalled) {
            // otherwise if we don't have event handlers installed, install them
            if (document.addEventListener) {
                // first choice is DOMContentLoaded event
                document.addEventListener("DOMContentLoaded", ready, false);
                // backup is window load event
                window.addEventListener("load", ready, false);
            } else {
                // must be IE
                document.attachEvent("onreadystatechange", readyStateChange);
                window.attachEvent("onload", ready);
            }
            readyEventHandlersInstalled = true;
        }
    }
})("docReady", window);

/*
 * JSS v0.6 - JavaScript Stylesheets
 * https://github.com/Box9/jss
 *
 * Copyright (c) 2011, David Tang
 * MIT Licensed (http://www.opensource.org/licenses/mit-license.php)
 */
var jss = (function() {
    var adjSelAttrRegex = /((?:\.|#)[^\.\s#]+)((?:\.|#)[^\.\s#]+)/g;
    var doubleColonPseudoElRegex = /(::)(before|after|first-line|first-letter|selection)/;
    var singleColonPseudoElRegex = /([^:])(:)(before|after|first-line|first-letter|selection)/;
    var singleColonForPseudoElements; // flag for older browsers

    function getSelectorsAndRules(sheet) {
        var rules = sheet.cssRules || sheet.rules || [];
        var results = {};
        for (var i = 0; i < rules.length; i++) {
            // Older browsers and FF report pseudo element selectors in an outdated format
            var selectorText = toDoubleColonPseudoElements(rules[i].selectorText);
            if (!results[selectorText]) {
                results[selectorText] = [];
            }
            results[selectorText].push({
                sheet: sheet,
                index: i,
                style: rules[i].style
            });
        }
        return results;
    }

    function getRules(sheet, selector) {
        var rules = sheet.cssRules || sheet.rules || [];
        var results = [];
        // Browsers report selectors in lowercase
        selector = selector.toLowerCase();
        for (var i = 0; i < rules.length; i++) {
            var selectorText = rules[i].selectorText;
            // Note - certain rules (e.g. @rules) don't have selectorText
            if (selectorText && (selectorText == selector || selectorText == swapAdjSelAttr(selector) || selectorText == swapPseudoElSyntax(selector))) {
                results.push({
                    sheet: sheet,
                    index: i,
                    style: rules[i].style
                });
            }
        }
        return results;
    }

    function addRule(sheet, selector) {
        var rules = sheet.cssRules || sheet.rules || [];
        var index = rules.length;
        var pseudoElementRule = addPseudoElementRule(sheet, selector, rules, index);

        if (!pseudoElementRule) {
            addRuleToSheet(sheet, selector, index);
        }
        
        return {
            sheet: sheet,
            index: index,
            style: rules[index].style
        };
    };

    function addRuleToSheet(sheet, selector, index) {
        if (sheet.insertRule) {
            sheet.insertRule(selector + ' { }', index);
        } else {
            sheet.addRule(selector, null, index);
        }
    }

    // Handles single colon syntax for older browsers and bugzilla.mozilla.org/show_bug.cgi?id=949651
    function addPseudoElementRule(sheet, selector, rules, index) {
        var doubleColonSelector;
        var singleColonSelector;

        if (doubleColonPseudoElRegex.exec(selector)) {
            doubleColonSelector = selector;
            singleColonSelector = toSingleColonPseudoElements(selector);
        } else if (singleColonPseudoElRegex.exec(selector)) {
            doubleColonSelector = toDoubleColonPseudoElements(selector);
            singleColonSelector = selector;
        } else {
            return false; // Not dealing with a pseudo element
        }

        if (!singleColonForPseudoElements) {
            // Assume modern browser and then check if successful
            addRuleToSheet(sheet, doubleColonSelector, index);
            if (rules.length <= index) {
                singleColonForPseudoElements = true;
            }
        }
        if (singleColonForPseudoElements) {
            addRuleToSheet(sheet, singleColonSelector, index);
        }

        return true;
    }

    function toDoubleColonPseudoElements(selector) {
        return selector.replace(singleColonPseudoElRegex, function (match, submatch1, submatch2, submatch3) {
            return submatch1 + '::' + submatch3;
        });
    }

    function toSingleColonPseudoElements(selector) {
        return selector.replace(doubleColonPseudoElRegex, function(match, submatch1, submatch2) {
            return ':' + submatch2;
        })
    }

    function removeRule(rule) {
        var sheet = rule.sheet;
        if (sheet.deleteRule) {
            sheet.deleteRule(rule.index);
        } else if (sheet.removeRule) {
            sheet.removeRule(rule.index);
        }
    }

    function extend(dest, src) {
        for (var key in src) {
            if (!src.hasOwnProperty(key))
                continue;
            dest[key] = src[key];
        }
        return dest;
    }

    function aggregateStyles(rules) {
        var aggregate = {};
        for (var i = 0; i < rules.length; i++) {
            extend(aggregate, declaredProperties(rules[i].style));
        }
        return aggregate;
    }

    function declaredProperties(style) {
        var declared = {};
        for (var i = 0; i < style.length; i++) {
            declared[style[i]] = style[toCamelCase(style[i])];
        }
        return declared;
    }

    // IE9 stores rules with attributes (classes or ID's) adjacent in the opposite order as defined
    // causing them to not be found, so this method swaps [#|.]sel1[#|.]sel2 to become [#|.]sel2[#|.]sel1
    function swapAdjSelAttr(selector) {
        var swap = '';
        var lastIndex = 0;
            
        while ((match = adjSelAttrRegex.exec(selector)) != null) {
            if (match[0] === '')
                break;
            swap += selector.substring(lastIndex, match.index);
            swap += selector.substr(match.index + match[1].length, match[2].length);
            swap += selector.substr(match.index, match[1].length);
            lastIndex = match.index + match[0].length;
        }
        swap += selector.substr(lastIndex);
        
        return swap;
    };

    // FF and older browsers store rules with pseudo elements using single-colon syntax
    function swapPseudoElSyntax(selector) {
        if (doubleColonPseudoElRegex.exec(selector)) {
            return toSingleColonPseudoElements(selector);
        }
        return selector;
    }

    function setStyleProperties(rule, properties) {
        for (var key in properties) {
            var value = properties[key];
            var importantIndex = value.indexOf(' !important');

            // Modern browsers seem to handle overrides fine, but IE9 doesn't
            rule.style.removeProperty(key); 
            if (importantIndex > 0) {
                rule.style.setProperty(key, value.substr(0, importantIndex), 'important');
            } else {
                rule.style.setProperty(key, value);
            }
        }
    }

    function toCamelCase(str) {
        return str.replace(/-([a-z])/g, function (match, submatch) {
            return submatch.toUpperCase();
        });
    }

    function transformCamelCasedPropertyNames(oldProps) {
        var newProps = {};
        for (var key in oldProps) {
            newProps[unCamelCase(key)] = oldProps[key];
        }
        return newProps;
    }

    function unCamelCase(str) {
        return str.replace(/([A-Z])/g, function(match, submatch) {
            return '-' + submatch.toLowerCase();
        });
    }

    var Jss = function(doc) {
        this.doc = doc;
        this.head = this.doc.head || this.doc.getElementsByTagName('head')[0];
        this.sheets = this.doc.styleSheets || [];
    };

    Jss.prototype = {
        // Returns JSS rules (selector is optional)
        get: function(selector) {
            if (!this.defaultSheet) {
                return {};
            }
            if (selector) {
                return aggregateStyles(getRules(this.defaultSheet, selector));
            }
            var rules = getSelectorsAndRules(this.defaultSheet);
            for (selector in rules) {
                rules[selector] = aggregateStyles(rules[selector]);
            }
            return rules;
        },
        // Returns all rules (selector is required)
        getAll: function(selector) {
            var properties = {};
            for (var i = 0; i < this.sheets.length; i++) {
                extend(properties, aggregateStyles(getRules(this.sheets[i], selector)));
            }
            return properties;
        },
        // Adds JSS rules for the selector based on the given properties
        set: function(selector, properties) {
            if (!this.defaultSheet) {
                this.defaultSheet = this._createSheet();
            }
            properties = transformCamelCasedPropertyNames(properties);
            var rules = getRules(this.defaultSheet, selector);
            if (!rules.length) {
                rules = [addRule(this.defaultSheet, selector)];
            }
            for (var i = 0; i < rules.length; i++) {
                setStyleProperties(rules[i], properties);
            }
        },
        // Removes JSS rules (selector is optional)
        remove: function(selector) {
            if (!this.defaultSheet)
                return;
            if (!selector) {
                this._removeSheet(this.defaultSheet);
                delete this.defaultSheet;
                return;
            }
            var rules = getRules(this.defaultSheet, selector);
            for (var i = 0; i < rules.length; i++) {
                removeRule(rules[i]);
            }
            return rules.length;
        },
        _createSheet: function() {
            var styleNode = this.doc.createElement('style');
            styleNode.type = 'text/css';
            styleNode.rel = 'stylesheet';
            this.head.appendChild(styleNode);
            return styleNode.sheet;
        },
        _removeSheet: function(sheet) {
            var node = sheet.ownerNode;
            node.parentNode.removeChild(node);
        }
    };

    var exports = new Jss(document);
    exports.forDocument = function(doc) {
        return new Jss(doc);
    };
    return exports;
})();

var jsdl = (function() {
    
    
    
    var Jsdl = function() {
    
    };
    Jsdl.prototype = {
        // Instance functions and variables go here
        echo: function(msg) {
            console.log('ECHO: ' + msg);
        }
    };
    var exports = new Jsdl();
    return exports;
})();
