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