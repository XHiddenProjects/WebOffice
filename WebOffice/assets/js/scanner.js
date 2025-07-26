class Scanner{
    #types;
    #base32;
    /**
     * Creates a new scanner
     * @param {HTMLElement} el Element to create scanner
     * @throws {Error} If the provided element is not a valid HTMLElement.
     */
    constructor(el){
        if(!(el instanceof HTMLElement)) throw new Error('Must be a HTMLElement');
        this.el = el;
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.type = '';
        this.#types = ['barcode', 'qrcode'];
        this.data = '';
        this.#base32 = '';
        this.correctionLevel = {
            L: 0.07,
            M: 0.15,
            Q: 0.25,
            H: 0.30
        }
        this.options = {
            barcode: {
                symbology: {
                    code128: 'code128',
                    code39: 'code39',
                    ean13: 'ean13',
                    ean8: 'ean8',
                    upc: 'upc',
                    itf: 'itf'
                }
            }
        }
        this.config = {
            barcode: {
                symbology: 'code128',
                width: 64,
                height: 64,
                background: '#fff',
                color: '#000'
            },
            qrcode: {
                width: 64, // Width of the QRCode
                height: 64, // Height of the QRCode
                colorDark: '#000000', // Dark CSS color
                colorLight: '#ffffff', // Light CSS color
                correctLevel: this.correctionLevel.H,//
                // Dot style
                dotScale: 1, // Dot style scale. 0-1.0
                dotScaleTiming: 1, // Dot style scale for timing. 0-1.0
                dotScaleTiming_V: undefined, // Dot style scale for horizontal timing
                dotScaleTiming_H: undefined, // Dot style scale for vertical timing
                dotScaleA: 1, // Dot style scale for alignment
                dotScaleAO: undefined, // Dot style scale for alignment outer
                dotScaleAI: undefined, // Dot style scale for alignment inner
                //Quiet zone
                quietZoneColor: 'rgba(0,0,0,0)', // Background CSS color to Quiet Zone
                quietZone: 0, // Quiet Zone size,
                // Logo
                logo: '', // Logo Image Path or Base64 encoded image
                logoWidth: 64/3.5, // Fixed logo width (use default width value)
                logoHeight: 64/3.5, // Fixed logo height (use default height value)
                logoMaxWidth: undefined, // Maximum logo width, if set will ignore logoWidth value.
                logoMaxHeight: undefined, // Maximum logo height. if set will ignore logoHeight value.
                logoBackgroundTransparent: false, // Whether the background transparent image(PNG) shows transparency. When true, logoBackgroundColor is invalid
                logoBackgroundColor: '#FFFFFF', // Set Background CSS Color when image background transparent. Valid when logoBackgroundTransparent is false
                // Background Image
                backgroundImage: undefined, // Background Image Path or Base64 encoded Image
                backgroundImageAlpha: 1, // Background image transparency. Ranges: 0-1.0
                autoColor: true, // Automatic color adjustment(for data block)
                autoColorDark: 'rgba(0, 0, 0, .7)', // Automatic color: dark CSS color
                autoColorLight: 'rgba(255, 255, 255, .7)', // Automatic color: light CSS color
                // Position Pattern Color options
                PO: undefined, // Global Position Outer CSS color. if not set, the default is colorDark
                PI: undefined, // Global Position Inner CSS color. if not set, the default is colorDark
                PO_TL: undefined, // Position Outer CSS color - Top Left
                PI_TL: undefined, // Position Inner CSS color - Top Left
                PO_TR: undefined, // Position Outer CSS color - Top Right
                PI_TR: undefined, // Position Inner CSS color - Top Right
                PO_BL: undefined, // Position Outer CSS color - Bottom Left
                PI_BL: undefined, // Position Inner CSS color - Bottom Left
                // Alignment Color options
                AO: undefined, // Alignment Outer CSS color. if not set, the default is colorDark
                AI: undefined, // Alignment Inner CSS color. if not set, the default is colorDark
                // Timing Pattern Color options
                timing: undefined, // Global Timing CSS color. if not set, the default is colorDark
                timing_H: undefined, // Horizontal timing CSS color
                timing_V: undefined // Vertical timing CSS color
            }
        }
    }
    /**
     * Creates a scanner
     * @param {'barcode'|'QRCode'} type Scanner type 
     * @returns {Scanner}
     * @throws {Error} Invalid scanner type
     */
    createScanner(type){
        if(this.#types.map((e)=>e.toLocaleLowerCase()).includes(type.toLocaleLowerCase())) this.type = type.toLocaleLowerCase();
        else throw new Error('Invalid scanner type');
        return this;
    }
    /**
     * Sets data to the scanner
     * @param {String} txt Text to place in the scanner
     * @returns {Scanner}
     */
    setData(txt){
        this.data = txt.toString();
        return this;
    }
    /**
     * Customizes the scanner.
     * @returns {{
     *   setWidth: (w: Number) => void, // Sets the width of the scanner
     *   setHeight: (h: Number) => void, // Sets the height of the scanner
     *   setSize: (p: Number) => void, // Sets the aspect ratio size of the scanner
     *   setImage: (i: String|URL) => void, // Sets the image of the scanner
     *   setBackground (c: String) => void, // Sets the background color
     *   setColor (c: String) => void // Sets the Fore color
     *   setGradient(t: 'linear'|'radial', c: Array<{color: string, stop: number}>, x0: number, y0: number, x1: number, y1: number, target: string)
     *   setPadding (t: Number, r: Number, b: Number, l: Number) => void // Sets the padding
     * }}
     */
    customize(){
        return {
            setWidth: (w)=>{
                this.config[this.type].width = parseInt(w);
            },
            setHeight: (h)=>{
                this.config[this.type].height = parseInt(h)>0 ? parseInt(h)  : 200;
            },
            setSize: (p)=>{
                this.config[this.type].width = parseInt(p);
                this.config[this.type].height = parseInt(p);
            },
            setImage: (i)=>{
                this.config[this.type].img = this.#checkImg(i) ? i : '';
            },
            setBackground: (c)=>{
                this.config[this.type].background = c;
            },
            setColor: (c)=>{
                this.config[this.type].color = c;
            },
            setGradient: (t, c, x0=0, y0=0, x1=0, y1=0, target='background') => {
                let gradient;
                if(t.toLocaleLowerCase() === 'linear'){
                    // Validate coordinates
                    if (
                        [x0, y0, x1, y1].every(coord => typeof coord === 'number' && isFinite(coord))
                    ) {
                        const linearGradient = this.ctx.createLinearGradient(x0, y0, x1, y1);
                        c.forEach(stop => {
                            linearGradient.addColorStop(stop.stop, stop.color);
                        });
                        // Assign the gradient to the target property (default 'color')
                        gradient = linearGradient;
                    } else {
                        console.error('Invalid coordinates for createLinearGradient:', x0, y0, x1, y1);
                        return;
                    }
                } else if (t.toLocaleLowerCase() === 'radial') {
                    if (
                        [x0, y0, x1, y1].every(coord => typeof coord === 'number' && isFinite(coord))
                    ) {
                        const r0 = 0;
                        const r1 = Math.sqrt(Math.pow(x1 - x0, 2) + Math.pow(y1 - y0, 2));
                        const radialGradient = this.ctx.createRadialGradient(x0, y0, r0, x0, y0, r1);
                        c.forEach(stop => {
                            radialGradient.addColorStop(stop.stop, stop.color);
                        });
                        gradient = radialGradient;
                    } else {
                        console.error('Invalid coordinates for createRadialGradient:', x0, y0, x1, y1);
                        return;
                    }
                }
                this.config[this.type][target] = gradient;
            },
            setPadding: (t,r,b,l)=>{
                const args = [t,r,b,l].filter(i=>i!==undefined);
                if(args.length==1){
                    this.config[this.type].paddingTop = parseFloat(t)??0;
                    this.config[this.type].paddingBottom = parseFloat(t)??0;
                    this.config[this.type].paddingLeft = parseFloat(t)??0;
                    this.config[this.type].paddingRight = parseFloat(t)??0;
                }else if(args.length==2){
                    this.config[this.type].paddingTop = parseFloat(t)??0;
                    this.config[this.type].paddingBottom = parseFloat(t)??0;
                    this.config[this.type].paddingLeft = parseFloat(r)??0;
                    this.config[this.type].paddingRight = parseFloat(r)??0;
                }else{
                    this.config[this.type].paddingTop = parseFloat(t)??0;
                    this.config[this.type].paddingBottom = parseFloat(b)??0;
                    this.config[this.type].paddingLeft = parseFloat(l)??0;
                    this.config[this.type].paddingRight = parseFloat(r)??0;
                }
            }
        }
    }
    /**
     * Checks if image is valid using XMLHttpRequest
     * @param {String|URL} url URL of image
     * @returns {Boolean} TRUE if image is valid, else FALSE
     */
    #checkImg(url){
        let imageUrl;
        try {
            imageUrl = new URL(url).href;
        } catch (e) {
            return false;
        }
        const xhr = new XMLHttpRequest();
        try {
            xhr.open('HEAD', imageUrl, false); // synchronous request
            xhr.send();
            return xhr.status >= 200 && xhr.status < 400;
        } catch (e) {
            return false;
        }
    }
    
    /**
     * Update the configurations of the key
     * @param {String} key Configuration key
     * @param {*} value Value to set
     * @returns {Scanner}
     */
    setConfig(key,value){
        this.config[this.type][key] = value;
        return this;
    }
    /**
     * Generates the scanner onto your website
     * @returns {void}
     * @throws {TypeError} Invalid value type
     */
    generate(){
        if(this.type === 'barcode'){
            const symbology = this.config.barcode.symbology;
            let data = this.data || '';
            let pattern = '';
            switch (symbology) {
                case this.options.barcode.symbology.code128:
                    // Minimal Code128-B encoding for ASCII data (no checksum, no FNC chars)
                    const CODE128B = [
                        "11011001100","11001101100","11001100110","10010011000","10010001100","10001001100","10011001000","10011000100","10001100100","11001001000",
                        "11001000100","11000100100","10110011100","10011011100","10011001110","10111001100","10011101100","10011100110","11001110010","11001011100",
                        "11001001110","11011100100","11001110100","11101101110","11101001100","11100101100","11100100110","11101100100","11100110100","11100110010",
                        "11011011000","11011000110","11000110110","10100011000","10001011000","10001000110","10110001000","10001101000","10001100010","11010001000",
                        "11000101000","11000100010","10110111000","10110001110","10001101110","10111011000","10111000110","10001110110","11101110110","11010001110",
                        "11000101110","11011101000","11011100010","11011101110","11101011000","11101000110","11100010110","11101101000","11101100010","11100011010",
                        "11101111010","11001000010","11110001010","10100110000","10100001100","10010110000","10010000110","10000101100","10000100110","10110010000",
                        "10110000100","10011010000","10011000010","10000110100","10000110010","11000010010","11001010000","11110111010","11000010100","10001111010",
                        "10100111100","10010111100","10010011110","10111100100","10011110100","10011110010","11110100100","11110010100","11110010010","11011011110",
                        "11011110110","11110110110","10101111000","10100011110","10001011110","10111101000","10111100010","11110101000","11110100010","10111011110",
                        "10111101110","11101011110","11110101110","11010000100","11010010000","11010011100","11000111010"
                    ];
                    const START_B = 104, STOP = 106;
                    let codes = [START_B];
                    for(let i=0; i<data.length; i++){
                        let code = data.charCodeAt(i) - 32;
                        if(code < 0 || code > 95) code = 0; // fallback to space
                        codes.push(code);
                    }
                    // Checksum calculation
                    let checksum = START_B;
                    for(let i=1; i<codes.length; i++){
                        checksum += codes[i] * i;
                    }
                    checksum = checksum % 103;
                    codes.push(checksum);
                    codes.push(STOP);

                    // Convert codes to bar patterns
                    for(let i=0; i<codes.length; i++){
                        pattern += CODE128B[codes[i]];
                    }
                    pattern += "11"; // Termination bars
                    break;
                case this.options.barcode.symbology.code39:
                    // Code39 encoding
                    const CODE39_CHARS = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%*";
                    const CODE39_PATTERNS = [
                        "101001101101", "110100101011", "101100101011", "110110010101", "101001101011", "110100110101", "101100110101", "101001011011", "110100101101", "101100101101", // 0-9
                        "110101001011", "101101001011", "110110100101", "101011001011", "110101100101", "101101100101", "101010011011", "110101001101", "101101001101", "101011001101", // A-J
                        "110101010011", "101101010011", "110110101001", "101011010011", "110101101001", "101101101001", "101010110011", "110101011001", "101101011001", "101011011001", // K-T
                        "110010101011", "100110101011", "110011010101", "100101101011", "110010110101", "100110110101", "100101011011", "110010101101", "100110101101", "100101101101", // U-*
                        "100101101101", "100101011011", "101011110101", "101101110101", "101011101101", "110101101101", "110110101101", "110101011101", "101101011101", "101011011101"  // - . space $ / + % *
                    ];
                    // Map each character to its pattern
                    let code39Data = '*' + data.toUpperCase().replace(/[^0-9A-Z\-\.\ \$\/\+\%]/g, '') + '*';
                    pattern = '';
                    for (let i = 0; i < code39Data.length; i++) {
                        let idx = CODE39_CHARS.indexOf(code39Data[i]);
                        if (idx === -1) idx = 39; // fallback to '*'
                        pattern += CODE39_PATTERNS[idx] + '0'; // narrow space between chars
                    }
                break;
                case this.options.barcode.symbology.ean13:
                    if(isNaN(this.data)) throw new TypeError('Must be a number');
                    // EAN-13 encoding
                    function getEAN13Checksum(number) {
                        let sum = 0;
                        for (let i = 0; i < 12; i++) {
                            let n = parseInt(number[i]);
                            sum += i % 2 === 0 ? n : n * 3;
                        }
                        return (10 - (sum % 10)) % 10;
                    }
                    // EAN-13 encoding tables
                    const EAN13_L = [
                        "0001101", "0011001", "0010011", "0111101", "0100011", "0110001", "0101111", "0111011", "0110111", "0001011"
                    ];
                    const EAN13_G = [
                        "0100111", "0110011", "0011011", "0100001", "0011101", "0111001", "0000101", "0010001", "0001001", "0010111"
                    ];
                    const EAN13_R = [
                        "1110010", "1100110", "1101100", "1000010", "1011100", "1001110", "1010000", "1000100", "1001000", "1110100"
                    ];
                    // Parity pattern for first digit
                    const EAN13_PARITY = [
                        ["L","L","L","L","L","L"],
                        ["L","L","G","L","G","G"],
                        ["L","G","L","L","G","G"],
                        ["L","G","G","L","L","G"],
                        ["L","G","G","G","L","L"],
                        ["L","L","G","G","L","G"],
                        ["L","G","L","G","L","G"],
                        ["L","G","G","L","G","L"],
                        ["L","L","G","G","G","L"],
                        ["L","G","L","G","G","L"]
                    ];
                    let ean = data.replace(/[^0-9]/g, '').padStart(12, '0').slice(0,12);
                    let eanChecksum = getEAN13Checksum(ean);
                    let fullEan = ean + eanChecksum;
                    // Build pattern
                    pattern = "101"; // Start
                    let first = parseInt(fullEan[0]);
                    let parity = EAN13_PARITY[first];
                    // Left side
                    for (let i = 1; i <= 6; i++) {
                        let digit = parseInt(fullEan[i]);
                        if (parity[i-1] === "L") pattern += EAN13_L[digit];
                        else pattern += EAN13_G[digit];
                    }
                    pattern += "01010"; // Center
                    // Right side
                    for (let i = 7; i <= 12; i++) {
                        let digit = parseInt(fullEan[i]);
                        pattern += EAN13_R[digit];
                    }
                    pattern += "101"; // End
                    data = fullEan;
                    break;
                case this.options.barcode.symbology.ean8:
                    if(isNaN(this.data)) throw new TypeError('Must be a number');
                    // EAN-8 encoding
                    function getEAN8Checksum(number) {
                        let sum = 0;
                        for (let i = 0; i < 7; i++) {
                            let n = parseInt(number[i]);
                            sum += i % 2 === 0 ? n * 3 : n;
                        }
                        return (10 - (sum % 10)) % 10;
                    }
                    // EAN-8 encoding tables
                    const EAN8_L = [
                        "0001101", "0011001", "0010011", "0111101", "0100011", "0110001", "0101111", "0111011", "0110111", "0001011"
                    ];
                    const EAN8_R = [
                        "1110010", "1100110", "1101100", "1000010", "1011100", "1001110", "1010000", "1000100", "1001000", "1110100"
                    ];
                    let ean8raw = data.replace(/[^0-9]/g, '');
                    let fullEan8 = '';
                    if (ean8raw.length === 8) {
                        // If 8 digits, use all 8 digits directly
                        fullEan8 = ean8raw;
                    } else {
                        // If less than 8, pad to 7 and calculate checksum
                        let ean8 = ean8raw.padStart(7, '0').slice(0, 7);
                        let ean8Checksum = getEAN8Checksum(ean8);
                        fullEan8 = ean8 + ean8Checksum;
                    }
                    // Build pattern
                    pattern = "101"; // Start
                    // Left side (first 4 digits)
                    for (let i = 0; i < 4; i++) {
                        let digit = parseInt(fullEan8[i]);
                        pattern += EAN8_L[digit];
                    }
                    pattern += "01010"; // Center
                    // Right side (last 4 digits)
                    for (let i = 4; i < 8; i++) {
                        let digit = parseInt(fullEan8[i]);
                        pattern += EAN8_R[digit];
                    }
                    pattern += "101"; // End
                    data = fullEan8;
                break;
                case this.options.barcode.symbology.upc:
                    // UPC-A encoding (12 digits: 11 data + 1 checksum)
                    if (isNaN(this.data)) throw new TypeError('Must be a number');
                    function getUPCAChecksum(number) {
                        let sum = 0;
                        for (let i = 0; i < 11; i++) {
                            let n = parseInt(number[i]);
                            sum += i % 2 === 0 ? n * 3 : n;
                        }
                        return (10 - (sum % 10)) % 10;
                    }
                    // UPC-A encoding tables (same as EAN-13 for left and right)
                    const UPCA_L = [
                        "0001101", "0011001", "0010011", "0111101", "0100011", "0110001", "0101111", "0111011", "0110111", "0001011"
                    ];
                    const UPCA_R = [
                        "1110010", "1100110", "1101100", "1000010", "1011100", "1001110", "1010000", "1000100", "1001000", "1110100"
                    ];
                    let upcRaw = data.replace(/[^0-9]/g, '').padStart(11, '0').slice(0, 11);
                    let upcChecksum = getUPCAChecksum(upcRaw);
                    let fullUpc = upcRaw + upcChecksum;
                    // Build pattern
                    pattern = "101"; // Start
                    // Left side (first 6 digits)
                    for (let i = 0; i < 6; i++) {
                        let digit = parseInt(fullUpc[i]);
                        pattern += UPCA_L[digit];
                    }
                    pattern += "01010"; // Center
                    // Right side (last 6 digits)
                    for (let i = 6; i < 12; i++) {
                        let digit = parseInt(fullUpc[i]);
                        pattern += UPCA_R[digit];
                    }
                    pattern += "101"; // End
                    data = fullUpc;
            break;
            case this.options.barcode.symbology.itf:
                // ITF (Interleaved 2 of 5) encoding
                // Only digits, must be even length
                let itfData = data.replace(/[^0-9]/g, '');
                if (itfData.length % 2 !== 0) itfData = '0' + itfData; // pad to even length

                // Patterns for digits 0-9 (narrow and wide)
                const ITF_PATTERNS = [
                    "00110", // 0
                    "10001", // 1
                    "01001", // 2
                    "11000", // 3
                    "00101", // 4
                    "10100", // 5
                    "01100", // 6
                    "00011", // 7
                    "10010", // 8
                    "01010"  // 9
                ];

                // Start and stop patterns (standard for ITF)
                const ITF_START = "1010";  // bar-space-bar-space (narrow)
                const ITF_STOP = "11101";  // wide bar, narrow space, narrow bar, wide space, narrow bar

                // Build pattern string
                pattern = ITF_START;

                // Encode each pair of digits
                for (let i = 0; i < itfData.length; i += 2) {
                    const firstDigit = parseInt(itfData[i], 10);
                    const secondDigit = parseInt(itfData[i + 1], 10);

                    const firstPattern = ITF_PATTERNS[firstDigit];
                    const secondPattern = ITF_PATTERNS[secondDigit];

                    // Interleave bars (from first digit) and spaces (from second digit)
                    for (let j = 0; j < 5; j++) {
                        // Bar (from first digit)
                        pattern += firstPattern[j] === '1' ? '111' : '1'; // wide: 3 units, narrow: 1 unit
                        // Space (from second digit)
                        pattern += secondPattern[j] === '1' ? '000' : '0'; // wide: 3 units, narrow: 1 unit
                    }
                }

                // Append stop sequence
                pattern += ITF_STOP;

                // The pattern string now can be used to generate a barcode pattern
                // It represents widths of bars and spaces, suitable for scanning
                data = itfData;
                break;
                default:
                    // Fallback: unsupported barcode symbology
                    this.el.innerHTML = '<span style="color:red">Unsupported barcode symbology: ' + symbology + '</span>';
                return;
            }

            // Drawing
            const barWidth = 2;
            const leftPadding = this.config[this.type]?.paddingLeft??10;
            const rightPadding = this.config[this.type]?.paddingRight??10;
            const minWidth = 64;
            const barcodeWidth = pattern.length * barWidth;
            let requiredWidth = barcodeWidth + leftPadding + rightPadding;
            if (requiredWidth < minWidth) requiredWidth = minWidth;

            this.canvas.width = requiredWidth;
            this.canvas.height = this.config[this.type].height;
            // Clear canvas
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            // Set background
            // Use gradient for background if available and target is 'background', else fallback to background color
            
            this.ctx.fillStyle = this.config[this.type].background;
          
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

            // Determine font size from customization or default
            let fontStr = this.config[this.type].font ?? '12px Arial';
            let fontSizeMatch = fontStr.match(/(\d+)px/);
            let fontSize = fontSizeMatch ? parseInt(fontSizeMatch[1]) : 12;
            const barHeight = this.config[this.type].height - fontSize - 20;
            let x = leftPadding;
            for(let i=0; i<pattern.length; i++){
                if(pattern[i] === "1"){
                    // Use gradient if available and target is 'color', otherwise use color
            
                    this.ctx.fillStyle = this.config[this.type].color;
                    this.ctx.fillRect(x, 10, barWidth, barHeight);
                }
                x += barWidth;
            }

            // Draw text below barcode
            this.ctx.fillStyle = this.config[this.type].color;
            this.ctx.font = fontStr;
            // For EAN-13, draw each digit under its corresponding bar group
            if (
                symbology === this.options.barcode.symbology.ean13 &&
                (this.config[this.type]?.showLabel ?? true) &&
                typeof data === "string" &&
                data.length >= 13
            ) {
                // EAN-13: 1 digit (left), 6 digits (left), 6 digits (right)
                // Bar pattern: 3 (start) + 42 (left) + 5 (center) + 42 (right) + 3 (end) = 95 modules
                // Each digit is 7 modules wide (two bars and two spaces per digit group)
                const barWidth = 2;
                const leftPadding = this.config[this.type]?.paddingLeft ?? 10;
                const y = this.config[this.type].height - 4;

                // EAN-13 label offsets (approximate, based on standard)
                // First digit: left of the start guard
                this.ctx.textAlign = "left";
                this.ctx.fillText(data[0], leftPadding - 6, y);

                // Left 6 digits: after start guard (3 modules), each digit is 7 modules
                let x = leftPadding + 3 * barWidth;
                for (let i = 1; i <= 6; i++) {
                    this.ctx.textAlign = "center";
                    // Center each digit under its 7-module group
                    this.ctx.fillText(data[i], x + (7 * barWidth) / 2, y);
                    x += 7 * barWidth;
                }

                // Center guard (5 modules)
                x += 5 * barWidth;

                // Right 6 digits: after center guard, each digit is 7 modules
                for (let i = 7; i <= 12; i++) {
                    this.ctx.textAlign = "center";
                    this.ctx.fillText(data[i], x + (7 * barWidth) / 2, y);
                    x += 7 * barWidth;
                }
            }
            // Draw text below barcode for EAN-8
            if (symbology === this.options.barcode.symbology.ean8 && (this.config[this.type]?.showLabel ?? true)) {
                // EAN-8: 8 digits, 4 on each side of the center guard
                const barWidth = 2;
                const leftPadding = this.config[this.type]?.paddingLeft ?? 10;
                const y = this.config[this.type].height - 4;
                // Left 4 digits (after start bars)
                let x = leftPadding + 3 * barWidth + (7 * barWidth) / 2;
                for (let i = 0; i < 4; i++) {
                    this.ctx.textAlign = "center";
                    this.ctx.fillText(data[i], x, y);
                    x += 7 * barWidth;
                }
                // Skip center guard (5 modules)
                x += 5 * barWidth;
                // Right 4 digits
                for (let i = 4; i < 8; i++) {
                    this.ctx.textAlign = "center";
                    this.ctx.fillText(data[i], x, y);
                    x += 7 * barWidth;
                }
            }
            // Draw text below barcode for Code128 and Code39
            if (
                (symbology === this.options.barcode.symbology.code128 ||
                symbology === this.options.barcode.symbology.code39) &&
                (this.config[this.type]?.showLabel ?? true)
            ) {
                const label = data;
                const y = this.config[this.type].height - 4;
                this.ctx.fillStyle = this.config[this.type].color;
                this.ctx.font = fontStr;
                this.ctx.textAlign = "center";
                this.ctx.fillText(label, this.canvas.width / 2, y);
            }
            // Draw text below barcode for UPC-A
            if (
                symbology === this.options.barcode.symbology.upc ||
                symbology === this.options.barcode.symbology.itf
                &&(this.config[this.type]?.showLabel ?? true)
            ) {
                // UPC-A: 12 digits, evenly spaced and centered under the barcode
                const barWidth = 2;
                const leftPadding = this.config[this.type]?.paddingLeft ?? 10;
                const rightPadding = this.config[this.type]?.paddingRight ?? 10;
                const y = this.config[this.type].height - 4;
                const totalDigits = 12;
                const barcodeWidth = (95 * barWidth); // 95 modules for UPC-A
                const startX = leftPadding;
                // Draw the entire UPC-A number centered under the barcode
                this.ctx.textAlign = "center";
                this.ctx.fillText(data, this.canvas.width / 2, y);
            }

            // Append canvas to element
            this.el.innerHTML = '';
            this.el.appendChild(this.canvas);
        } else if (this.type === 'qrcode') {
             var QRCode;

  function QR8bitByte(data, binary, utf8WithoutBOM) {
    this.mode = QRMode.MODE_8BIT_BYTE;
    this.data = data;
    this.parsedData = [];

    function toUTF8Array(str) {
      var utf8 = [];
      for (var i = 0; i < str.length; i++) {
        var charcode = str.charCodeAt(i);
        if (charcode < 0x80) utf8.push(charcode);
        else if (charcode < 0x800) {
          utf8.push(0xc0 | (charcode >> 6), 0x80 | (charcode & 0x3f));
        } else if (charcode < 0xd800 || charcode >= 0xe000) {
          utf8.push(
            0xe0 | (charcode >> 12),
            0x80 | ((charcode >> 6) & 0x3f),
            0x80 | (charcode & 0x3f)
          );
        } else {
          i++;
          charcode =
            0x10000 +
            (((charcode & 0x3ff) << 10) | (str.charCodeAt(i) & 0x3ff));
          utf8.push(
            0xf0 | (charcode >> 18),
            0x80 | ((charcode >> 12) & 0x3f),
            0x80 | ((charcode >> 6) & 0x3f),
            0x80 | (charcode & 0x3f)
          );
        }
      }
      return utf8;
    }

    if (binary) {
      for (var i = 0, l = this.data.length; i < l; i++) {
        var byteArray = [];
        var code = this.data.charCodeAt(i);
        byteArray[0] = code;
        this.parsedData.push(byteArray);
      }
      this.parsedData = Array.prototype.concat.apply([], this.parsedData);
    } else {
      this.parsedData = toUTF8Array(data);
    }

    this.parsedData = Array.prototype.concat.apply([], this.parsedData);
    if (!utf8WithoutBOM && this.parsedData.length != this.data.length) {
      this.parsedData.unshift(191);
      this.parsedData.unshift(187);
      this.parsedData.unshift(239);
    }
  }

  QR8bitByte.prototype = {
    getLength: function (buffer) {
      return this.parsedData.length;
    },
    write: function (buffer) {
      for (var i = 0, l = this.parsedData.length; i < l; i++) {
        buffer.put(this.parsedData[i], 8);
      }
    },
  };

  function QRCodeModel(typeNumber, errorCorrectLevel) {
    this.typeNumber = typeNumber;
    this.errorCorrectLevel = errorCorrectLevel;
    this.modules = null;
    this.moduleCount = 0;
    this.dataCache = null;
    this.dataList = [];
  }

  QRCodeModel.prototype = {
    addData: function (data, binary, utf8WithoutBOM) {
      var newData = new QR8bitByte(data, binary, utf8WithoutBOM);
      this.dataList.push(newData);
      this.dataCache = null;
    },
    isDark: function (row, col) {
      if (
        row < 0 ||
        this.moduleCount <= row ||
        col < 0 ||
        this.moduleCount <= col
      ) {
        throw new Error(row + "," + col);
      }
      return this.modules[row][col][0];
    },
    getEye: function (row, col) {
      if (
        row < 0 ||
        this.moduleCount <= row ||
        col < 0 ||
        this.moduleCount <= col
      ) {
        throw new Error(row + "," + col);
      }

      var block = this.modules[row][col]; // [isDark(ture/false), EyeOuterOrInner(O/I), Position(TL/TR/BL/A) ]

      if (block[1]) {
        var type = "P" + block[1] + "_" + block[2]; //PO_TL, PI_TL, PO_TR, PI_TR, PO_BL, PI_BL
        if (block[2] == "A") {
          type = "A" + block[1]; // AI, AO
        }

        return {
          isDark: block[0],
          type: type,
        };
      } else {
        return null;
      }
    },
    getModuleCount: function () {
      return this.moduleCount;
    },
    make: function () {
      this.makeImpl(false, this.getBestMaskPattern());
    },
    makeImpl: function (test, maskPattern) {
      this.moduleCount = this.typeNumber * 4 + 17;
      this.modules = new Array(this.moduleCount);
      for (var row = 0; row < this.moduleCount; row++) {
        this.modules[row] = new Array(this.moduleCount);
        for (var col = 0; col < this.moduleCount; col++) {
          this.modules[row][col] = []; // [isDark(ture/false), EyeOuterOrInner(O/I), Position(TL/TR/BL) ]
        }
      }
      this.setupPositionProbePattern(0, 0, "TL"); // TopLeft, TL
      this.setupPositionProbePattern(this.moduleCount - 7, 0, "BL"); // BottomLeft, BL
      this.setupPositionProbePattern(0, this.moduleCount - 7, "TR"); // TopRight, TR
      this.setupPositionAdjustPattern("A"); // Alignment, A
      this.setupTimingPattern();
      this.setupTypeInfo(test, maskPattern);
      if (this.typeNumber >= 7) {
        this.setupTypeNumber(test);
      }
      if (this.dataCache == null) {
        this.dataCache = QRCodeModel.createData(
          this.typeNumber,
          this.errorCorrectLevel,
          this.dataList
        );
      }
      this.mapData(this.dataCache, maskPattern);
    },
    setupPositionProbePattern: function (row, col, posName) {
      for (var r = -1; r <= 7; r++) {
        if (row + r <= -1 || this.moduleCount <= row + r) continue;
        for (var c = -1; c <= 7; c++) {
          if (col + c <= -1 || this.moduleCount <= col + c) continue;
          if (
            (0 <= r && r <= 6 && (c == 0 || c == 6)) ||
            (0 <= c && c <= 6 && (r == 0 || r == 6)) ||
            (2 <= r && r <= 4 && 2 <= c && c <= 4)
          ) {
            this.modules[row + r][col + c][0] = true;

            this.modules[row + r][col + c][2] = posName; // Position
            if (r == -0 || c == -0 || r == 6 || c == 6) {
              this.modules[row + r][col + c][1] = "O"; // Position Outer
            } else {
              this.modules[row + r][col + c][1] = "I"; // Position Inner
            }
          } else {
            this.modules[row + r][col + c][0] = false;
          }
        }
      }
    },
    getBestMaskPattern: function () {
      var minLostPoint = 0;
      var pattern = 0;
      for (var i = 0; i < 8; i++) {
        this.makeImpl(true, i);
        var lostPoint = QRUtil.getLostPoint(this);
        if (i == 0 || minLostPoint > lostPoint) {
          minLostPoint = lostPoint;
          pattern = i;
        }
      }
      return pattern;
    },
    createMovieClip: function (target_mc, instance_name, depth) {
      var qr_mc = target_mc.createEmptyMovieClip(instance_name, depth);
      var cs = 1;
      this.make();
      for (var row = 0; row < this.modules.length; row++) {
        var y = row * cs;
        for (var col = 0; col < this.modules[row].length; col++) {
          var x = col * cs;
          var dark = this.modules[row][col][0];
          if (dark) {
            qr_mc.beginFill(0, 100);
            qr_mc.moveTo(x, y);
            qr_mc.lineTo(x + cs, y);
            qr_mc.lineTo(x + cs, y + cs);
            qr_mc.lineTo(x, y + cs);
            qr_mc.endFill();
          }
        }
      }
      return qr_mc;
    },
    setupTimingPattern: function () {
      for (var r = 8; r < this.moduleCount - 8; r++) {
        if (this.modules[r][6][0] != null) {
          continue;
        }
        this.modules[r][6][0] = r % 2 == 0;
      }
      for (var c = 8; c < this.moduleCount - 8; c++) {
        if (this.modules[6][c][0] != null) {
          continue;
        }
        this.modules[6][c][0] = c % 2 == 0;
      }
    },
    setupPositionAdjustPattern: function (posName) {
      var pos = QRUtil.getPatternPosition(this.typeNumber);
      for (var i = 0; i < pos.length; i++) {
        for (var j = 0; j < pos.length; j++) {
          var row = pos[i];
          var col = pos[j];
          if (this.modules[row][col][0] != null) {
            continue;
          }
          for (var r = -2; r <= 2; r++) {
            for (var c = -2; c <= 2; c++) {
              if (
                r == -2 ||
                r == 2 ||
                c == -2 ||
                c == 2 ||
                (r == 0 && c == 0)
              ) {
                this.modules[row + r][col + c][0] = true;
                this.modules[row + r][col + c][2] = posName; // Position
                if (r == -2 || c == -2 || r == 2 || c == 2) {
                  this.modules[row + r][col + c][1] = "O"; // Position Outer
                } else {
                  this.modules[row + r][col + c][1] = "I"; // Position Inner
                }
              } else {
                this.modules[row + r][col + c][0] = false;
              }
            }
          }
        }
      }
    },
    setupTypeNumber: function (test) {
      var bits = QRUtil.getBCHTypeNumber(this.typeNumber);
      for (var i = 0; i < 18; i++) {
        var mod = !test && ((bits >> i) & 1) == 1;
        this.modules[Math.floor(i / 3)][(i % 3) + this.moduleCount - 8 - 3][0] =
          mod;
      }
      for (var i = 0; i < 18; i++) {
        var mod = !test && ((bits >> i) & 1) == 1;
        this.modules[(i % 3) + this.moduleCount - 8 - 3][Math.floor(i / 3)][0] =
          mod;
      }
    },
    setupTypeInfo: function (test, maskPattern) {
      var data = (this.errorCorrectLevel << 3) | maskPattern;
      var bits = QRUtil.getBCHTypeInfo(data);
      for (var i = 0; i < 15; i++) {
        var mod = !test && ((bits >> i) & 1) == 1;
        if (i < 6) {
          this.modules[i][8][0] = mod;
        } else if (i < 8) {
          this.modules[i + 1][8][0] = mod;
        } else {
          this.modules[this.moduleCount - 15 + i][8][0] = mod;
        }
      }
      for (var i = 0; i < 15; i++) {
        var mod = !test && ((bits >> i) & 1) == 1;
        if (i < 8) {
          this.modules[8][this.moduleCount - i - 1][0] = mod;
        } else if (i < 9) {
          this.modules[8][15 - i - 1 + 1][0] = mod;
        } else {
          this.modules[8][15 - i - 1][0] = mod;
        }
      }
      this.modules[this.moduleCount - 8][8][0] = !test;
    },
    mapData: function (data, maskPattern) {
      var inc = -1;
      var row = this.moduleCount - 1;
      var bitIndex = 7;
      var byteIndex = 0;
      for (var col = this.moduleCount - 1; col > 0; col -= 2) {
        if (col == 6) col--;
        while (true) {
          for (var c = 0; c < 2; c++) {
            if (this.modules[row][col - c][0] == null) {
              var dark = false;
              if (byteIndex < data.length) {
                dark = ((data[byteIndex] >>> bitIndex) & 1) == 1;
              }
              var mask = QRUtil.getMask(maskPattern, row, col - c);
              if (mask) {
                dark = !dark;
              }
              this.modules[row][col - c][0] = dark;
              bitIndex--;
              if (bitIndex == -1) {
                byteIndex++;
                bitIndex = 7;
              }
            }
          }
          row += inc;
          if (row < 0 || this.moduleCount <= row) {
            row -= inc;
            inc = -inc;
            break;
          }
        }
      }
    },
  };
  QRCodeModel.PAD0 = 0xec;
  QRCodeModel.PAD1 = 0x11;
  QRCodeModel.createData = function (typeNumber, errorCorrectLevel, dataList) {
    var rsBlocks = QRRSBlock.getRSBlocks(typeNumber, errorCorrectLevel);
    var buffer = new QRBitBuffer();
    for (var i = 0; i < dataList.length; i++) {
      var data = dataList[i];
      buffer.put(data.mode, 4);
      buffer.put(
        data.getLength(),
        QRUtil.getLengthInBits(data.mode, typeNumber)
      );
      data.write(buffer);
    }
    var totalDataCount = 0;
    for (var i = 0; i < rsBlocks.length; i++) {
      totalDataCount += rsBlocks[i].dataCount;
    }
    if (buffer.getLengthInBits() > totalDataCount * 8) {
      throw new Error(
        "code length overflow. (" +
          buffer.getLengthInBits() +
          ">" +
          totalDataCount * 8 +
          ")"
      );
    }
    if (buffer.getLengthInBits() + 4 <= totalDataCount * 8) {
      buffer.put(0, 4);
    }
    while (buffer.getLengthInBits() % 8 != 0) {
      buffer.putBit(false);
    }
    while (true) {
      if (buffer.getLengthInBits() >= totalDataCount * 8) {
        break;
      }
      buffer.put(QRCodeModel.PAD0, 8);
      if (buffer.getLengthInBits() >= totalDataCount * 8) {
        break;
      }
      buffer.put(QRCodeModel.PAD1, 8);
    }
    return QRCodeModel.createBytes(buffer, rsBlocks);
  };
  QRCodeModel.createBytes = function (buffer, rsBlocks) {
    var offset = 0;
    var maxDcCount = 0;
    var maxEcCount = 0;
    var dcdata = new Array(rsBlocks.length);
    var ecdata = new Array(rsBlocks.length);
    for (var r = 0; r < rsBlocks.length; r++) {
      var dcCount = rsBlocks[r].dataCount;
      var ecCount = rsBlocks[r].totalCount - dcCount;
      maxDcCount = Math.max(maxDcCount, dcCount);
      maxEcCount = Math.max(maxEcCount, ecCount);
      dcdata[r] = new Array(dcCount);
      for (var i = 0; i < dcdata[r].length; i++) {
        dcdata[r][i] = 0xff & buffer.buffer[i + offset];
      }
      offset += dcCount;
      var rsPoly = QRUtil.getErrorCorrectPolynomial(ecCount);
      var rawPoly = new QRPolynomial(dcdata[r], rsPoly.getLength() - 1);
      var modPoly = rawPoly.mod(rsPoly);
      ecdata[r] = new Array(rsPoly.getLength() - 1);
      for (var i = 0; i < ecdata[r].length; i++) {
        var modIndex = i + modPoly.getLength() - ecdata[r].length;
        ecdata[r][i] = modIndex >= 0 ? modPoly.get(modIndex) : 0;
      }
    }
    var totalCodeCount = 0;
    for (var i = 0; i < rsBlocks.length; i++) {
      totalCodeCount += rsBlocks[i].totalCount;
    }
    var data = new Array(totalCodeCount);
    var index = 0;
    for (var i = 0; i < maxDcCount; i++) {
      for (var r = 0; r < rsBlocks.length; r++) {
        if (i < dcdata[r].length) {
          data[index++] = dcdata[r][i];
        }
      }
    }
    for (var i = 0; i < maxEcCount; i++) {
      for (var r = 0; r < rsBlocks.length; r++) {
        if (i < ecdata[r].length) {
          data[index++] = ecdata[r][i];
        }
      }
    }
    return data;
  };
  var QRMode = {
    MODE_NUMBER: 1 << 0,
    MODE_ALPHA_NUM: 1 << 1,
    MODE_8BIT_BYTE: 1 << 2,
    MODE_KANJI: 1 << 3,
  };
  var QRErrorCorrectLevel = {
    L: 1,
    M: 0,
    Q: 3,
    H: 2,
  };
  var QRMaskPattern = {
    PATTERN000: 0,
    PATTERN001: 1,
    PATTERN010: 2,
    PATTERN011: 3,
    PATTERN100: 4,
    PATTERN101: 5,
    PATTERN110: 6,
    PATTERN111: 7,
  };
  var QRUtil = {
    PATTERN_POSITION_TABLE: [
      [],
      [6, 18],
      [6, 22],
      [6, 26],
      [6, 30],
      [6, 34],
      [6, 22, 38],
      [6, 24, 42],
      [6, 26, 46],
      [6, 28, 50],
      [6, 30, 54],
      [6, 32, 58],
      [6, 34, 62],
      [6, 26, 46, 66],
      [6, 26, 48, 70],
      [6, 26, 50, 74],
      [6, 30, 54, 78],
      [6, 30, 56, 82],
      [6, 30, 58, 86],
      [6, 34, 62, 90],
      [6, 28, 50, 72, 94],
      [6, 26, 50, 74, 98],
      [6, 30, 54, 78, 102],
      [6, 28, 54, 80, 106],
      [6, 32, 58, 84, 110],
      [6, 30, 58, 86, 114],
      [6, 34, 62, 90, 118],
      [6, 26, 50, 74, 98, 122],
      [6, 30, 54, 78, 102, 126],
      [6, 26, 52, 78, 104, 130],
      [6, 30, 56, 82, 108, 134],
      [6, 34, 60, 86, 112, 138],
      [6, 30, 58, 86, 114, 142],
      [6, 34, 62, 90, 118, 146],
      [6, 30, 54, 78, 102, 126, 150],
      [6, 24, 50, 76, 102, 128, 154],
      [6, 28, 54, 80, 106, 132, 158],
      [6, 32, 58, 84, 110, 136, 162],
      [6, 26, 54, 82, 110, 138, 166],
      [6, 30, 58, 86, 114, 142, 170],
    ],
    G15:
      (1 << 10) |
      (1 << 8) |
      (1 << 5) |
      (1 << 4) |
      (1 << 2) |
      (1 << 1) |
      (1 << 0),
    G18:
      (1 << 12) |
      (1 << 11) |
      (1 << 10) |
      (1 << 9) |
      (1 << 8) |
      (1 << 5) |
      (1 << 2) |
      (1 << 0),
    G15_MASK: (1 << 14) | (1 << 12) | (1 << 10) | (1 << 4) | (1 << 1),
    getBCHTypeInfo: function (data) {
      var d = data << 10;
      while (QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G15) >= 0) {
        d ^=
          QRUtil.G15 <<
          (QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G15));
      }
      return ((data << 10) | d) ^ QRUtil.G15_MASK;
    },
    getBCHTypeNumber: function (data) {
      var d = data << 12;
      while (QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G18) >= 0) {
        d ^=
          QRUtil.G18 <<
          (QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G18));
      }
      return (data << 12) | d;
    },
    getBCHDigit: function (data) {
      var digit = 0;
      while (data != 0) {
        digit++;
        data >>>= 1;
      }
      return digit;
    },
    getPatternPosition: function (typeNumber) {
      return QRUtil.PATTERN_POSITION_TABLE[typeNumber - 1];
    },
    getMask: function (maskPattern, i, j) {
      switch (maskPattern) {
        case QRMaskPattern.PATTERN000:
          return (i + j) % 2 == 0;
        case QRMaskPattern.PATTERN001:
          return i % 2 == 0;
        case QRMaskPattern.PATTERN010:
          return j % 3 == 0;
        case QRMaskPattern.PATTERN011:
          return (i + j) % 3 == 0;
        case QRMaskPattern.PATTERN100:
          return (Math.floor(i / 2) + Math.floor(j / 3)) % 2 == 0;
        case QRMaskPattern.PATTERN101:
          return ((i * j) % 2) + ((i * j) % 3) == 0;
        case QRMaskPattern.PATTERN110:
          return (((i * j) % 2) + ((i * j) % 3)) % 2 == 0;
        case QRMaskPattern.PATTERN111:
          return (((i * j) % 3) + ((i + j) % 2)) % 2 == 0;
        default:
          throw new Error("bad maskPattern:" + maskPattern);
      }
    },
    getErrorCorrectPolynomial: function (errorCorrectLength) {
      var a = new QRPolynomial([1], 0);
      for (var i = 0; i < errorCorrectLength; i++) {
        a = a.multiply(new QRPolynomial([1, QRMath.gexp(i)], 0));
      }
      return a;
    },
    getLengthInBits: function (mode, type) {
      if (1 <= type && type < 10) {
        switch (mode) {
          case QRMode.MODE_NUMBER:
            return 10;
          case QRMode.MODE_ALPHA_NUM:
            return 9;
          case QRMode.MODE_8BIT_BYTE:
            return 8;
          case QRMode.MODE_KANJI:
            return 8;
          default:
            throw new Error("mode:" + mode);
        }
      } else if (type < 27) {
        switch (mode) {
          case QRMode.MODE_NUMBER:
            return 12;
          case QRMode.MODE_ALPHA_NUM:
            return 11;
          case QRMode.MODE_8BIT_BYTE:
            return 16;
          case QRMode.MODE_KANJI:
            return 10;
          default:
            throw new Error("mode:" + mode);
        }
      } else if (type < 41) {
        switch (mode) {
          case QRMode.MODE_NUMBER:
            return 14;
          case QRMode.MODE_ALPHA_NUM:
            return 13;
          case QRMode.MODE_8BIT_BYTE:
            return 16;
          case QRMode.MODE_KANJI:
            return 12;
          default:
            throw new Error("mode:" + mode);
        }
      } else {
        throw new Error("type:" + type);
      }
    },
    getLostPoint: function (qrCode) {
      var moduleCount = qrCode.getModuleCount();
      var lostPoint = 0;
      for (var row = 0; row < moduleCount; row++) {
        for (var col = 0; col < moduleCount; col++) {
          var sameCount = 0;
          var dark = qrCode.isDark(row, col);
          for (var r = -1; r <= 1; r++) {
            if (row + r < 0 || moduleCount <= row + r) {
              continue;
            }
            for (var c = -1; c <= 1; c++) {
              if (col + c < 0 || moduleCount <= col + c) {
                continue;
              }
              if (r == 0 && c == 0) {
                continue;
              }
              if (dark == qrCode.isDark(row + r, col + c)) {
                sameCount++;
              }
            }
          }
          if (sameCount > 5) {
            lostPoint += 3 + sameCount - 5;
          }
        }
      }
      for (var row = 0; row < moduleCount - 1; row++) {
        for (var col = 0; col < moduleCount - 1; col++) {
          var count = 0;
          if (qrCode.isDark(row, col)) count++;
          if (qrCode.isDark(row + 1, col)) count++;
          if (qrCode.isDark(row, col + 1)) count++;
          if (qrCode.isDark(row + 1, col + 1)) count++;
          if (count == 0 || count == 4) {
            lostPoint += 3;
          }
        }
      }
      for (var row = 0; row < moduleCount; row++) {
        for (var col = 0; col < moduleCount - 6; col++) {
          if (
            qrCode.isDark(row, col) &&
            !qrCode.isDark(row, col + 1) &&
            qrCode.isDark(row, col + 2) &&
            qrCode.isDark(row, col + 3) &&
            qrCode.isDark(row, col + 4) &&
            !qrCode.isDark(row, col + 5) &&
            qrCode.isDark(row, col + 6)
          ) {
            lostPoint += 40;
          }
        }
      }
      for (var col = 0; col < moduleCount; col++) {
        for (var row = 0; row < moduleCount - 6; row++) {
          if (
            qrCode.isDark(row, col) &&
            !qrCode.isDark(row + 1, col) &&
            qrCode.isDark(row + 2, col) &&
            qrCode.isDark(row + 3, col) &&
            qrCode.isDark(row + 4, col) &&
            !qrCode.isDark(row + 5, col) &&
            qrCode.isDark(row + 6, col)
          ) {
            lostPoint += 40;
          }
        }
      }
      var darkCount = 0;
      for (var col = 0; col < moduleCount; col++) {
        for (var row = 0; row < moduleCount; row++) {
          if (qrCode.isDark(row, col)) {
            darkCount++;
          }
        }
      }
      var ratio =
        Math.abs((100 * darkCount) / moduleCount / moduleCount - 50) / 5;
      lostPoint += ratio * 10;
      return lostPoint;
    },
  };
  var QRMath = {
    glog: function (n) {
      if (n < 1) {
        throw new Error("glog(" + n + ")");
      }
      return QRMath.LOG_TABLE[n];
    },
    gexp: function (n) {
      while (n < 0) {
        n += 255;
      }
      while (n >= 256) {
        n -= 255;
      }
      return QRMath.EXP_TABLE[n];
    },
    EXP_TABLE: new Array(256),
    LOG_TABLE: new Array(256),
  };
  for (var i = 0; i < 8; i++) {
    QRMath.EXP_TABLE[i] = 1 << i;
  }
  for (var i = 8; i < 256; i++) {
    QRMath.EXP_TABLE[i] =
      QRMath.EXP_TABLE[i - 4] ^
      QRMath.EXP_TABLE[i - 5] ^
      QRMath.EXP_TABLE[i - 6] ^
      QRMath.EXP_TABLE[i - 8];
  }
  for (var i = 0; i < 255; i++) {
    QRMath.LOG_TABLE[QRMath.EXP_TABLE[i]] = i;
  }

  function QRPolynomial(num, shift) {
    if (num.length == undefined) {
      throw new Error(num.length + "/" + shift);
    }
    var offset = 0;
    while (offset < num.length && num[offset] == 0) {
      offset++;
    }
    this.num = new Array(num.length - offset + shift);
    for (var i = 0; i < num.length - offset; i++) {
      this.num[i] = num[i + offset];
    }
  }

  QRPolynomial.prototype = {
    get: function (index) {
      return this.num[index];
    },
    getLength: function () {
      return this.num.length;
    },
    multiply: function (e) {
      var num = new Array(this.getLength() + e.getLength() - 1);
      for (var i = 0; i < this.getLength(); i++) {
        for (var j = 0; j < e.getLength(); j++) {
          num[i + j] ^= QRMath.gexp(
            QRMath.glog(this.get(i)) + QRMath.glog(e.get(j))
          );
        }
      }
      return new QRPolynomial(num, 0);
    },
    mod: function (e) {
      if (this.getLength() - e.getLength() < 0) {
        return this;
      }
      var ratio = QRMath.glog(this.get(0)) - QRMath.glog(e.get(0));
      var num = new Array(this.getLength());
      for (var i = 0; i < this.getLength(); i++) {
        num[i] = this.get(i);
      }
      for (var i = 0; i < e.getLength(); i++) {
        num[i] ^= QRMath.gexp(QRMath.glog(e.get(i)) + ratio);
      }
      return new QRPolynomial(num, 0).mod(e);
    },
  };

  function QRRSBlock(totalCount, dataCount) {
    this.totalCount = totalCount;
    this.dataCount = dataCount;
  }

  QRRSBlock.RS_BLOCK_TABLE = [
    [1, 26, 19],
    [1, 26, 16],
    [1, 26, 13],
    [1, 26, 9],
    [1, 44, 34],
    [1, 44, 28],
    [1, 44, 22],
    [1, 44, 16],
    [1, 70, 55],
    [1, 70, 44],
    [2, 35, 17],
    [2, 35, 13],
    [1, 100, 80],
    [2, 50, 32],
    [2, 50, 24],
    [4, 25, 9],
    [1, 134, 108],
    [2, 67, 43],
    [2, 33, 15, 2, 34, 16],
    [2, 33, 11, 2, 34, 12],
    [2, 86, 68],
    [4, 43, 27],
    [4, 43, 19],
    [4, 43, 15],
    [2, 98, 78],
    [4, 49, 31],
    [2, 32, 14, 4, 33, 15],
    [4, 39, 13, 1, 40, 14],
    [2, 121, 97],
    [2, 60, 38, 2, 61, 39],
    [4, 40, 18, 2, 41, 19],
    [4, 40, 14, 2, 41, 15],
    [2, 146, 116],
    [3, 58, 36, 2, 59, 37],
    [4, 36, 16, 4, 37, 17],
    [4, 36, 12, 4, 37, 13],
    [2, 86, 68, 2, 87, 69],
    [4, 69, 43, 1, 70, 44],
    [6, 43, 19, 2, 44, 20],
    [6, 43, 15, 2, 44, 16],
    [4, 101, 81],
    [1, 80, 50, 4, 81, 51],
    [4, 50, 22, 4, 51, 23],
    [3, 36, 12, 8, 37, 13],
    [2, 116, 92, 2, 117, 93],
    [6, 58, 36, 2, 59, 37],
    [4, 46, 20, 6, 47, 21],
    [7, 42, 14, 4, 43, 15],
    [4, 133, 107],
    [8, 59, 37, 1, 60, 38],
    [8, 44, 20, 4, 45, 21],
    [12, 33, 11, 4, 34, 12],
    [3, 145, 115, 1, 146, 116],
    [4, 64, 40, 5, 65, 41],
    [11, 36, 16, 5, 37, 17],
    [11, 36, 12, 5, 37, 13],
    [5, 109, 87, 1, 110, 88],
    [5, 65, 41, 5, 66, 42],
    [5, 54, 24, 7, 55, 25],
    [11, 36, 12, 7, 37, 13],
    [5, 122, 98, 1, 123, 99],
    [7, 73, 45, 3, 74, 46],
    [15, 43, 19, 2, 44, 20],
    [3, 45, 15, 13, 46, 16],
    [1, 135, 107, 5, 136, 108],
    [10, 74, 46, 1, 75, 47],
    [1, 50, 22, 15, 51, 23],
    [2, 42, 14, 17, 43, 15],
    [5, 150, 120, 1, 151, 121],
    [9, 69, 43, 4, 70, 44],
    [17, 50, 22, 1, 51, 23],
    [2, 42, 14, 19, 43, 15],
    [3, 141, 113, 4, 142, 114],
    [3, 70, 44, 11, 71, 45],
    [17, 47, 21, 4, 48, 22],
    [9, 39, 13, 16, 40, 14],
    [3, 135, 107, 5, 136, 108],
    [3, 67, 41, 13, 68, 42],
    [15, 54, 24, 5, 55, 25],
    [15, 43, 15, 10, 44, 16],
    [4, 144, 116, 4, 145, 117],
    [17, 68, 42],
    [17, 50, 22, 6, 51, 23],
    [19, 46, 16, 6, 47, 17],
    [2, 139, 111, 7, 140, 112],
    [17, 74, 46],
    [7, 54, 24, 16, 55, 25],
    [34, 37, 13],
    [4, 151, 121, 5, 152, 122],
    [4, 75, 47, 14, 76, 48],
    [11, 54, 24, 14, 55, 25],
    [16, 45, 15, 14, 46, 16],
    [6, 147, 117, 4, 148, 118],
    [6, 73, 45, 14, 74, 46],
    [11, 54, 24, 16, 55, 25],
    [30, 46, 16, 2, 47, 17],
    [8, 132, 106, 4, 133, 107],
    [8, 75, 47, 13, 76, 48],
    [7, 54, 24, 22, 55, 25],
    [22, 45, 15, 13, 46, 16],
    [10, 142, 114, 2, 143, 115],
    [19, 74, 46, 4, 75, 47],
    [28, 50, 22, 6, 51, 23],
    [33, 46, 16, 4, 47, 17],
    [8, 152, 122, 4, 153, 123],
    [22, 73, 45, 3, 74, 46],
    [8, 53, 23, 26, 54, 24],
    [12, 45, 15, 28, 46, 16],
    [3, 147, 117, 10, 148, 118],
    [3, 73, 45, 23, 74, 46],
    [4, 54, 24, 31, 55, 25],
    [11, 45, 15, 31, 46, 16],
    [7, 146, 116, 7, 147, 117],
    [21, 73, 45, 7, 74, 46],
    [1, 53, 23, 37, 54, 24],
    [19, 45, 15, 26, 46, 16],
    [5, 145, 115, 10, 146, 116],
    [19, 75, 47, 10, 76, 48],
    [15, 54, 24, 25, 55, 25],
    [23, 45, 15, 25, 46, 16],
    [13, 145, 115, 3, 146, 116],
    [2, 74, 46, 29, 75, 47],
    [42, 54, 24, 1, 55, 25],
    [23, 45, 15, 28, 46, 16],
    [17, 145, 115],
    [10, 74, 46, 23, 75, 47],
    [10, 54, 24, 35, 55, 25],
    [19, 45, 15, 35, 46, 16],
    [17, 145, 115, 1, 146, 116],
    [14, 74, 46, 21, 75, 47],
    [29, 54, 24, 19, 55, 25],
    [11, 45, 15, 46, 46, 16],
    [13, 145, 115, 6, 146, 116],
    [14, 74, 46, 23, 75, 47],
    [44, 54, 24, 7, 55, 25],
    [59, 46, 16, 1, 47, 17],
    [12, 151, 121, 7, 152, 122],
    [12, 75, 47, 26, 76, 48],
    [39, 54, 24, 14, 55, 25],
    [22, 45, 15, 41, 46, 16],
    [6, 151, 121, 14, 152, 122],
    [6, 75, 47, 34, 76, 48],
    [46, 54, 24, 10, 55, 25],
    [2, 45, 15, 64, 46, 16],
    [17, 152, 122, 4, 153, 123],
    [29, 74, 46, 14, 75, 47],
    [49, 54, 24, 10, 55, 25],
    [24, 45, 15, 46, 46, 16],
    [4, 152, 122, 18, 153, 123],
    [13, 74, 46, 32, 75, 47],
    [48, 54, 24, 14, 55, 25],
    [42, 45, 15, 32, 46, 16],
    [20, 147, 117, 4, 148, 118],
    [40, 75, 47, 7, 76, 48],
    [43, 54, 24, 22, 55, 25],
    [10, 45, 15, 67, 46, 16],
    [19, 148, 118, 6, 149, 119],
    [18, 75, 47, 31, 76, 48],
    [34, 54, 24, 34, 55, 25],
    [20, 45, 15, 61, 46, 16],
  ];
  QRRSBlock.getRSBlocks = function (typeNumber, errorCorrectLevel) {
    var rsBlock = QRRSBlock.getRsBlockTable(typeNumber, errorCorrectLevel);
    if (rsBlock == undefined) {
      throw new Error(
        "bad rs block @ typeNumber:" +
          typeNumber +
          "/errorCorrectLevel:" +
          errorCorrectLevel
      );
    }
    var length = rsBlock.length / 3;
    var list = [];
    for (var i = 0; i < length; i++) {
      var count = rsBlock[i * 3 + 0];
      var totalCount = rsBlock[i * 3 + 1];
      var dataCount = rsBlock[i * 3 + 2];
      for (var j = 0; j < count; j++) {
        list.push(new QRRSBlock(totalCount, dataCount));
      }
    }
    return list;
  };
  QRRSBlock.getRsBlockTable = function (typeNumber, errorCorrectLevel) {
    switch (errorCorrectLevel) {
      case QRErrorCorrectLevel.L:
        return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 0];
      case QRErrorCorrectLevel.M:
        return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 1];
      case QRErrorCorrectLevel.Q:
        return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 2];
      case QRErrorCorrectLevel.H:
        return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 3];
      default:
        return undefined;
    }
  };

  function QRBitBuffer() {
    this.buffer = [];
    this.length = 0;
  }

  QRBitBuffer.prototype = {
    get: function (index) {
      var bufIndex = Math.floor(index / 8);
      return ((this.buffer[bufIndex] >>> (7 - (index % 8))) & 1) == 1;
    },
    put: function (num, length) {
      for (var i = 0; i < length; i++) {
        this.putBit(((num >>> (length - i - 1)) & 1) == 1);
      }
    },
    getLengthInBits: function () {
      return this.length;
    },
    putBit: function (bit) {
      var bufIndex = Math.floor(this.length / 8);
      if (this.buffer.length <= bufIndex) {
        this.buffer.push(0);
      }
      if (bit) {
        this.buffer[bufIndex] |= 0x80 >>> this.length % 8;
      }
      this.length++;
    },
  };
  var QRCodeLimitLength = [
    [17, 14, 11, 7],
    [32, 26, 20, 14],
    [53, 42, 32, 24],
    [78, 62, 46, 34],
    [106, 84, 60, 44],
    [134, 106, 74, 58],
    [154, 122, 86, 64],
    [192, 152, 108, 84],
    [230, 180, 130, 98],
    [271, 213, 151, 119],
    [321, 251, 177, 137],
    [367, 287, 203, 155],
    [425, 331, 241, 177],
    [458, 362, 258, 194],
    [520, 412, 292, 220],
    [586, 450, 322, 250],
    [644, 504, 364, 280],
    [718, 560, 394, 310],
    [792, 624, 442, 338],
    [858, 666, 482, 382],
    [929, 711, 509, 403],
    [1003, 779, 565, 439],
    [1091, 857, 611, 461],
    [1171, 911, 661, 511],
    [1273, 997, 715, 535],
    [1367, 1059, 751, 593],
    [1465, 1125, 805, 625],
    [1528, 1190, 868, 658],
    [1628, 1264, 908, 698],
    [1732, 1370, 982, 742],
    [1840, 1452, 1030, 790],
    [1952, 1538, 1112, 842],
    [2068, 1628, 1168, 898],
    [2188, 1722, 1228, 958],
    [2303, 1809, 1283, 983],
    [2431, 1911, 1351, 1051],
    [2563, 1989, 1423, 1093],
    [2699, 2099, 1499, 1139],
    [2809, 2213, 1579, 1219],
    [2953, 2331, 1663, 1273],
  ];

  // android 2.x doesn't support Data-URI spec
  function _getAndroid() {
    var android = false;
    var sAgent = navigator.userAgent;

    if (/android/i.test(sAgent)) {
      // android
      android = true;
      var aMat = sAgent.toString().match(/android ([0-9]\.[0-9])/i);

      if (aMat && aMat[1]) {
        android = parseFloat(aMat[1]);
      }
    }

    return android;
  }

  //

  // QR code rendering logic
  // Use the QRCodeModel and configuration to draw the QR code on the canvas
  // (Basic implementation, you can expand for more customization)
  const qrCanvasSize = this.config.qrcode.size || 128;
  const correction = this.config.qrcode.correctLevel || this.correctionLevel.H;
  let errorLevel = QRErrorCorrectLevel.H;
  if (correction === this.correctionLevel.L) errorLevel = QRErrorCorrectLevel.L;
  else if (correction === this.correctionLevel.M) errorLevel = QRErrorCorrectLevel.M;
  else if (correction === this.correctionLevel.Q) errorLevel = QRErrorCorrectLevel.Q;

  // Determine version based on config or default
  // Auto-detect QR version if not set or set to 0, based on data length

