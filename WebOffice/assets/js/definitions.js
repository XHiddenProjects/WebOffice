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


const LANGUAGES = $.ajax({
    url: `${BASE}/locales/${(navigator.language || navigator.userLanguage).toLowerCase() || 'en-us'}.json`,
    method: 'GET',
    dataType: 'json',
    async: false,
    success: function(data) {
        return data;
    },
    error: function() {
        return {};
    }
});

/**
 * Rotates the array base on the number of indexes
 * @param {Number} index Index rotation -1 backwards, 1 forwards
 * @returns {Array} Rotated Array
 */
Array.prototype.rotate = function(index) {
    const len = this.length;
    if (len === 0) return this;

    // Normalize the index to be within the array bounds
    const normalizedIndex = ((index % len) + len) % len;

    // Rotate the array by slicing and concatenating
    return this.slice(normalizedIndex).concat(this.slice(0, normalizedIndex));
};
/**
 * Capitalizes the first character
 * @returns {String} Capitalized string
 */
String.prototype.capitalizeFirstChar = function() {
    if (this.length === 0) return '';
    return this.charAt(0).toUpperCase() + this.slice(1);
};
/**
 * Rounds down the number to the nearest multiplier
 * @param {Number} n Base number
 * @param {Number} x Multiplier
 * @returns {Number} Rounded down number
 */
Math.floorBy = function(n,x){
    return (Math.floor(Math.floor(n)/x))*x||Math.floor(n);
}
/**
 * Rounds up the number to the nearest multiplier
 * @param {Number} n Base number
 * @param {Number} x Multiplier
 * @returns {Number} Rounded up number
 */
Math.ceilBy = function(n,x){
    return (Math.ceil(Math.ceil(n)/x))*x||Math.ceil(n);
}


/**
 * Parses XML from string, File object, or URL
 *
 * @param {String|File|URL} input - XML string, File object, or URL
 * @returns {Promise<Document>} Promise resolving to the parsed XML Document
 */
const XMLParse = (input) => {
    return new Promise((resolve, reject) => {
        const parser = new DOMParser();

        // Helper function to check for parse errors
        const isParseError = (xmlDoc) => {
            return xmlDoc.getElementsByTagName("parsererror").length > 0;
        };

        if (typeof input === 'string') {
            // Determine if input is a URL or XML string
            if (input.trim().startsWith("<")) {
                // Assume it's an XML string
                const xmlDoc = parser.parseFromString(input, "application/xml");
                if (isParseError(xmlDoc)) {
                    reject(new Error("Error parsing XML string."));
                } else {
                    resolve(xmlDoc);
                }
            } else {
                // Assume it's a URL
                fetch(input)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Network response was not ok: ${response.statusText}`);
                        }
                        return response.text();
                    })
                    .then(str => {
                        const xmlDoc = parser.parseFromString(str, "application/xml");
                        if (isParseError(xmlDoc)) {
                            reject(new Error("Error parsing XML from URL."));
                        } else {
                            resolve(xmlDoc);
                        }
                    })
                    .catch(err => reject(err));
            }
        } else if (input instanceof File) {
            // Handle File object
            const reader = new FileReader();
            reader.onload = () => {
                const xmlDoc = parser.parseFromString(reader.result, "application/xml");
                if (isParseError(xmlDoc)) {
                    reject(new Error("Error parsing XML from file."));
                } else {
                    resolve(xmlDoc);
                }
            };
            reader.onerror = () => reject(reader.error);
            reader.readAsText(input);
        } else {
            reject(new Error("Input must be a string, File, or URL."));
        }
    });
};


Math.MeasurementConverter = {
  // Conversion constants
  DPI: 96, // Default pixels per inch (common screen DPI, can be adjusted)

  // Conversion factors
  units: {
    px: {
      name: 'pixels',
      to_px: (value) => value,
      from_px: (value) => value,
    },
    in: {
      name: 'inch',
      to_px: (value) => value * 96, // 1 inch = 96 px
      from_px: (px) => px / 96,
    },
    ft: {
      name: 'foot',
      to_px: (value) => value * 12 * 96, // 1 ft = 12 in
      from_px: (px) => px / (12 * 96),
    },
    yd: {
      name: 'yard',
      to_px: (value) => value * 36 * 96, // 1 yd = 36 in
      from_px: (px) => px / (36 * 96),
    },
    mile: {
      name: 'mile',
      to_px: (value) => value * 5280 * 12 * 96, // 1 mile = 5280 ft
      from_px: (px) => px / (5280 * 12 * 96),
    },
    mm: {
      name: 'millimeter',
      to_px: (value) => value / 25.4 * 96, // 1 inch = 25.4 mm
      from_px: (px) => px / 96 * 25.4,
    },
    cm: {
      name: 'centimeter',
      to_px: (value) => value / 2.54 * 96, // 1 inch = 2.54 cm
      from_px: (px) => px / 96 * 2.54,
    },
    m: {
      name: 'meter',
      to_px: (value) => value / 0.0254 * 96, // 1 inch = 0.0254 m
      from_px: (px) => px / 96 * 0.0254,
    },
    km: {
      name: 'kilometer',
      to_px: (value) => value / 0.0000254 * 96, // 1 inch = 0.0000254 km
      from_px: (px) => px / 96 * 0.0000254,
    },
    mi: {
      name: 'mile',
      to_px: (value) => value * 5280 * 12 * 96, // same as mile above
      from_px: (px) => px / (5280 * 12 * 96),
    },
  },

  // Convert from one unit to another
  convert: function(value, fromUnit, toUnit) {
    const from = this.units[fromUnit];
    const to = this.units[toUnit];

    if (!from || !to) {
      throw new Error(`Invalid units: ${fromUnit} or ${toUnit}`);
    }

    // Convert value to pixels
    const valueInPx = from.to_px(value);
    // Convert pixels to target unit
    return to.from_px(valueInPx);
  },

  // Set DPI for conversions involving pixels
  setDPI: function(dpi) {
    this.DPI = dpi;
    // Update conversion functions if necessary
    // For now, they rely on static 96 dpi, but you can adjust if needed
  },

  // Example: convert px to inches
  pxToIn: function(px) {
    return this.units.in.from_px(px);
  },

  // Example: convert inches to px
  inToPx: function(inches) {
    return this.units.in.to_px(inches);
  },

  // Additional utility functions
  // Convert measurement to pixels (based on current DPI)
  measurementToPx: function(value, unit) {
    const u = this.units[unit];
    if (!u) throw new Error(`Invalid unit: ${unit}`);
    return u.to_px(value);
  },

  // Convert pixels to measurement (based on current DPI)
  pxToMeasurement: function(px, unit) {
    const u = this.units[unit];
    if (!u) throw new Error(`Invalid unit: ${unit}`);
    return u.from_px(px);
  },
};
