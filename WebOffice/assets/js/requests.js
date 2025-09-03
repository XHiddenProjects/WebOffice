$(document).ready(()=>{
    /**
     * Converts pixels to inches
     * @param {number} pixels Pixels
     * @param {number} dpi DPI of the screen (default 96)
     * @returns {number} Pixels in inches
     */
    /**
     * Description placeholder
     *
     * @param {number} pixels 
     * @param {number} [dpi=96] 
     * @returns {number} 
     */
    const px2in = (pixels, dpi = 96)=>{
        return pixels/dpi;
    },
    /**
     * Converts inches to centimeters
     * @param {number} inches Inches to convert
     * @returns {number} Centimeters
     */
    in2cm = (inches)=>{
        return inches*2.54;
    },
    /**
     * Returns the aspect ratio string
     * @param {number} width Width of screen
     * @param {number} height Height of screen
     * @returns {{ratioWidth: number, ratioHeight: number, string: string}} Returns the aspect ratio
     */
    getAspectRatioString = (width, height)=>{
        const gcd = (a, b)=>b === 0 ? a : gcd(b, a % b),
        divisor = gcd(width, height),
        ratioWidth = width / divisor,
        ratioHeight = height / divisor;
        return {
            ratioWidth: ratioWidth,
            ratioHeight : ratioHeight,
            string: `${ratioWidth}:${ratioHeight}`
        };
    },
    /**
     * Returns the resolution name
     * @returns {string} resolution name
     */
    getOrientationName = ()=>{
        if (window.screen.orientation||window.orientation) {
            const type = window.screen.orientation.type;
            if (type.includes('landscape')) {
            // Check if it's widescreen based on aspect ratio
            if (window.innerWidth / window.innerHeight >= 1.8) {
                return 'widescreen';
            } else {
                return 'landscape';
            }
            } else if (type.includes('portrait')) {
            return 'portrait';
            } else {
            return 'unknown orientation';
            }
        } else {
            // Fallback for browsers that don't support screen.orientation
            if (window.innerWidth > window.innerHeight) {
            if (window.innerWidth / window.innerHeight >= 1.8) {
                return 'widescreen';
            } else {
                return 'landscape';
            }
            } else {
            return 'portrait';
            }
        }
        };
    function getDeviceScreenSize() {
        const screenInfo = {
            width: window.screen.width,
            height: window.screen.height,
            colorDepth: window.screen.colorDepth,
            pixelDepth: window.screen.pixelDepth,
            pixelRatio: window.devicePixelRatio,
            resolution: getOrientationName(),
            aspectRatio: {
                ratioWidth: getAspectRatioString(window.screen.width*window.devicePixelRatio,window.screen.height*window.devicePixelRatio)['ratioWidth'],
                ratioHeight: getAspectRatioString(window.screen.width*window.devicePixelRatio,window.screen.height*window.devicePixelRatio)['ratioHeight'],
                toString: `${getAspectRatioString(window.screen.width*window.devicePixelRatio,window.screen.height*window.devicePixelRatio)['string']}`
            },
            DeviceResolution: {
                width: Math.ceil(window.screen.width*window.devicePixelRatio),
                height: Math.ceil(window.screen.height*window.devicePixelRatio),
                toString: `${Math.ceil(window.screen.width*window.devicePixelRatio)}x${Math.ceil(window.screen.height*window.devicePixelRatio)}`
            },
            DeviceInnerResolution: {
                width: Math.ceil(window.innerWidth*window.devicePixelRatio),
                height: Math.ceil(window.innerHeight*window.devicePixelRatio),
                toString: `${Math.ceil(window.innerWidth*window.devicePixelRatio)}x${Math.ceil(window.innerHeight*window.devicePixelRatio)}`
            },
            Dimensions:{
                inchesWidth: Math.roundBy(px2in(window.screen.width,96*window.devicePixelRatio),1),
                inchesHeight: Math.roundBy(px2in(window.screen.height,96*window.devicePixelRatio),1),
                centimetersWidth: Math.roundBy(in2cm(px2in(window.screen.width,96*window.devicePixelRatio)),1),
                centimetersHeight: Math.roundBy(in2cm(px2in(window.screen.height,96*window.devicePixelRatio)),1),
                toString: `${px2in(window.screen.width,96*window.devicePixelRatio)}" (${Math.roundBy(in2cm(px2in(window.screen.width,96*window.devicePixelRatio)),1)} cm)x${px2in(window.screen.height,96*window.devicePixelRatio)}" (${Math.roundBy(in2cm(px2in(window.screen.height,96*window.devicePixelRatio)),1)} cm)`
            },
            Diagonal: {
                inches: Math.roundBy(px2in(Math.sqrt(Math.pow(window.screen.width,2)+Math.pow(window.screen.height,2)),96*window.devicePixelRatio),1),
                centimeters: Math.roundBy(in2cm(px2in(Math.sqrt(Math.pow(window.screen.width,2)+Math.pow(window.screen.height,2)),96*window.devicePixelRatio)),1),
                toString: `${Math.roundBy(px2in(Math.sqrt(Math.pow(window.screen.width,2)+Math.pow(window.screen.height,2)),96*window.devicePixelRatio),1)}" (${Math.roundBy(in2cm(px2in(Math.sqrt(Math.pow(window.screen.width,2)+Math.pow(window.screen.height,2)),96*window.devicePixelRatio)),1)} cm)`
            },
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight,
                toString: `${window.innerWidth}x${window.innerHeight}`
            }
        };

        // Send data to PHP via AJAX
        $.ajax({
            url: `${REQUEST_PATH}/device.screen.php`, // Path to your PHP script
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(screenInfo),
            success: function() {},
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
            }
        });
    }
    // Call the function on page load
    getDeviceScreenSize();
});