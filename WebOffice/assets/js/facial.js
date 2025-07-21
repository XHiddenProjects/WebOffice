class FacialRecognition {
    constructor(el = document.body, width = 640, height = 480) {
        this.el = el;
        this.width = width;
        this.height = height;

        // Create video element
        this.videoElement = document.createElement('video');
        this.videoElement.id = 'facial-video';
        this.videoElement.className = 'facial-video';

        // Create canvas element
        this.canvasElement = document.createElement('canvas');
        this.canvasElement.id = 'facial-canvas';
        this.canvasElement.className = 'facial-canvas';
        this.videoElement.width = width;
        this.videoElement.height = height;
        this.canvasElement.width = width;
        this.canvasElement.height = height;
        this.canvasElement.style.width = `${width}px`;
        this.canvasElement.style.height = `${height}px`;

        this.context = this.canvasElement.getContext('2d');

        // Initialize recognition state in sessionStorage if not already set
        if (sessionStorage.getItem('recognitionActive') === null) {
            sessionStorage.setItem('recognitionActive', 'false');
        }

        this.el.appendChild(this.videoElement);
    }

    /**
     * Initializes the facial recognition process.
     */
    init() {
        if (!this.checkSupport()) return;

        navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
            this.videoElement.srcObject = stream;
            this.videoElement.play();
            sessionStorage.setItem('recognitionActive', 'true');
            this.recognitionLoop();

            // Save the object state
            this.saveStateToSession();
        }).catch(error => {
            console.error('Error accessing webcam:', error);
            sessionStorage.setItem('recognitionActive', 'false');
        });
    }

    /**
     * Main recognition loop: captures frames and processes them.
     */
    recognitionLoop() {
        const isActive = sessionStorage.getItem('recognitionActive') === 'true';
        if (!isActive) return;

        this.context.drawImage(this.videoElement, 0, 0, this.canvasElement.width, this.canvasElement.height);
        // Placeholder: Call your facial recognition library here

        requestAnimationFrame(() => this.recognitionLoop());
    }

    /**
     * Stops the recognition and webcam stream.
     */
    stop() {
        sessionStorage.setItem('recognitionActive', 'false');

        if (this.videoElement.srcObject) {
            const tracks = this.videoElement.srcObject.getTracks();
            tracks.forEach(track => track.stop());
        }
        this.videoElement.srcObject = null;
        this.context.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);
        // Optionally, remove or hide the canvas
        if (this.el.contains(this.canvasElement)) {
            this.el.removeChild(this.canvasElement);
        }
        // Save state
        this.saveStateToSession();
    }

    /**
     * Toggles recognition on/off.
     */
    toggleRecognition() {
        const isActive = sessionStorage.getItem('recognitionActive') === 'true';
        if (isActive) {
            this.stop();
        } else {
            this.init();
        }
    }

    /**
     * Checks for webcam support.
     */
    checkSupport() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.error('Webcam access is not supported in this browser.');
            return false;
        }
        return true;
    }
    /**
     * Compares the current facial image with stored images.
     * @returns {Array} Array of comparison results or empty array if no images found.
     */
    compareFacial() {
        $.ajax({
            url: `${mainDir}/compareFacial.php`,
            type: 'GET',
            contentType: 'application/json',
            success: (response) => {
                response = JSON.parse(response);
                // Expecting response to be an array of image filenames
                if (!Array.isArray(response)) {
                    console.error('Unexpected response format:', response);
                    return;
                }

                if (response.length < 1) {
                    console.error('No images to compare.');
                    return;
                }

                // Load all images
                const imagePromises = response.map((filename, index) => {
                    return new Promise((resolve, reject) => {
                        const img = new Image();
                        img.crossOrigin = 'Anonymous'; // important for pixel data
                        img.onload = () => {
                            // Create a temporary canvas to get data URL
                            const tempCanvas = document.createElement('canvas');
                            tempCanvas.width = img.width;
                            tempCanvas.height = img.height;
                            const tempCtx = tempCanvas.getContext('2d');
                            tempCtx.drawImage(img, 0, 0);
                            const dataURL = tempCanvas.toDataURL();

                            

                            resolve({ filename, img, dataURL });
                        };
                        img.onerror = reject;
                        img.src = `${mainDir}facials/${filename}`; // adjust path as needed
                    });
                });

                Promise.all(imagePromises)
                    .then(images => {
                        // Loop through each image and compare
                        const comparisons = images.map(({ filename, img }) => {
                            // Draw both images onto separate canvases
                            const width = Math.min(img.width, this.canvasElement.width);
                            const height = Math.min(img.height, this.canvasElement.height);

                            const tempCanvas1 = document.createElement('canvas');
                            const tempCanvas2 = document.createElement('canvas');
                            tempCanvas1.width = width;
                            tempCanvas1.height = height;
                            tempCanvas2.width = width;
                            tempCanvas2.height = height;

                            const ctx1 = tempCanvas1.getContext('2d');
                            const ctx2 = tempCanvas2.getContext('2d');

                            ctx1.drawImage(img, 0, 0, width, height);
                            ctx2.drawImage(this.canvasElement, 0, 0, width, height);

                            const data1 = ctx1.getImageData(0, 0, width, height).data;
                            const data2 = ctx2.getImageData(0, 0, width, height).data;

                            // Compare pixel differences
                            let diffCount = 0;
                            for (let i = 0; i < data1.length; i += 4) {
                                const rDiff = Math.abs(data1[i] - data2[i]);
                                const gDiff = Math.abs(data1[i + 1] - data2[i + 1]);
                                const bDiff = Math.abs(data1[i + 2] - data2[i + 2]);
                                const pixelDiff = rDiff + gDiff + bDiff;
                                if (pixelDiff > 50) { // threshold
                                    diffCount++;
                                }
                            }

                            const totalPixels = width * height;
                            const similarityScore = (1 - diffCount / totalPixels) * 100; // percentage

                            return {
                                filename,
                                score: similarityScore,
                                percentage: `${similarityScore.toFixed(2)}%`,
                            };
                        });

                        // Optionally, return the array of comparison results
                        return comparisons;
                    })
                    .then(results => {
                        sessionStorage.setItem('comparisonResults', JSON.stringify(results));
                    })
                    .catch(err => {
                        console.error('Error loading images:', err);
                    });
            },
            error: (xhr, status, error) => {
                console.error('Failed to get image list:', error);
            }
        });
        return JSON.parse(sessionStorage.getItem('comparisonResults')) || [];
    }

    /**
     * Shows face outline with pixel points.
     */
    showFacePixel() {
        const drawFaceOutline = (points) => {
            this.context.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);
            this.context.drawImage(this.videoElement, 0, 0, this.canvasElement.width, this.canvasElement.height);

            if (points && points.length > 0) {
                this.context.strokeStyle = 'red';
                this.context.lineWidth = 2;

                // Calculate center and radius from points for a circle
                const xs = points.map(p => p.x);
                const ys = points.map(p => p.y);
                const centerX = xs.reduce((a, b) => a + b, 0) / xs.length;
                const centerY = ys.reduce((a, b) => a + b, 0) / ys.length;
                const radius = Math.max(
                    ...points.map(p => Math.hypot(p.x - centerX, p.y - centerY))
                );

                // Draw a circle
                this.context.beginPath();
                this.context.arc(centerX, centerY, radius, 0, Math.PI * 2);
                this.context.stroke();
            }
        };

        

        // Dummy landmark detection function
        const detectFaceLandmarks = (canvas) => {
            return new Promise((resolve) => {
                const centerX = this.canvasElement.width / 2;
                const centerY = this.canvasElement.height / 2;
                const radius = Math.min(this.canvasElement.width, this.canvasElement.height) / 3;
                const points = [];
                const numPoints = 20;
                for (let i = 0; i < numPoints; i++) {
                    const angle = (Math.PI * 2 / numPoints) * i;
                    points.push({
                        x: centerX + radius * Math.cos(angle),
                        y: centerY + radius * Math.sin(angle)
                    });
                }
                resolve(points);
            });
        };
        const processFrame = () => {
            this.context.drawImage(this.videoElement, 0, 0, this.canvasElement.width, this.canvasElement.height);

            // Replace with actual face landmark detection
            detectFaceLandmarks(this.canvasElement).then((landmarks) => {
                drawFaceOutline(landmarks);
            }).catch(err => {
                console.error('Face detection error:', err);
            });
            setTimeout(()=>{
                requestAnimationFrame(processFrame);
            },0);
            
        };

        // Start processing
        processFrame();
    }

    /**
     * Takes a picture and saves it via AJAX.
     */
    takePicture() {
        if (sessionStorage.getItem('recognitionActive') !== 'true') {
            console.error('Facial recognition is not active.');
            return;
        }

        // Detect face landmarks to get face points
        const detectFaceLandmarks = () => {
            const centerX = this.canvasElement.width / 2;
            const centerY = this.canvasElement.height / 2;
            const radius = Math.min(this.canvasElement.width, this.canvasElement.height) / 3;
            const points = [];
            const numPoints = 20;
            for (let i = 0; i < numPoints; i++) {
                const angle = (Math.PI * 2 / numPoints) * i;
                points.push({
                    x: centerX + radius * Math.cos(angle),
                    y: centerY + radius * Math.sin(angle)
                });
            }
            return Promise.resolve(points);
        };

        detectFaceLandmarks().then(points => {
            // Calculate bounding box
            const xs = points.map(p => p.x);
            const ys = points.map(p => p.y);
            const minX = Math.min(...xs);
            const maxX = Math.max(...xs);
            const minY = Math.min(...ys);
            const maxY = Math.max(...ys);

            const padding = 10;
            const cropX = Math.max(0, minX - padding);
            const cropY = Math.max(0, minY - padding);
            const cropWidth = Math.min(this.canvasElement.width - cropX, maxX - minX + 2 * padding);
            const cropHeight = Math.min(this.canvasElement.height - cropY, maxY - minY + 2 * padding);

            // Clear the canvas to remove the red circle
            this.context.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);

            // Draw the current video frame onto the cleared canvas
            this.context.drawImage(this.videoElement, 0, 0, this.canvasElement.width, this.canvasElement.height);

            // Create a temporary canvas for the face crop
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = cropWidth;
            tempCanvas.height = cropHeight;
            const tempCtx = tempCanvas.getContext('2d');

            // Draw the face region onto the temporary canvas
            tempCtx.drawImage(
                this.canvasElement,
                cropX, cropY, cropWidth, cropHeight,
                0, 0, cropWidth, cropHeight
            );

            // Get the data URL of the face image
            const faceImageData = tempCanvas.toDataURL('image/png');

            // Send via AJAX
            $.ajax({
                url: `${mainDir}/savePicture.php`,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ image: faceImageData }),
                success: function(response) {
                    console.log('Face image saved successfully.');
                },
                error: function(xhr, status, error) {
                    console.error('Failed to save face image:', error);
                }
            });
        }).catch(err => {
            console.error('Error detecting face for capture:', err);
        });
    }

    /**
     * Save the current object state into sessionStorage as JSON.
     * Since DOM elements can't be directly stored, store their IDs or relevant info.
     */
    saveStateToSession() {
        const state = {
            recognitionActive: sessionStorage.getItem('recognitionActive'),
            videoElementId: this.videoElement.id,
            canvasElementId: this.canvasElement.id,
            width: this.width,
            height: this.height,
            // Add other properties if needed
        };
        sessionStorage.setItem('facialRecognitionState', JSON.stringify(state));
    }

    /**
     * Load state from sessionStorage (if needed)
     */
    static loadFromSession() {
        const stateStr = sessionStorage.getItem('facialRecognitionState');
        if (!stateStr) return null;
        const state = JSON.parse(stateStr);
        return state;
    }
}