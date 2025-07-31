"use strict";
const params = new URLSearchParams(new URL(decodeURIComponent(document.currentScript.src)).search);
const REQUEST_PATH = `${params.get('base')}/requests`;