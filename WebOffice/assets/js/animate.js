/**
 * AnimateJS class: Main class to handle animations and utilities
 */
const AnimateJS = class {
    constructor() {
        this.INFINITE = -1; // Constant to denote infinite animation
        // Timing
        this.EASE = 'ease';
        this.LINEAR="linear";
        this.EASE_IN = "ease-in";
        this.EASE_OUT = "ease-out"
        this.EASE_IN_OUT = "ease-in-out";
        /**
         * Returns the cubic-bezier of the animation
         * @param {number} x1 X-axis first control point
         * @param {number} y1 Y-axis first control point
         * @param {number} x2 X-axis second control point
         * @param {number} y2 Y-axis second control point
         * @returns {string} cubic-bezier format
         */
        this.CUBIC_BEZIER = function(x1,y1,x2,y2){if(arguments.length<4) throw new Error('Must have four parameters.');return `cubic-bezier(${x1},${y1},${x2},${y2})`;}
        // Direction
        this.NORMAL = "normal";
        this.REVERSE = "reverse";
        this.ALT = "alternate";
        this.ALT_REVERSE = "alternate-reverse";
        // Mode
        this.NONE = "none";
        this.FORWARDS = "forwards";
        this.BACKWARDS = "backwards";
        this.BOTH = "both";

        // Speed
        this.SUPER_SLOW = 5000;
        this.SLOW = 3000;
        this.MODERATE = 1000;
        this.FAST = 500;
        this.SUPER_FAST = 200;

        //Colors
        this.RED = "#ff0000";
        this.BROWN = "#a52a2a";
        this.TAN = "#d2b48c";
        this.ORANGE = "#ffa500";
        this.YELLOW = "#ffff00";
        this.GOLD = "#ffd700";
        this.GREEN = "#008000";
        this.LIME = "#00ff00";
        this.BLUE = "#0000ff";
        this.CYAN = "#00ffff";
        this.NAVY = "#000080";
        this.PURPLE = "#800080";
        this.MAGENTA = "#ff00ff";
        this.GRAY = "#808080";
        this.LIGHT_GRAY = "#d3d3d3";
        this.PINK = "#ffc0cb";
        this.BLACK = "#000";
        this.WHITE = "#FFF";
        //Transparency
        this.OPACITY = 1;
        this.OPACITY_HALF = 0.5;
        this.NO_OPACITY = 0;


        // Utilities for DOM interactions and style merging
        this.Utils = {
            /**
             * Checks if an element is within the viewport
             * @param {Element} el - DOM element to check
             * @returns {boolean} - true if in view
             */
            inView: (el) => {
                const rect = el.getBoundingClientRect(),
                    windowHeight = window.innerHeight || document.documentElement.clientHeight,
                    windowWidth = window.innerWidth || document.documentElement.clientWidth,
                    inVerticalView = rect.top < windowHeight && rect.bottom > 0,
                    inHorizontalView = rect.left < windowWidth && rect.right > 0;
                return inVerticalView && inHorizontalView;
            },

            /**
             * Selects all elements under target selector.
             * **Reference:** _(document.querySelectorAll)_
             * @param {string} el CSS selector
             * @returns {NodeList} Returns all element descendants of node that match selectors.
             */
            $: (el) => document.querySelectorAll(el),
            /**
             * Selects an element under target selector.
             * **Reference:** _(document.querySelector)_
             * @param {string} el CSS selector
             * @returns {any} Returns the first element that is a descendant of node that matches selectors.
             */
            $$:(el)=>document.querySelector(el),
            /**
             * Merges a new animation string with existing styles
             * @param {Element} el - DOM element
             * @param {string} animation - animation string to merge
             */
            Merger: (el, animation) => {
                const currentAnimations = el.style.animation ? el.style.animation.split(',').map(anim => anim.trim()) : [];
                if (!currentAnimations.includes(animation)) {
                    el.style.animation = currentAnimations.length ? `${el.style.animation}, ${animation}` : animation;
                }
            },
            /**
             * Preservers 3D to an element
             * @param {Element} el Element to preserve 3D
             */
            preserve3D: (el)=>{
                el.style.transformStyle = "preserve-3d";
            },
            /**
             * Create an HTML tag
             * @param {String} tag HTML tag
             * @returns HTML Object
             */
            create: (tag)=>{
                return document.createElement(tag);
            },
            /**
             * Insert object to container
             * @param {Element} parent Parent element to insert
             * @param {'beforebegin'|'afterbegin'|'beforeend'|'afterend'} position Position
             * @param {Element} input Created element
             */
            insert: (parent,position,input)=>{
                parent.insertAdjacentElement(position,input);
            },
            /**
             * Returns the contrast ratio between 2 colors
             * @param {string} color1 Background color
             * @param {string} color2 Fore color
             * @returns {number} Ratio between to colors 
             */
            contrast: (color1, color2)=>{
                const hex2rgb = (hex)=>{
                    hex = hex.replace(/^#/, '');
                    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
                    const bigint = parseInt(hex, 16);
                    const r = (bigint >> 16) & 255;
                    const g = (bigint >> 8) & 255;
                    const b = bigint & 255;
                    return { r, g, b };
                },
                getLuminance = ({r,g,b})=>{
                    const srgb = [r, g, b].map(c => {
                        c /= 255;
                        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                    });
                    // Constants for luminance calculation
                    return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
                },
                lum1 = getLuminance(hex2rgb(color1)),
                lum2 = getLuminance(hex2rgb(color2)),
                brightest = Math.max(lum1,lum2),
                darkest = Math.min(lum1,lum2);
                return parseFloat(((brightest+0.05)/(darkest+0.05)).toFixed(2));
            },
            /**
             * Generates a CSS gradient string (linear or radial) based on provided options.
             * Validates input options and returns a properly formatted gradient function.
             *
             * @param {Object} config - Configuration object for the gradient.
             * @param {string} [config.type='linear'] - Type of gradient: 'linear' or 'radial'.
             * @param {string} [config.direction='to right'] - Direction for linear gradients.
             * @param {Array} [config.colors=[]] - Array of color stops for the gradient.
             * @param {string} [config.shape='ellipse'] - Shape for radial gradients: 'circle' or 'ellipse'.
             * @param {string} [config.size='farthest-corner'] - Size for radial gradients.
             * @param {string} [config.position='center'] - Position for radial gradients.
             * @returns {string} - CSS gradient function string.
             * @throws Will throw an error if colors array is empty or invalid options are provided.
             */
            gradient(config = {}) {
                const {
                    type = 'linear', // 'linear' or 'radial'
                    direction = 'to right', // for linear gradients
                    colors = [], // array of color stops
                    shape = 'ellipse', // for radial gradients: 'circle' or 'ellipse'
                    size = 'farthest-corner', // for radial gradients
                    position = 'center' // for radial gradients
                } = config;

                if (!Array.isArray(colors) || colors.length === 0) {
                    throw new Error('Colors array must contain at least one color.');
                }

                // Create color stops string
                const colorStops = colors.join(', ');

                if (type === 'linear') {
                    // Validate direction
                    const validDirections = [
                    'to top', 'to bottom', 'to left', 'to right',
                    'to top left', 'to top right', 'to bottom left', 'to bottom right'
                    ];
                    const dir = validDirections.includes(direction) ? direction : 'to right';

                    return `linear-gradient(${dir}, ${colorStops})`;
                } else if (type === 'radial') {
                    // Validate shape
                    const shapeType = (shape === 'circle' || shape === 'ellipse') ? shape : 'ellipse';

                    // Validate size
                    const sizeOptions = [
                    'closest-side', 'closest-corner',
                    'farthest-side', 'farthest-corner'
                    ];
                    const sz = sizeOptions.includes(size) ? size : 'farthest-corner';

                    return `radial-gradient(${shapeType} ${sz} at ${position}, ${colorStops})`;
                } else throw new Error('Invalid gradient type. Use "linear" or "radial".');
                
            }
        };

        this.Listener = {
            /**
             * Checks when animations starts
             * @param {Element} el Element
             * @param {Function} callback Callback
             * @returns {this}
             */
            start: (el, callback)=>{
                el.addEventListener('animationstart',()=>callback(this,el));
                return this;
            },
            /**
             * Attach a callback for when the animation repeats (iteration) on an element.
             * @param {Element} el - DOM element.
             * @param {Function} callback - Function to execute on animation iteration.
             * @returns {AnimateJS}
             */
            iteration: function(el, callback) {
                let iterationCount = 0;
                const handler = () => {
                    iterationCount++;
                    callback(this,el,iterationCount);
                };
                el.addEventListener('animationiteration', handler);
                return this;
            },
            /**
             * Attach a callback for when the animation ends on an element.
             * @param {Element} el - DOM element.
             * @param {Function} callback - Function to execute on animation end.
             * @returns {AnimateJS}
             */
            end: (el,callback)=>{
                el.addEventListener('animationend',()=>callback(this,el));
                return this;
            },
            /**
             * Attach callbacks for all animation events (start, iteration, end).
             * @param {Element} el - DOM element.
             * @param {{s: Function, i: Function, e: Function}} callbacks - Object with optional callbacks: { start, iteration, end }
             * @returns {AnimateJS}
             */
            animation: (el, {s,i,e})=>{
                if(s) this.Listener.start(el,s);
                if(i) this.Listener.iteration(el,i);
                if(e) this.Listener.end(el,e);
                return this;
            }
        }

        this.draw = {
            /**
             * Creates a 3D cube within a specified container element, with customizable properties.
             * 
             * @param {Object} options - Configuration options for the cube.
             * @param {HTMLElement | string} options.container - The DOM element or selector where the cube will be rendered.
             * @param {number} [options.width=200] - Width of the scene container in pixels.
             * @param {number} [options.height=200] - Height of the scene container in pixels.
             * @param {number} [options.margin=0] - Margin around the scene container in pixels.
             * @param {number} [options.perspective=500] - Perspective distance for 3D effect in pixels.
             * @param {Object} [options.label] - Configuration for labels on each face.
             * @param {boolean} [options.label.enabled=false] - Whether to display labels on each face.
             * @param {string} [options.label.color='black'] - Color of the label text.
             * @param {Object} [options.face] - Styling options for each face of the cube.
             * @param {Object} [options.face.front] - Styling for the front face.
             * @param {string} [options.face.front.border='black'] - Border color for the front face.
             * @param {string} [options.face.front.background='skyblue'] - Background color for the front face.
             * @param {number} [options.face.front.opacity=this.OPACITY_HALF] - Opacity for the front face.
             * @param {Object} [options.face.back] - Styling for the back face.
             * @param {string} [options.face.back.border='black'] - Border color for the back face.
             * @param {string} [options.face.back.background='skyblue'] - Background color for the back face.
             * @param {number} [options.face.back.opacity=this.OPACITY_HALF] - Opacity for the back face.
             * @param {Object} [options.face.top] - Styling for the top face.
             * @param {string} [options.face.top.border='black'] - Border color for the top face.
             * @param {string} [options.face.top.background='skyblue'] - Background color for the top face.
             * @param {number} [options.face.top.opacity=this.OPACITY_HALF] - Opacity for the top face.
             * @param {Object} [options.face.bottom] - Styling for the bottom face.
             * @param {string} [options.face.bottom.border='black'] - Border color for the bottom face.
             * @param {string} [options.face.bottom.background='skyblue'] - Background color for the bottom face.
             * @param {number} [options.face.bottom.opacity=this.OPACITY_HALF] - Opacity for the bottom face.
             * @param {Object} [options.face.left] - Styling for the left face.
             * @param {string} [options.face.left.border='black'] - Border color for the left face.
             * @param {string} [options.face.left.background='skyblue'] - Background color for the left face.
             * @param {number} [options.face.left.opacity=this.OPACITY_HALF] - Opacity for the left face.
             * @param {Object} [options.face.right] - Styling for the right face.
             * @param {string} [options.face.right.border='black'] - Border color for the right face.
             * @param {string} [options.face.right.background='skyblue'] - Background color for the right face.
             * @param {number} [options.face.right.opacity=this.OPACITY_HALF] - Opacity for the right face.
             * 
             * @example
             * cube({
             *   container: '#sceneContainer',
             *   width: 300,
             *   height: 300,
             *   perspective: 600,
             *   label: { enabled: true, color: 'red' },
             *   face: {
             *     front: { background: 'red', border: 'blue', opacity: 0.8 },
             *     back: { background: 'green' },
             *     top: { background: 'yellow' },
             *     bottom: { background: 'purple' },
             *     left: { background: 'orange' },
             *     right: { background: 'pink' }
             *   }
             * });
             */
            cube: (options = {}) => {
                const {
                    container,
                    width = 200,
                    height = 200,
                    margin = 0,
                    perspective = 500,
                    label = { enabled: false, color: 'black' },
                    face = {
                        front: { border: 'black', background: 'skyblue', opacity: this.OPACITY_HALF },
                        back: { border: 'black', background: 'skyblue', opacity: this.OPACITY_HALF },
                        top: { border: 'black', background: 'skyblue', opacity: this.OPACITY_HALF },
                        left: { border: 'black', background: 'skyblue', opacity: this.OPACITY_HALF },
                        right: { border: 'black', background: 'skyblue', opacity: this.OPACITY_HALF },
                        bottom: { border: 'black', background: 'skyblue', opacity: this.OPACITY_HALF }
                    }
                } = options;

                const scene = (typeof container === 'string') ? this.Utils.$$(container) : container;

                if (!scene) {
                    console.error('Container element not found for cube.');
                    return;
                }


                scene.style.perspective = `${perspective}px`;
                scene.style.width = `${width}px`;
                scene.style.height = `${height}px`;
                scene.style.margin = `${margin}px`;
                scene.classList.add('cube-scene');

                const cube = this.Utils.create('div');
                cube.className = 'cube';
                this.Utils.insert(scene, 'afterbegin', cube);

                const faces = ['front', 'back', 'top', 'bottom', 'left', 'right'];
                for (const f of faces) {
                    const faceOptions = face[f] || {};
                    const faceDiv = this.Utils.create('div');
                    faceDiv.className = `face ${f}`;

                    if (label && label.enabled) {
                        faceDiv.innerHTML = `<span style="color:${label.color};">${f}</span>`;
                    }

                    faceDiv.style.border = `2px solid ${faceOptions.border ?? 'black'}`;
                    faceDiv.style.background = faceOptions.background ?? 'skyblue';
                    faceDiv.style.opacity = `${faceOptions.opacity ?? this.OPACITY_HALF}`;

                    this.Utils.insert(cube, 'beforeend', faceDiv);
                }
            },
            /**
             * Options for creating a customizable sphere.
             * @param {Object} options - Configuration options for the sphere.
             * @param {string|HTMLElement} options.container - Selector or DOM element to contain the sphere.
             * @param {Object} [options.face={}] - Default style options for each face.
             * @param {string} [options.face.background='linear-gradient(to right, #ff7e5f, #feb47b)'] - Background style for faces (can be gradient or color).
             * @param {string} [options.face.backgroundType='linear'] - Type of background: 'linear' or 'radial'.
             * @param {number} [options.diameter=200] - Diameter of the sphere in pixels.
             * @param {Object} [options.faces] - Specific configurations for individual faces (face1 to face8).
             * @example
             *      Each face can override default styles, e.g.,
             *      {
             *        face1: { background: 'radial-gradient(circle, #f6d365, #fda085)' },
             *        face2: { background: 'linear-gradient(to right, #a1c4fd, #c2e9fb)' },
             *        // ... up to face8
             *      }
             */
            sphere: (options={}) => {
                const {
                    container,
                    face={},
                    diameter=200,
                    faces={} // Specific styles for individual faces
                } = options;

                // Determine parent element
                const parent = (typeof container==='string' ? this.Utils.$$(container) : container); 

                // Set up container styles
                parent.classList.add('sphere-scene');
                parent.style.width = `${diameter}px`;
                parent.style.height = `${diameter}px`;

                // Create the body element
                const body = this.Utils.create('div');
                body.className = "clip-circle";

                // Generate face styles based on options
                const faceStyles = [
                    { id: 'face1', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' },
                    { id: 'face2', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' },
                    { id: 'face3', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' },
                    { id: 'face4', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' },
                    { id: 'face5', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' },
                    { id: 'face6', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' },
                    { id: 'face7', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' },
                    { id: 'face8', defaultBackground: face.background || 'linear-gradient(to right, #ff7e5f, #feb47b)' }
                ];

                // Build inner HTML for faces with individual styles
                const facesHTML = faceStyles.map(faceStyle => {
                    const specificFace = faces[faceStyle.id] || {};
                    const background = specificFace.background || faceStyle.defaultBackground;
                    return `<div id="${faceStyle.id}" class="face" style="background: ${background};"></div>`;
                }).join('');

                body.innerHTML = `
                    <div class="sphere-wrapper">
                        <div class="sphere-2">
                            <div class="sphere">
                                ${facesHTML}
                            </div>
                        </div>
                    </div>
                `;

                // Insert the created element into the parent
                this.Utils.insert(parent, 'afterbegin', body);
            },
            /**
             * Creates and appends a shadow element with gradient styling to a specified container.
             * 
             * @param {Object} options - Configuration options for the shadow.
             * @param {string|HTMLElement} options.container - Selector string or DOM element to contain the shadow.
             * @param {Array<string>} [options.gradientColors=['#000f', '#fff0']] - Array of gradient colors for the background.
             * @param {number} [options.width=600] - Width of the shadow element in pixels.
             * @param {number} [options.height=50] - Height of the shadow element in pixels.
             * @param {Object} [options.margin={top: 0, right: 0, bottom: 0, left: 0}] - Margin around the shadow element.
             * @param {number} [options.radius=50] - Border radius percentage for rounded corners.
             * @param {number} [options.stop=72] - Percentage stop point for the gradient transition.
             * @param {string} [options.gradientType='linear'] - Type of gradient: 'linear' or 'radial'.
             * 
             * This function creates a div element styled with a gradient to simulate a shadow,
             * and appends it as the last child within the specified container element.
             */
            shadow: (options = {}) => {
                const {
                    container,
                    gradientColors = ['#000f', '#fff0'],
                    width = 600,
                    height = 50,
                    margin = { top: 0, right: 0, bottom: 0, left: 0 },
                    radius = 50,
                    size = 300,
                    gradientType = 'linear' // default to linear gradient
                } = options;

                const parentEl = (typeof container === 'string') ? this.Utils.$$(container) : container;
                if (!parentEl) {
                    console.error('Container element not found.');
                    return;
                }

                const shadow = this.Utils.create('div');
                shadow.className = 'shadow';
                shadow.style.width = `${width}px`;
                shadow.style.height = `${height}px`;
                shadow.style.margin = `${margin.top ?? 0}px ${margin.right ?? 0}px ${margin.bottom ?? 0}px ${margin.left ?? 0}px`;
                shadow.style.borderRadius = `${radius}%`;

                // Use this.Utils.gradient() here
                if (gradientType === 'radial') {
                    // Generate radial gradient string
                    shadow.style.background = this.Utils.gradient({
                        type: 'radial',
                        shape: 'circle', // or 'ellipse' if needed
                        position: 'center', // or other position
                        colors: gradientColors
                    });
                } else {
                    // Generate linear gradient string
                    shadow.style.background = this.Utils.gradient({
                        type: 'linear',
                        direction: 'to bottom',
                        colors: gradientColors
                    });
                }
                shadow.style.backgroundSize = `${size}% ${size}%`;
                // Append the shadow element to the container
                this.Utils.insert(parentEl, 'beforeend', shadow);
                }
        }

        /**
         * Collection of predefined animations with various directions and types
         */
        this.Animations = {
            fade: {
                // Fade In
                in: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeIn ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out
                out: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOut ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Left
                inLeft: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Left
                outLeft: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Right
                inRight: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Right
                outRight: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Top
                inTop: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Top
                outTop: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Bottom
                inBottom: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Bottom
                outBottom: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                }
            },
            slide: {
                // Slide In from Top
                inTop: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Top
                outTop: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide In from Bottom
                inBottom: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Bottom
                outBottom: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide In from Left
                inLeft: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Left
                outLeft: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide In from Right
                inRight: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Right
                outRight: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                }
            },
            rotate:{
                 // Rotate In (clockwise)
                in: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `rotateIn ${duration}ms ${timing} ${delay}ms ${count} ${direction}  ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Rotate Out (counterclockwise)
                out: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `rotateOut ${duration}ms ${timing} ${delay}ms ${count} ${direction}  ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Rotate Clockwise
                clockwise: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `rotateClockwise ${duration}ms ${timing} ${delay}ms ${count} ${direction}  ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Rotate Counterclockwise
                counterClockwise: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `rotateCounterClockwise ${duration}ms ${timing} ${delay}ms ${count} ${direction}  ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                }
            },
            zoom:{
                // Zoom In
                in: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `zoomIn ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Zoom Out
                out: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `zoomOut ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                }
            },
            bounce: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                const animStr = `bounce ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                if (merge) this.Utils.Merger(el, animStr);
                else el.style.animation = animStr;
            },
            stretch:{
                // Stretch width
                width: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `stretchWidth ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Stretch height
                height: (el, timing='ease', duration=this.MODERATE, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `stretchHeight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                }
            }
        };

        // Create a style element in document head for custom keyframes if not already exists
        if (!this.Utils.$$('#animateJS-animations')) {
            const css = document.createElement('style');
            css.id = 'animateJS-animations';
            document.head.appendChild(css);
        }
    }

    /**
     * Trigger animation on an element based on predefined animation paths
     * @param {Element} el - DOM element to animate
     * @param {string} animationPath - dot-separated path to animation in Animations
     * @param {{timing: 'linear'|'ease'|'ease-in'|'ease-out'|'ease-in-out'|'cubic-bezier(n,n,n,n)', duration: number, delay: number, count: number, direction: 'normal'|'reverse'|'alternate'|'alternate-reverse' , mode: 'none'|'forwards'|'backwards'|'both'}} options - animation options like duration, delay etc.
     * @returns {Promise} - resolves after animation ends
     */
    animate(el, animationPath, options = {}) {
        const {
            timing = 'linear',
            duration = this.MODERATE,
            delay = 0,
            count = 1,
            direction = 'normal',
            mode = 'forwards',
            merge = false
        } = options;

        const parts = animationPath.split('.');
        let current = this.Animations;

        // Traverse the animation path to reach the specific animation function
        for (let i = 0; i < parts.length; i++) {
            if (current[parts[i]] !== undefined) {
                current = current[parts[i]];
            } else {
                console.error('Animation not found:', animationPath);
                return Promise.resolve();
            }
        }

        // Ensure the target is a function
        if (typeof current !== 'function') {
            console.error('Animation is not a function:', animationPath);
            return Promise.resolve();
        }

        // Call the animation function
        current(el, timing, duration, delay, (count === this.INFINITE ? 'infinite' : count), direction, mode, merge);

        // Return a Promise that resolves after animation ends or immediately if infinite
        return new Promise((resolve) => {
            if (count === this.INFINITE) {
                resolve();
            } else {
                const handler = () => {
                    el.removeEventListener('animationend', handler);
                    resolve();
                };
                el.addEventListener('animationend', handler);
            }
        });
    }

    // Other utility methods for event handling, styling, waiting, adding custom animations, and running sequences

    /**
     * Triggers animation on scroll
     * @param {Element} el - Element to attach the scroll event.
     * @param {Function} callback - Callback function invoked on scroll.
     * @returns {AnimateJS}
     */
    scroll(el, callback) {
        el.addEventListener('scroll', ()=>callback(this, el));
        return this;
    }

    /**
     * Triggers animation on load
     * @param {Element} el - Element to attach the load event.
     * @param {Function} callback - Callback function invoked on load.
     * @returns {AnimateJS}
     */
    load(el, callback) {
        el.addEventListener('load', ()=>callback(this, el));
        return this;
    }
    /**
     * Triggers animation on hover
     * @param {Element} el - Element to attach the hover event.
     * @param {Function} start - Callback function invoked on hover.
     * @param {Function} end - Callback function invoked off hover.
     * @returns {AnimateJS}
     */
    hover(el,start,end=(a,e)=>{e.style.animation = '';}){
        el.addEventListener('mouseenter',()=>start(this, el));
        el.addEventListener('touchstart',()=>start(this, el));
        el.addEventListener('mouseleave',()=>end(this,el));
        el.addEventListener('touchend',()=>end(this,el));
        return this;
    }

    /**
     * Triggers animation on click
     * @param {Element} el - Element to attach the click event.
     * @param {Function} callback - Callback function invoked on click.
     * @returns {AnimateJS}
     */
    click(el,callback){
        el.addEventListener('click',()=>callback(this,el));
        return this;
    }

    /**
     * Initialize element styles
     */
    init(el, styles = {}) {
        const styleString = Object.entries(styles)
            .map(([key, value]) => `${key}: ${value}`)
            .join('; ');
        el.setAttribute('style', styleString);
        return this;
    }

    /**
     * Wait for specified milliseconds before executing callback
     */
    wait(callback, ms=1000) {
        setTimeout(callback, ms);
        return this;
    }

    /**
     * Add a custom animation to the collection
     * @param {AnimationJS} animation Animation script
     */
    add(animation) {
        if (!(animation instanceof AnimationJS))
            throw new Error('Must be an instance of AnimationJS');
        if (!animation.callback) throw new Error(`${animation.path} hasn't been developed. You must use develop() method`);
        const parts = animation.path.split('.');
        let current = this.Animations;

        // Insert the callback into the animation collection
        for (let i = 0; i < parts.length; i++) {
            const part = parts[i];
            if (i === parts.length - 1) {
                current[part] = animation.callback;
            } else {
                if (current[part] !== undefined) {
                    current = current[part];
                } else {
                    current[part] = {};
                    current = current[part];
                }
            }
        }

        // Append custom CSS keyframes if provided
        if (animation.css) {
            const styleEl = this.Utils.$$('#animateJS-animations');
            if (styleEl) {
                styleEl.innerHTML += animation.css;
            }
        }
        return this;
    }

    /**
     * Run multiple animations sequentially
     */
    async run(...animations) {
        for (const animation of animations) {
            if (typeof animation === 'function') {
                await animation();
            } else if (animation instanceof Promise) {
                await animation;
            } else {
                console.warn('Animation must be a function or promise:', animation);
            }
        }
    }
};

/**
 * AnimationJS class: Helper for creating custom keyframe animations
 */
const AnimationJS = class {
    #begin;   // String for start of keyframes
    #keyframes; // String for keyframe definitions
    #end;     // String for end of keyframes
    
    /**
     * @constructor
     * @param {...string} path Path naming
     */
    constructor(...path) {
        this.path = path.join('.') ?? ''; // Path for naming
        this.name = `${this.#firstIndex(path)}_${this.#lastIndex(path)}`; // Animation name derived from last path segment
        this.#begin = `@keyframes ${this.name}{`; // Start of keyframes CSS
        this.#keyframes = ''; // Initialize keyframes content
        this.#end = `}`; // End of keyframes CSS
        this.css = ''; // Final CSS string
        this.callback = null; // Function to execute for animation
        this.Utils = {
            /**
             * Merge animation string into element styles
             */
            Merger: (el, animation) => {
                el.style.animation = el.style.animation ? `${el.style.animation}, ${animation}` : animation;
            }
        };
    }

    /**
     * Helper to get last element of path array
     */
    #lastIndex(array) {
        return array[array.length - 1];
    }
    /**
     * Helper to get the first element of path array
     */
    #firstIndex(array) {
        return array[0];
    }

    /**
     * Create a keyframe timeline with specific frames and keyframes
     * @param {string} frame - Frame percentage or label (e.g., '50')
     * @param {...KeyFrames} keyframes - Keyframe definitions
     * @returns {AnimationJS} - for chaining
     */
    createTimeline(frame, ...keyframes) {
        frame = frame.toString();
        this.#keyframes += `${frame.match(/\d+/) ? `${frame}%` : frame}{`;
        keyframes.forEach((kf) => {
            if(!(kf instanceof KeyFrames)) throw new Error('Must be a keyframe object');
            this.#keyframes += kf.finalize();
        });
        this.#keyframes += `}`;
        this.css = `${this.#begin}${this.#keyframes}${this.#end}`;
        return this;
    }

    /**
     * Develop the animation by defining its callback function
     */
    develop() {
        this.callback = (el, timing='ease', duration=3000, delay=0, count=1, direction='normal', mode='none', merge=false) => {
            const animation = `${this.name} ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
            if(merge) this.Utils.Merger(el, animation);
            else el.style.animation = animation;
        };
        return this;
    }
}
const KeyFrames = class {
    /**
     * Constructor initializes all style-related properties.
     */
    constructor() {
        this.css = '';
        this.transforms = [];
        this.boxShadows = [];
        this.backgrounds = [];
        this.transitions = [];
        this.borders = {};
        this.fontShadows = [];
        this.colors = '';
        this.opacities = null;
        this.backgroundPositionX = null; // optional, for background position
        this.backgroundPositionY = null;
        this.widthVal = null;
        this.heightVal = null;
    }

    /**
     * Adds a rotation transformation.
     * @param {number} degree - Angle in degrees.
     * @returns {this}
     */
    rotate(degree) {
        this.transforms.push(`rotate(${degree}deg)`);
        return this;
    }

    /**
     * Adds a rotation around the X-axis.
     * @param {number} degree - Angle in degrees.
     * @returns {this}
     */
    rotateX(degree) {
        this.transforms.push(`rotateX(${degree}deg)`);
        return this;
    }

    /**
     * Adds a rotation around the Y-axis.
     * @param {number} degree - Angle in degrees.
     * @returns {this}
     */
    rotateY(degree) {
        this.transforms.push(`rotateY(${degree}deg)`);
        return this;
    }

    /**
     * Adds a rotation around the Z-axis.
     * @param {number} degree - Angle in degrees.
     * @returns {this}
     */
    rotateZ(degree) {
        this.transforms.push(`rotateZ(${degree}deg)`);
        return this;
    }

    /**
     * Adds scaling transformation.
     * @param {number} sx - Scale factor in X.
     * @param {number} [sy] - Scale factor in Y, optional.
     * @returns {this}
     */
    scale(sx, sy) {
        if (sy === undefined) {
            this.transforms.push(`scale(${sx})`);
        } else {
            this.transforms.push(`scale(${sx}, ${sy})`);
        }
        return this;
    }

    /**
     * Adds 3D scaling transformation.
     * @param {number} sx - Scale factor in X.
     * @param {number} sy - Scale factor in Y.
     * @param {number} sz - Scale factor in Z.
     * @returns {this}
     */
    scale3D(sx, sy, sz) {
        this.transforms.push(`scale3d(${sx}, ${sy}, ${sz})`);
        return this;
    }

    /**
     * Adds scaling in X direction.
     * @param {number} sx - Scale factor.
     * @returns {this}
     */
    scaleX(sx) {
        this.transforms.push(`scaleX(${sx})`);
        return this;
    }

    /**
     * Adds scaling in Y direction.
     * @param {number} sy - Scale factor.
     * @returns {this}
     */
    scaleY(sy) {
        this.transforms.push(`scaleY(${sy})`);
        return this;
    }

    /**
     * Adds scaling in Z direction.
     * @param {number} sz - Scale factor.
     * @returns {this}
     */
    scaleZ(sz) {
        this.transforms.push(`scaleZ(${sz})`);
        return this;
    }

    /**
     * Adds translation transformation.
     * @param {number} x - Translation in X (pixels).
     * @param {number} y - Translation in Y (pixels).
     * @returns {this}
     */
    translate(x, y) {
        this.transforms.push(`translate(${x}px, ${y}px)`);
        return this;
    }

    /**
     * Translates along the X-axis.
     * @param {number} x - Pixels to translate.
     * @returns {this}
     */
    translateX(x) {
        this.transforms.push(`translateX(${x}px)`);
        return this;
    }

    /**
     * Translates along the Y-axis.
     * @param {number} y - Pixels to translate.
     * @returns {this}
     */
    translateY(y) {
        this.transforms.push(`translateY(${y}px)`);
        return this;
    }

    /**
     * Adds perspective transformation.
     * @param {number} distance - Distance in pixels.
     * @returns {this}
     */
    perspective(distance) {
        this.transforms.push(`perspective(${distance}px)`);
        return this;
    }

    /**
     * Applies a 4x4 matrix transformation.
     * @param {...number} values - Matrix values.
     * @returns {this}
     */
    matrix(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p) {
        this.transforms.push(`matrix(${a}, ${b}, ${c}, ${d}, ${e}, ${f}, ${g}, ${h}, ${i}, ${j}, ${k}, ${l}, ${m}, ${n}, ${o}, ${p})`);
        return this;
    }

    /**
     * Applies a 3D matrix transformation.
     * @param {...number} values - Matrix values.
     * @returns {this}
     */
    matrix3D(a1, b1, c1, d1, a2, b2, c2, d2, a3, b3, c3, d3, a4, b4, c4, d4) {
        this.transforms.push(`matrix3d(${a1}, ${b1}, ${c1}, ${d1}, ${a2}, ${b2}, ${c2}, ${d2}, ${a3}, ${b3}, ${c3}, ${d3}, ${a4}, ${b4}, ${c4}, ${d4})`);
        return this;
    }

    /**
     * Adds a 3D rotation.
     * @param {number} x - X component.
     * @param {number} y - Y component.
     * @param {number} z - Z component.
     * @param {number} deg - Degrees to rotate.
     * @returns {this}
     */
    rotate3D(x, y, z, deg) {
        this.transforms.push(`rotate3d(${x}, ${y}, ${z}, ${deg}deg)`);
        return this;
    }

    /**
     * Adds skew transformation.
     * @param {number} xDeg - Skew in X (degrees).
     * @param {number} yDeg - Skew in Y (degrees).
     * @returns {this}
     */
    skew(xDeg, yDeg) {
        this.transforms.push(`skew(${xDeg}deg, ${yDeg}deg)`);
        return this;
    }

    /**
     * Skews along the X-axis.
     * @param {number} xDeg - Degrees to skew.
     * @returns {this}
     */
    skewX(xDeg) {
        this.transforms.push(`skewX(${xDeg}deg)`);
        return this;
    }

    /**
     * Skews along the Y-axis.
     * @param {number} yDeg - Degrees to skew.
     * @returns {this}
     */
    skewY(yDeg) {
        this.transforms.push(`skewY(${yDeg}deg)`);
        return this;
    }

    /**
     * Adds box-shadow styles.
     * @param {...{h:number, v: number, blur: number, spread: number, color: string}} shadows - Shadow objects with h, v, blur, spread, color.
     * @returns {this}
     */
    boxShadow(...shadows) {
        shadows.forEach(s => {
            this.boxShadows.push(`${s.h ?? 0}px ${s.v ?? 0}px ${s.blur ?? 0}px ${s.spread ?? 0}px ${s.color ?? 'transparent'}`);
        });
        return this;
    }

    /**
     * Adds background styles.
     * @param {...string} backgrounds - Background layers.
     * @returns {this}
     */
    background(...backgrounds) {
        this.backgrounds.push(...backgrounds);
        return this;
    }

    /**
     * Adds transition styles.
     * @param {...string} transitions - Transition properties.
     * @returns {this}
     */
    transition(...transitions) {
        this.transitions.push(...transitions);
        return this;
    }

    /**
     * Sets border styles for specified sides or all sides.
     * @param {number} width - Border width.
     * @param {string} style - Border style.
     * @param {string} color - Border color.
     * @param {...string} [locations] - Optional sides ('top', 'right', 'bottom', 'left').
     * @returns {this}
     */
    border(width, style, color, ...locations) {
        if (locations.length === 0) {
            this.borders['border'] = `${width}px ${style} ${color}`;
        } else {
            for (const loc of locations) {
                switch (loc.toLowerCase()) {
                    case 'top':
                        this.borders['border-top'] = `${width}px ${style} ${color}`;
                        break;
                    case 'right':
                        this.borders['border-right'] = `${width}px ${style} ${color}`;
                        break;
                    case 'bottom':
                        this.borders['border-bottom'] = `${width}px ${style} ${color}`;
                        break;
                    case 'left':
                        this.borders['border-left'] = `${width}px ${style} ${color}`;
                        break;
                }
            }
        }
        return this;
    }

    /**
     * Adds text-shadow styles.
     * @param {...{h:number, v:number, blur: number, color: string}} shadows - Shadow objects with h, v, blur, color.
     * @returns {this}
     */
    fontShadow(...shadows) {
        shadows.forEach(s => {
            this.fontShadows.push(`${s.h ?? 0}px ${s.v ?? 0}px ${s.blur ?? 0}px ${s.color ?? 'transparent'}`);
        });
        return this;
    }

    /**
     * Sets text color.
     * @param {string} color - Color value.
     * @returns {this}
     */
    color(color) {
        this.colors = color;
        return this;
    }

    /**
     * Sets opacity value, constrained between 0 and 1.
     * @param {number} value - Opacity value.
     * @returns {this}
     */
    opacity(value) {
        this.opacities = Math.min(Math.max(parseFloat(value), 0), 1);
        return this;
    }

    /**
     * Sets background position. Accepts 1 or 2 parameters.
     * If 1 parameter, sets both X and Y to the same value.
     * Valid options include number (pixels), 'top', 'right', 'bottom', 'left', 'center'.
     * @param {string|number} x - X position.
     * @param {string|number} [y] - Y position.
     * @returns {this}
     */
    backgroundPosition(x, y) {
        if (y === undefined) {
            this.backgroundPositionX = x;
            this.backgroundPositionY = x;
        } else {
            this.backgroundPositionX = x;
            this.backgroundPositionY = y;
        }
        return this;
    }
    /**
     * Changes the width of object
     * @param {Number} w Width in pixels
     */
    width(w){
        this.widthVal = parseFloat(w);
    }

    /**
     * Changes the height of object
     * @param {Number} h Height in pixels
     */
    height(h){
        this.heightVal = parseFloat(h);
    }

    /**
     * Finalizes and generates the CSS string.
     * @returns {string} - CSS styles.
     */
    finalize() {
        const styles = [];
        if (this.transforms.length > 0) styles.push(`transform: ${this.transforms.join(' ')};`);
        if (this.boxShadows.length > 0) styles.push(`box-shadow: ${this.boxShadows.join(', ')};`);
        if (this.backgrounds.length > 0) styles.push(`background: ${this.backgrounds.join(', ')};`);
        if (this.transitions.length > 0) styles.push(`transition: ${this.transitions.join(', ')};`);
        for (const [key, value] of Object.entries(this.borders)) {
            styles.push(`${key}: ${value};`);
        }
        if (this.fontShadows.length > 0) styles.push(`text-shadow: ${this.fontShadows.join(', ')};`);
        if (!this.#isNull(this.colors)) styles.push(`color: ${this.colors};`);
        if (this.opacities !== null && this.opacities !== undefined) styles.push(`opacity: ${this.opacities};`);
        // Add background-position if set
        if (this.backgroundPositionX !== null && this.backgroundPositionY !== null) {
            styles.push(`background-position: ${this.#formatBackgroundPosition(this.backgroundPositionX, this.backgroundPositionY)};`);
        }
        if(this.widthVal) styles.push(`width: ${this.widthVal}px;`);
        if(this.heightVal) styles.push(`height: ${this.heightVal}px;`);
        this.css = styles.join(' ');
        return this.css;
    }

    /**
     * Helper method to format background position value.
     * @param {string|number} x
     * @param {string|number} y
     * @returns {string}
     */
    #formatBackgroundPosition(x, y) {
        const formatValue = (val) => {
            if (typeof val === 'number') {
                return `${val}px`;
            } else {
                return val;
            }
        };
        return `${formatValue(x)} ${formatValue(y)}`;
    }

    /**
     * Helper method to check if a string is null or empty.
     * @param {string} str
     * @returns {boolean}
     */
    #isNull(str) {
        return !str || str === '';
    }

    /**
     * Resets all styles to initial state.
     * @returns {this}
     */
    clear() {
        this.css = '';
        this.transforms = [];
        this.boxShadows = [];
        this.backgrounds = [];
        this.transitions = [];
        this.borders = {};
        this.fontShadows = [];
        this.colors = '';
        this.opacities = null;
        this.backgroundPositionX = null;
        this.backgroundPositionY = null;
        this.widthVal = null;
        this.heightVal = null;
        return this;
    }
}

const AnimateEvents = class{
    constructor() {
        // Initialize state to keep track of transforms
        this.transformState = {
            rotateX: 0,
            rotateY: 0,
            rotateZ: 0,
            scale: 1
        };
    }

    /**
     * Adds an interactive click+drag rotation to an element, including Z-axis.
     * @param {Element} el - The DOM element to rotate.
     * @param {number} rotationFactor - Sensitivity multiplier for rotation.
     * @returns {this}
     */
    clickRotate(el, rotationFactor=1) {
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let currentRotationX = 0;
        let currentRotationY = 0;
        let currentRotationZ = 0; // Added for Z-axis rotation
        let isRotatingZ = false; // Flag to indicate Z rotation mode

        el.classList.add('isMovable');

        const start = (e) => {
            isDragging = true;
            // Determine if Z rotation mode is active (e.g., right-click or modifier key)
            // For simplicity, suppose holding Shift enables Z rotation
            isRotatingZ = e.shiftKey || (e.type === 'touchstart' && false); // adjust as needed

            if (e.type.startsWith('touch')) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            } else {
                startX = e.clientX;
                startY = e.clientY;
            }
            document.addEventListener('mousemove', move);
            document.addEventListener('mouseup', end);
            document.addEventListener('touchmove', move);
            document.addEventListener('touchend', end);
        };

        const move = (e) => {
            if (!isDragging) return;
            let currentX, currentY;
            if (e.type.startsWith('touch')) {
                currentX = e.touches[0].clientX;
                currentY = e.touches[0].clientY;
            } else {
                currentX = e.clientX;
                currentY = e.clientY;
            }
            const deltaX = currentX - startX;
            const deltaY = currentY - startY;

            if (isRotatingZ) {
                // Rotate around Z-axis based on horizontal movement
                currentRotationZ += deltaX * rotationFactor;
            } else {
                // Rotate around X and Y axes
                currentRotationY += deltaX * rotationFactor;
                currentRotationX -= deltaY * rotationFactor;
            }

            // Update the transform state
            this.transformState.rotateX = currentRotationX;
            this.transformState.rotateY = currentRotationY;
            this.transformState.rotateZ = currentRotationZ;

            // Apply combined transform
            el.style.transform = this._getTransformString();

            // Update start points for smooth dragging
            startX = currentX;
            startY = currentY;
        };

        const end = () => {
            isDragging = false;
            document.removeEventListener('mousemove', move);
            document.removeEventListener('mouseup', end);
            document.removeEventListener('touchmove', move);
            document.removeEventListener('touchend', end);
        };

        el.addEventListener('mousedown', start);
        el.addEventListener('touchstart', start);

        return this;
    }

    // Ensure your _getTransformString method includes rotateZ:
    _getTransformString() {
        const { rotateX, rotateY, rotateZ } = this.transformState;
        return `rotateX(${rotateX}deg) rotateY(${rotateY}deg) rotateZ(${rotateZ}deg)`;
    }

    /**
     * Adds scroll (mouse wheel) zoom and pinch-to-zoom gesture.
     * @param {Element} el - The DOM element to apply zoom.
     * @param {number} minScale - Minimum scale factor.
     * @param {number} maxScale - Maximum scale factor.
     * @returns {this}
     */
    scrollZoom(el, minScale=0.5, maxScale=3) {
        let isPinching = false;
        let startDistance = 0;

        // Helper to get distance between two touch points
        const getDistance = (touches) => {
            const [a, b] = touches;
            const dx = b.clientX - a.clientX;
            const dy = b.clientY - a.clientY;
            return Math.hypot(dx, dy);
        };

        // Mouse wheel zoom
        el.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = -e.deltaY * 0.001; // sensitivity
            let newScale = this.transformState.scale + delta;
            newScale = Math.min(Math.max(newScale, minScale), maxScale);
            this.transformState.scale = newScale;
            el.style.transform = this._getTransformString();
        });

        // Touch pinch-to-zoom
        el.addEventListener('touchstart', (e) => {
            if (e.touches.length === 2) {
                isPinching = true;
                startDistance = getDistance(e.touches);
            }
        });

        el.addEventListener('touchmove', (e) => {
            if (isPinching && e.touches.length === 2) {
                const currentDistance = getDistance(e.touches);
                const deltaDistance = currentDistance - startDistance;
                let newScale = this.transformState.scale + deltaDistance * 0.005; // sensitivity
                newScale = Math.min(Math.max(newScale, minScale), maxScale);
                this.transformState.scale = newScale;
                el.style.transform = this._getTransformString();
            }
        });

        el.addEventListener('touchend', (e) => {
            if (isPinching && e.touches.length < 2) {
                // Finish pinch
                isPinching = false;
            }
        });

        return this;
    }

    /**
     * Helper to generate the combined transform string based on current state.
     * @returns {string}
     */
    _getTransformString() {
        const { rotateX, rotateY, scale } = this.transformState;
        return `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(${scale})`;
    }
}
