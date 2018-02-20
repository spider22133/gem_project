/*!
 * hoverIntent v1.8.1 // 2014.08.11 // jQuery v1.9.1+
 * http://cherne.net/brian/resources/jquery.hoverIntent.html
 *
 * You may use hoverIntent under the terms of the MIT license. Basically that
 * means you are free to use hoverIntent as long as this header is left intact.
 * Copyright 2007, 2014 Brian Cherne
 */
!function(e){"use strict";"function"==typeof define&&define.amd?define(["jquery"],e):jQuery&&!jQuery.fn.hoverIntent&&e(jQuery)}(function(e){"use strict";var t,n,o={interval:100,sensitivity:6,timeout:0},i=0,r=function(e){t=e.pageX,n=e.pageY},u=function(e,o,i,v){if(Math.sqrt((i.pX-t)*(i.pX-t)+(i.pY-n)*(i.pY-n))<v.sensitivity)return o.off("mousemove.hoverIntent"+i.namespace,r),delete i.timeoutId,i.isActive=!0,delete i.pX,delete i.pY,v.over.apply(o[0],[e]);i.pX=t,i.pY=n,i.timeoutId=setTimeout(function(){u(e,o,i,v)},v.interval)};e.fn.hoverIntent=function(t,n,v){var s=i++,a=e.extend({},o);a=e.isPlainObject(t)?e.extend(a,t):e.isFunction(n)?e.extend(a,{over:t,out:n,selector:v}):e.extend(a,{over:t,out:t,selector:n});var d=function(t){var n=e.extend({},t),o=e(this),i=o.data("hoverIntent");i||o.data("hoverIntent",i={});var v=i[s];v||(i[s]=v={id:s}),v.timeoutId&&(v.timeoutId=clearTimeout(v.timeoutId));var d=v.namespace=".hoverIntent"+s;if("mouseenter"===t.type){if(v.isActive)return;v.pX=n.pageX,v.pY=n.pageY,o.on("mousemove.hoverIntent"+d,r),v.timeoutId=setTimeout(function(){u(n,o,v,a)},a.interval)}else{if(!v.isActive)return;o.off("mousemove.hoverIntent"+d,r),v.timeoutId=setTimeout(function(){!function(e,t,n,o){delete t.data("hoverIntent")[n.id],o.apply(t[0],[e])}(n,o,v,a.out)},a.timeout)}};return this.on({"mouseenter.hoverIntent":d,"mouseleave.hoverIntent":d},a.selector)}});