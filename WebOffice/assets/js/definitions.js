"use strict";
const params = new URLSearchParams(new URL(decodeURIComponent(document.currentScript.src)).search);
const REQUEST_PATH = `${params.get('base')}/requests`;
const BASE = params.get('base') || '/';
/**
 * Rounds a number by precision
 * @param {Number} number Number to round
 * @param {Number} precision Number of decimal places to round by
 * @returns {Number} Rounded number
 */
Math.roundBy = (number, precision=0)=>{
    return parseFloat(number.toFixed(precision));
}


/**
 * Sends a request to the url
 *
 * @param {String} url URL
 * @param {{}} [options={}] Options to the xhr request
 * @returns {Promise<any>} Returns a promise response
 */
function sendRequest(url, options = {}) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(options.method || 'GET', url);
        if (options.headers) for (const header in options.headers) xhr.setRequestHeader(header, options.headers[header]);
        xhr.onload = () => {
            if (xhr.status == 200 && xhr.readyState == xhr.DONE) resolve(xhr.response);
            else reject({status: xhr.status, statusText: xhr.statusText});
        };
        xhr.onerror = () => reject(new Error('Network error'));
        xhr.send(options.data || null);
    });
}