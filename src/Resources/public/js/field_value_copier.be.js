"use strict";function _classCallCheck(a,b){if(!(a instanceof b))throw new TypeError("Cannot call a class as a function")}function _defineProperties(a,b){for(var c,d=0;d<b.length;d++)c=b[d],c.enumerable=c.enumerable||!1,c.configurable=!0,"value"in c&&(c.writable=!0),Object.defineProperty(a,c.key,c)}function _createClass(a,b,c){return b&&_defineProperties(a.prototype,b),c&&_defineProperties(a,c),a}var FieldValueCopierBundle=/*#__PURE__*/function(){function a(){_classCallCheck(this,a)}return _createClass(a,null,[{key:"init",value:function init(){document.querySelectorAll(".field-value-copier .load").forEach(function(a){a.addEventListener("click",function(b){if(b.preventDefault(),!a.classList.contains("disabled")){var c,d=Array.prototype.filter.call(a.parentNode.parentNode.children,function(b){return b!==a&&"select"===b.tagName.toLowerCase()});utilsBundle.util.isTruthy(d[0].value)&&""!=d[0].value&&confirm(a.getAttribute("data-confirm"))&&(c=utilsBundle.url.addParameterToUri(a.getAttribute("href"),"fieldName",a.closest(".field-value-copier").getAttribute("data-field")),window.location.href=utilsBundle.url.addParameterToUri(c,"fieldValue",d[0].value))}})})}}]),a}();document.addEventListener("DOMContentLoaded",FieldValueCopierBundle.init);