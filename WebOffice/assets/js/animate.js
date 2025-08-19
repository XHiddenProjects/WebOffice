/**
 * AnimateJS class: Main class to handle animations and utilities
 */
const AnimateJS = class {
    constructor() {
        this.ANIMATION_INFINITE = -1; // Constant to denote infinite animation
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
            }
        };

        /**
         * Collection of predefined animations with various directions and types
         */
        this.Animations = {
            fade: {
                // Fade In
                in: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeIn ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out
                out: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOut ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Left
                inLeft: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Left
                outLeft: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Right
                inRight: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Right
                outRight: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Top
                inTop: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Top
                outTop: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade In from Bottom
                inBottom: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeInBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Fade Out to Bottom
                outBottom: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `fadeOutBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                }
            },

            slide: {
                // Slide In from Top
                inTop: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Top
                outTop: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutTop ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide In from Bottom
                inBottom: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Bottom
                outBottom: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutBottom ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide In from Left
                inLeft: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Left
                outLeft: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutLeft ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide In from Right
                inRight: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideInRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                },
                // Slide Out to Right
                outRight: (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
                    const animStr = `slideOutRight ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
                    if (merge) this.Utils.Merger(el, animStr);
                    else el.style.animation = animStr;
                }
            }
        };

        // Create a style element in document head for custom keyframes if not already exists
        if (!document.querySelector('#animateJS-style')) {
            const css = document.createElement('style');
            css.id = 'animateJS-style';
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
            duration = 300,
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
        current(el, timing, duration, delay, count === this.ANIMATION_INFINITE ? 'infinite' : count, direction, mode, merge);

        // Return a Promise that resolves after animation ends or immediately if infinite
        return new Promise((resolve) => {
            if (count === this.ANIMATION_INFINITE) {
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
     * Attach scroll event listener
     */
    scroll(el, callback) {
        el.addEventListener('scroll', callback);
        return this;
    }

    /**
     * Attach load event listener
     */
    load(el, callback) {
        el.addEventListener('load', callback);
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
     */
    add(animation) {
        if (!(animation instanceof AnimationJS))
            throw new Error('Must be an instance of AnimationJS');
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
            const styleEl = document.querySelector('#animateJS-style');
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

    constructor(...path) {
        this.path = path.join('.') ?? ''; // Path for naming
        this.name = this.#lastIndex(path); // Animation name derived from last path segment
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
     * Create a keyframe timeline with specific frames and keyframes
     * @param {string} frame - Frame percentage or label (e.g., '50')
     * @param {...string} keyframes - Keyframe definitions
     * @returns {AnimationJS} - for chaining
     */
    createTimeline(frame, ...keyframes) {
        this.#keyframes += `${frame.match(/\d+/) ? `${frame}%` : frame}{`;
        keyframes.forEach((kf) => {
            this.#keyframes += `${kf.match(/;$/) ? kf : `${kf};`}`;
        });
        this.#keyframes += `}`;
        this.css = `${this.#begin}${this.#keyframes}${this.#end}`;
        return this;
    }

    /**
     * Develop the animation by defining its callback function
     */
    develop() {
        this.callback = (el, timing='ease', duration=300, delay=0, count=1, direction='normal', mode='none', merge=false) => {
            const animation = `${this.name} ${duration}ms ${timing} ${delay}ms ${count} ${direction} ${mode}`;
            if(merge) this.Utils.Merger(el, animation);
            else el.style.animation = animation;
        };
        return this;
    }

    /**
     * Generate the final CSS for keyframes
     */
    generate() {
        if (!this.callback) throw new Error('You must use develop()');
        return this;
    }
}