function _getUTF8Length(sText) {
    var replacedText = encodeURI(sText)
      .toString()
      .replace(/\%[0-9a-fA-F]{2}/g, "a");
    return replacedText.length;
  }
 
  var nType = 1;
    var length = _getUTF8Length(this.data);

    for (var i = 0, len = QRCodeLimitLength.length; i < len; i++) {
      var nLimit = 0;
      switch (errorLevel) {
        case QRErrorCorrectLevel.L:
          nLimit = QRCodeLimitLength[i][0];
          break;
        case QRErrorCorrectLevel.M:
          nLimit = QRCodeLimitLength[i][1];
          break;
        case QRErrorCorrectLevel.Q:
          nLimit = QRCodeLimitLength[i][2];
          break;
        case QRErrorCorrectLevel.H:
          nLimit = QRCodeLimitLength[i][3];
          break;
      }

      if (length <= nLimit) {
        break;
      } else {
        nType++;
      }
    }



    if (nType > QRCodeLimitLength.length) {
      throw new Error(
        "Too long data. the CorrectLevel." +
          ["M", "L", "H", "Q"][nCorrectLevel] +
          " limit length is " +
          nLimit
      );
    }
      let version = (this.config.qrcode.version === 0 || this.config.qrcode.version === undefined) ? nType : this.config.qrcode.version;
  // Create QR code model
  const qr = new QRCodeModel(version, errorLevel);
  qr.addData(this.data);
  qr.make();

  // Prepare canvas
  const qrCfg = this.config[this.type];
  const size = qrCfg.width || qrCanvasSize;
  const height = qrCfg.height || size;
  this.canvas.width = size;
  this.canvas.height = height;
  this.ctx.clearRect(0, 0, size, height);

  // Set background (colorLight, background, or gradient)
  if (qrCfg.colorLight) {
    this.ctx.fillStyle = qrCfg.colorLight;
  } else if (qrCfg.background) {
    this.ctx.fillStyle = qrCfg.background;
  } else {
    this.ctx.fillStyle = "#fff";
  }
  this.ctx.globalAlpha = qrCfg.backgroundImageAlpha !== undefined ? qrCfg.backgroundImageAlpha : 1;
  this.ctx.fillRect(0, 0, size, height);
  this.ctx.globalAlpha = 1;

  // Draw background image if provided
  if (qrCfg.backgroundImage) {
    const bgImg = new window.Image();
    bgImg.crossOrigin = "Anonymous";
    bgImg.onload = () => {
      this.ctx.globalAlpha = qrCfg.backgroundImageAlpha !== undefined ? qrCfg.backgroundImageAlpha : 1;
      this.ctx.drawImage(bgImg, 0, 0, size, height);
      this.ctx.globalAlpha = 1;
      drawModulesAndLogo.call(this);
    };
    bgImg.src = qrCfg.backgroundImage;
    if (bgImg.complete) {
      this.ctx.globalAlpha = qrCfg.backgroundImageAlpha !== undefined ? qrCfg.backgroundImageAlpha : 1;
      this.ctx.drawImage(bgImg, 0, 0, size, height);
      this.ctx.globalAlpha = 1;
      drawModulesAndLogo.call(this);
    }
  } else {
    drawModulesAndLogo.call(this);
  }

  function drawModulesAndLogo() {
    // Draw quiet zone if specified
    const quietZone = qrCfg.quietZone || 0;
    if (quietZone > 0) {
      if (qrCfg.quietZone_gradient) {
        this.ctx.fillStyle = qrCfg.quietZone_gradient;
      } else {
        this.ctx.fillStyle = qrCfg.quietZoneColor || 'rgba(0,0,0,0)';
      }
      this.ctx.fillRect(0, 0, size, height);
    }

    // Draw QR modules
    const moduleCount = qr.getModuleCount();
    const tileW = (size - 2 * quietZone) / moduleCount;
    const tileH = (height - 2 * quietZone) / moduleCount;

    // Logo logic
    const logoUrl = qrCfg.logo || '';
    const logoWidth = qrCfg.logoMaxWidth !== undefined ? qrCfg.logoMaxWidth : (qrCfg.logoWidth || Math.floor(size / 3.5));
    const logoHeight = qrCfg.logoMaxHeight !== undefined ? qrCfg.logoMaxHeight : (qrCfg.logoHeight || Math.floor(height / 3.5));
    const logoX = Math.floor((size - logoWidth) / 2);
    const logoY = Math.floor((height - logoHeight) / 2);

    for (let row = 0; row < moduleCount; row++) {
      for (let col = 0; col < moduleCount; col++) {
        // Calculate pixel position for this module
        const px = Math.round(col * tileW) + quietZone;
        const py = Math.round(row * tileH) + quietZone;
        const pw = Math.ceil(tileW);
        const ph = Math.ceil(tileH);

        // Skip drawing modules that would be covered by the logo
        if (
          logoUrl &&
          px + pw > logoX &&
          px < logoX + logoWidth &&
          py + ph > logoY &&
          py < logoY + logoHeight
        ) {
          continue;
        }

        if (qr.isDark(row, col)) {
          // Determine style and scale for each module type
          let style = qrCfg.dataModuleStyle;
          const eye = qr.getEye ? qr.getEye(row, col) : null;
          let scale = qrCfg.dotScale !== undefined ? qrCfg.dotScale : 1;
          let moduleColor = null;

          if (eye) {
            if (eye.type.startsWith("PO")) {
              style = qrCfg.positionMarkerStyle;
              scale = 1; // Always solid for position outer
              // Check for gradient for position outer
              if (qrCfg.PO_gradient) {
                moduleColor = qrCfg.PO_gradient;
              } else {
                moduleColor = qrCfg[eye.type] || qrCfg[eye.type.toLowerCase()] || qrCfg.PO || qrCfg.colorDark;
              }
            } else if (eye.type.startsWith("PI")) {
              style = qrCfg.positionMarkerStyle;
              scale = 1; // Always solid for position inner
              // Check for gradient for position inner
              if (qrCfg.PI_gradient) {
                moduleColor = qrCfg.PI_gradient;
              } else {
                moduleColor = qrCfg[eye.type] || qrCfg[eye.type.toLowerCase()] || qrCfg.PI || qrCfg.colorDark;
              }
            } else if (eye.type.startsWith("AO")) {
              style = qrCfg.alignmentMarkerStyle;
              scale = qrCfg.dotScaleAO !== undefined ? qrCfg.dotScaleAO : (qrCfg.dotScaleA !== undefined ? qrCfg.dotScaleA : 1);
              // Check for gradient for alignment outer
              if (qrCfg.AO_gradient) {
                moduleColor = qrCfg.AO_gradient;
              } else {
                moduleColor = qrCfg[eye.type] || qrCfg[eye.type.toLowerCase()] || qrCfg.AO || qrCfg.colorDark;
              }
            } else if (eye.type.startsWith("AI")) {
              style = qrCfg.alignmentMarkerStyle;
              scale = qrCfg.dotScaleAI !== undefined ? qrCfg.dotScaleAI : (qrCfg.dotScaleA !== undefined ? qrCfg.dotScaleA : 1);
              // Check for gradient for alignment inner
              if (qrCfg.AI_gradient) {
                moduleColor = qrCfg.AI_gradient;
              } else {
                moduleColor = qrCfg[eye.type] || qrCfg[eye.type.toLowerCase()] || qrCfg.AI || qrCfg.colorDark;
              }
            }
          } else if (
            (row === 6 && col >= 0 && col < moduleCount) ||
            (col === 6 && row >= 0 && row < moduleCount)
          ) {
            // Check for gradient for timing pattern
            if (col === 6 && row !== 6) {
              scale = qrCfg.dotScaleTiming_V !== undefined ? qrCfg.dotScaleTiming_V : (qrCfg.dotScaleTiming !== undefined ? qrCfg.dotScaleTiming : 1);
              if (qrCfg.timing_gradient) {
                moduleColor = qrCfg.timing_gradient;
              } else if (qrCfg.timing_V_gradient) {
                moduleColor = qrCfg.timing_V_gradient;
              } else {
                moduleColor = qrCfg.timing_V || qrCfg.timing || qrCfg.colorDark;
              }
            } else if (row === 6 && col !== 6) {
              scale = qrCfg.dotScaleTiming_H !== undefined ? qrCfg.dotScaleTiming_H : (qrCfg.dotScaleTiming !== undefined ? qrCfg.dotScaleTiming : 1);
              if (qrCfg.timing_gradient) {
                moduleColor = qrCfg.timing_gradient;
              } else if (qrCfg.timing_H_gradient && qrCfg.timing_H_gradient) {
                moduleColor = qrCfg.timing_H_gradient;
              } else {
                moduleColor = qrCfg.timing_H || qrCfg.timing || qrCfg.colorDark;
              }
            } else {
              scale = qrCfg.dotScaleTiming !== undefined ? qrCfg.dotScaleTiming : 1;
              if (qrCfg.timing_gradient) {
                moduleColor = qrCfg.timing_gradient;
              } else {
                moduleColor = qrCfg.timing || qrCfg.colorDark;
              }
            }
          } else if (
            (row < 9 && col < 9) ||
            (row < 9 && col > moduleCount - 9) ||
            (row > moduleCount - 9 && col < 9)
          ) {
            scale = 1;
            // Check for gradient for version modules
            if (qrCfg.version_gradient) {
              moduleColor = qrCfg.version_gradient;
            }
          }

          // Gradient for data modules
          if (moduleColor) {
            this.ctx.fillStyle = moduleColor;
          } else if (qrCfg.autoColor && !eye) {
            // Use autoColor for data modules if enabled
            this.ctx.fillStyle = qrCfg.autoColorDark || 'rgba(0,0,0,.6)';
          } else if (style && style.fill) {
            this.ctx.fillStyle = style.fill;
          } else if (qrCfg.colorDark) {
            this.ctx.fillStyle = qrCfg.colorDark;
          } else if (qrCfg.color) {
            this.ctx.fillStyle = qrCfg.color;
          } else {
            this.ctx.fillStyle = "#000";
          }

          // Apply scaling for dotScale (only for data modules)
          const scaledW = pw * scale;
          const scaledH = ph * scale;
          const offsetX = px + (pw - scaledW) / 2;
          const offsetY = py + (ph - scaledH) / 2;
          this.ctx.fillRect(offsetX, offsetY, scaledW, scaledH);

          if (style && style.stroke) {
            this.ctx.strokeStyle = style.stroke;
            this.ctx.lineWidth = style.strokeWidth || 1;
            this.ctx.strokeRect(offsetX, offsetY, scaledW, scaledH);
          }
        } else if (qrCfg.autoColor && !qr.isDark(row, col)) {
          // Use autoColorLight for light modules if enabled
          this.ctx.fillStyle = qrCfg.autoColorLight || 'rgba(255,255,255,.7)';
          this.ctx.fillRect(px, py, pw, ph);
        }
      }
    }

    // Draw logo in the center if provided
    if (logoUrl) {
      const img = new window.Image();
      img.crossOrigin = "Anonymous";
      img.onload = () => {
        // Draw logo background if not transparent
        if (!qrCfg.logoBackgroundTransparent) {
          this.ctx.fillStyle = qrCfg.logoBackgroundColor || "#FFFFFF";
          this.ctx.fillRect(logoX, logoY, logoWidth, logoHeight);
        }
        this.ctx.drawImage(
          img,
          logoX,
          logoY,
          logoWidth,
          logoHeight
        );
      };
      img.src = logoUrl;
      if (img.complete) {
        if (!qrCfg.logoBackgroundTransparent) {
          this.ctx.fillStyle = qrCfg.logoBackgroundColor || "#FFFFFF";
          this.ctx.fillRect(logoX, logoY, logoWidth, logoHeight);
        }
        this.ctx.drawImage(
          img,
          logoX,
          logoY,
          logoWidth,
          logoHeight
        );
      }
    }
  }



  // Append canvas to element
  this.el.innerHTML = '';
  this.el.appendChild(this.canvas);

        }
    }
    /**
     * Saves the Scanner
     * @param {String} [name='scanner'] Filename
     * @param {'png'|'jpg'|'webp'|'svg'} [ext='png'] Extension
     * @returns {void} 
     */
    saveAs(name='scanner', ext='png') {
        if (!this.canvas) {
            throw new Error('No canvas to save');
        }
        let mimeType = 'image/png';
        let extension = ext.toLowerCase();
        if (extension === 'jpg' || extension === 'jpeg') {
            mimeType = 'image/jpeg';
            extension = 'jpg';
        } else if (extension === 'webp') {
            mimeType = 'image/webp';
            extension = 'webp';
        }else if(extension === 'svg'){
            const width = this.canvas.width,
            height = this.canvas.height,
            imageData = this.ctx.getImageData(0, 0, width, height);
            let svgContent = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}">`;
            svgContent += `<rect width="100%" height="100%" fill="${this.config[this.type].background}"/>`;
            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const idx = (y * width + x) * 4,
                    r = imageData.data[idx],
                    g = imageData.data[idx + 1],
                    b = imageData.data[idx + 2],
                    a = imageData.data[idx + 3];
                    if (a > 0 && (r !== 255 || g !== 255 || b !== 255)) {
                        svgContent += `<rect x="${x}" y="${y}" width="1" height="1" fill="rgb(${r},${g},${b})"/>`;
                    }
                }
            }
            svgContent += `</svg>`;
            const svgBlob = new Blob([svgContent], {type: 'image/svg+xml'});
            const svgUrl = URL.createObjectURL(svgBlob);
            const link = document.createElement('a');
            link.href = svgUrl;
            link.download = `${name}.svg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(svgUrl);
            return;
        }
        const dataURL = this.canvas.toDataURL(mimeType);
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = `${name}.${extension}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    /**
     * Prints out the canvas
     */
        print(){
          window.onload = ()=>{
            if (!this.canvas) throw new Error('No canvas to print');
          
            const dataUrl = this.canvas.toDataURL('image/png');
            const printWindow = window.open('', '_blank');
            if (printWindow) {
              printWindow.document.title = 'Print Scanner';
              const style = printWindow.document.createElement('style');
              style.textContent = `
                body { margin: 0; text-align: center; }
                img { max-width: 100%; height: auto; }
              `;
              printWindow.document.head.appendChild(style);
              const img = printWindow.document.createElement('img');
              img.src = dataUrl;
              img.onload = function() {
                printWindow.focus();
                printWindow.print();
                setTimeout(() => { printWindow.close(); }, 1);
              };
              printWindow.document.body.appendChild(img);
            } else throw new Error('Windows popup is blocked');
          };
        
        }

    /**
     * Generates a 2FA/MAF URL
     * @param {String} account Account name
     * @param {String} secrete Base32 secrete key
     * @param {String} issuer Issuer name
     * @param {'SHA1'|'SHA256'|'SHA512'|'MD5'} [algo='SHA1'] Algorithm type, default **SHA1**
     * @param {Number} [digits=6] Number of digits, default **6**
     * @param {Number} [period=30] Number of seconds, default **30** 
     * @param {String} [image=''] [Optional] - Image to the authentication app
     * @returns {String} OTPAUTH URL
     */
    generate2FA(account, secrete, issuer, algo='SHA1', digits=6, period=30, image=''){
        //check for base32
        const base32Regex = /^(?:[A-Z2-7]{8})*(?:[A-Z2-7]{2}={6}|[A-Z2-7]{4}={4}|[A-Z2-7]{5}={3}|[A-Z2-7]{7}=)?$/i;
        if (!base32Regex.test(secrete)) {
            throw new Error('Secret must be a valid base32 string');
        }
        return `otpauth://totp/${encodeURIComponent(account)}?secret=${encodeURIComponent(secrete)}&issuer=${encodeURIComponent(issuer)}&algorithm=${encodeURIComponent(algo.toLocaleUpperCase())}&digits=${encodeURIComponent(parseInt(digits))}&period=${encodeURIComponent(parseInt(period))}${image && image !== '' ? `&image=${encodeURIComponent(image)}` : ''}`;
    }
    
}