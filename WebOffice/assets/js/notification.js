class NotificationJS{
    constructor(el=null){
        this.el = document.querySelector(el)||null;
        if(!this.el) this.useDevice = true;
        else this.useDevice = false;
        if(this.el){
            this.el.classList.add('notification');
            this.el.tabIndex = 0;
            const title = document.createElement('div');
            title.className = `notification-title`;
            const body = document.createElement('div');
            body.className = `notification-body`;
            const closeBtn = document.createElement('span');
            closeBtn.className = 'notification-close-btn';
            closeBtn.innerHTML = 'X';
            const timeBar = document.createElement('div');
            timeBar.className = 'notification-close';
            timeBar.innerHTML = `<div class="notification-close-bar"></div>`;
            if(!this.el.hasAttribute('notification-rendered')){
                this.el.appendChild(title);
                this.el.appendChild(body);
                this.el.appendChild(timeBar);
                this.el.appendChild(closeBtn);
            }

            this.el.setAttribute('notification-rendered',true);
        }

        this.granted = false;
        this.notification = null;
        this.options = {};
    }
    async request(){
        if(this.useDevice){
            if(Notification.requestPermission()==="denied"&&!this.el)
                console.error('Make sure el parameter is used, user denied device notification');
        }
    }
    
    /**
     * Adds a title to the notification
     *
     * @param {String} title Notification title
     * @returns {this}
     */
    addTitle(title){
        this.options['title'] = title;
        return this;
    }
    
    /**
     * Adds a body for the notification
     *
     * @param {String} body Text to insert into body of the notification
     * @returns {this} 
     */
    addBody(body){
        this.options['body'] = body;
        return this;
    }
    
    /**
     * Adds data to the notification
     *
     * @param {String} url URL to the notification
     * @param {'open'|'close'} [status='open'] Status of the URL
     * @returns {this} 
     */
    addData(url,status='open'){
        this.options['data'] = {
            url: url,
            status: status.toLocaleLowerCase()
        };
        return this;
    }
    
    /**
     * Adds the direction to the notification
     *
     * @param {'auto'|'rtl'|'ltr'} dir Direction of the notification text
     * @returns {this} 
     */
    addDir(dir){
        this.options['dir'] = dir;
        return this;
    }
    
    /**
     * Adds an icon to the notification
     *
     * @param {String} path Icon path
     * @returns {this}
     */
    addIcon(path){
        this.options['icon'] = path;
        return this;
    }
    
    /**
     * Sets the language to the notification
     *
     * @param {String} lang Language to have the notification be
     * @returns {this} 
     */
    setLang(lang){
        this.options['lang'] = lang||navigator.language;
        return this;
    }
    
    /**
     * Sets the renotify
     *
     * @param {boolean} [renotify=false] Renotify if another notification pops-up
     * @returns {this}
     */
    allowRenotify(renotify=false){
        this.options['renotify'] = renotify;
        return this;
    }
    /**
     * Allows silent notification
     * @param {boolean} [silent=false] Enable/Disable silent notification
     * @returns 
     */
    allowSilent(silent=false){
        this.options['silent'] = silent;
        return this;
    }
    
    /**
     * Sets the timestamp of the event
     *
     * @param {Number} [timestamp=Math.floor(Date.now())] 
     * @returns {this} 
     */
    setTimestamp(timestamp=Math.floor(Date.now())){
        this.options['timestamp'] = timestamp;
        return this;
    }
    
    
    /**
     * Set the vibration
     *
     * @param {Number} start Vibrate for \# ms
     * @param {Number} pause Pause for \# ms
     * @param {Number} end Vibrate for \# ms
     * @returns {this} 
     */
    setVibrate(start, pause, end) {
        this.options['vibrate'] = [start, pause, end];
        return this;
    }
    
    /**
     * Sets the wait time before closing the notification
     *
     * @param {Number} ms \# (in ms) before closing notification
     * @returns {this}
     */
    setTimeout(ms){
        this.options.timeout = ms;
        return this;
    }

    /**
     * Sets the close event
     *
     * @param {Function} options Close options with timeout in ms and function on close
     * @returns {this}
     */
    onClose(event){
        this.options.onClose = event;
        return this;
    }
    
    /**
     * Triggers function on error
     *
     * @param {Function} event Function to execute on error
     * @returns {this}
     */
    onError(event){
        this.options.onError = event;
        return this;
    }
    
    /**
     * Execute function on notification show
     *
     * @param {Function} event Execute function on notification show
     * @returns {this}
     */
    onShow(event){
        this.options.onShow = event;
        return this;
    }
    
    /**
     * Triggers event on click
     *
     * @param {Function} event Function to trigger on click
     * @returns {this} 
     */
    onClick(event){
        this.options.onClick = event;
        return this;
    }
    
    /**
     * Adds an audio to be played on show
     *
     * @param {String} url Audio URL to be played
     * @returns {this}
     */
    addAudio(url){
        this.options.audio = url;
        return this;
    }

    /**
     * Sets the options
     *
     * @param {{
     * title: string, 
     * body: string, 
     * timeout: number,
     * data:{url: string, status: 'open'|'close'}, 
     * dir: 'auto'|'rtl'|'ltr',
     * audio: string,
     * icon: string,
     * lang: string,
     * renotify: boolean,
     * silent: boolean,
     * timestamp: number,
     * vibrate: [number, number, number],
     * onClose: function,
     * onError: function,
     * onShow: function,
     * onClick: function}} [options={}] Configure the options
     * @returns {this}
     */
    setOptions(options={}){
        this.options = options;
        return this;
    }
    /** Pushes notification */
    push(){
        if(this.useDevice){
            const title = this.options.title;
            this.notification = new Notification(title,this.options);
            if(this.options.onClose) this.notification.addEventListener('close',(event)=>this.options.onClose(event));
            if(this.options.timeout){
                setTimeout(()=>{
                    this.notification.close();
                },this.options.timeout);
            }
            if(this.options.onError) this.notification.addEventListener('error',(event)=>this.options.onError(event));
            if(this.options.onShow) this.notification.addEventListener('show',(event)=>this.options.onShow(event));
            if(this.options.onClick) this.notification.addEventListener('click',(event)=>this.options.onClick(event));
        }else{
            if(this.el.style.opacity==1) return;
            var elem = this.el,
            options = this.options;
            this.el.querySelector('.notification-title').innerText = this.options.title;
            this.el.querySelector('.notification-body').innerText = this.options.body;
            this.el.style.opacity = 1;
            this.el.style.zIndex = 0;
            this.el.style.pointerEvents = 'all';
            if(this.options.audio) this.#playAudio(this.options.audio);
            this.options.onShow(this.el);
            if(this.options.onClick) this.el.addEventListener('click',(event)=>{
                if(!event.target.className.match('notification-close-btn')) {
                    this.options.onClick(event);
                }
            },{once: true});
            let interrupt=false;
            if(this.options.timeout){
                const totalDuration = this.options.timeout; // total countdown in milliseconds
                const progressBar = this.el.querySelector('.notification-close-bar');
                let currentPercentage = 100; // start at 100%
                const steps = 100; // total number of 1% steps
                const intervalTime = totalDuration / steps; // time per 1% decrease
                // Function to update the bar
                function decrease() {
                    if (currentPercentage > 0&&!interrupt) {
                        currentPercentage -= 1; // decrease by 1%
                        if (currentPercentage < 0) currentPercentage = 0; // clamp at 0%
                        progressBar.style.width = `${currentPercentage}%`;
                        setTimeout(decrease, intervalTime);
                    } else {
                        progressBar.parentElement.parentElement.style.opacity = 0;
                        progressBar.parentElement.parentElement.style.zIndex = -1;
                        progressBar.parentElement.parentElement.style.pointerEvents = 'none';
                        options.onClose(elem);
                        progressBar.style.width = '0%'; // ensure it ends at 0%
                        interrupt=false;
                    }
                }
                // Initialize at full width
                progressBar.style.width = '100%';
                // Start decreasing
                decrease();
            }
            this.el.querySelector('.notification-close-btn').addEventListener('click',()=>{
                interrupt = true;
                if(!this.options.timeout){
                    this.el.querySelector('.notification-close-bar').style.opacity = 0;
                    this.el.querySelector('.notification-close-bar').style.zIndex = -1;
                    this.el.style.pointerEvents = 'none';
                    this.options.onClose(elem);
                }
            });
        }
    }
    #playAudio(url){
        const a = new Audio(url);
        a.play();
    }
}