window.addEventListener('alpine:init', ()=>{
    Alpine.data('chunkedUpload', (livewireComponent) => ({
        progress: 0,
        isUploading: false,
        lock: livewireComponent.entangle('lockUpload'),
        get progressFormatted() {
            return this.progress.toFixed(2) + '%';
        },

        uploadChunks() {
            // Prevent multiple uploads
            if (this.isUploading) {
                console.log('Upload already in progress');
                return;
            }

            const fileInput = document.querySelector('input[name="myFile"]');
            if (fileInput.files[0]) {
                const file = fileInput.files[0];
                this.progress = 0;
                this.isUploading = true; // Set upload state to true

                livewireComponent.$set('fileSize', file.size, true);
                livewireComponent.$set('displayName', file.name, true);
                livewireComponent.$set('uniqueName', this.generateUUIDv7() + '.' + file.name.split('.').pop(), true);

                this.livewireUploadChunk(file, 0).catch(() => {
                    // Handle any errors that occur during upload
                    this.isUploading = false;
                    this.progress = 0;
                });
            }
        },

        async livewireUploadChunk(file, start) {
            console.log('Uploading chunk', start);
            const chunkSize = livewireComponent.$get('chunkSize');
            const chunkEnd = Math.min(start + chunkSize, file.size);
            const chunk = file.slice(start, chunkEnd, file.type);
            const chunkFile = new File([chunk], file.name, { type: file.type });

            try {
                await new Promise((resolve, reject) => {
                    livewireComponent.$upload(
                        'fileChunk',
                        chunkFile,
                        () => resolve(), // finish
                        (error) => {
                            console.error('Upload error:', error);
                            this.isUploading = false; // Reset on error
                            reject(error);
                        },
                        (event) => {
                            console.log(event.detail.progress);
                            this.progress = ((start + event.detail.progress) / file.size) * 100;

                            if (event.detail.progress == 100) {
                                start = chunkEnd;

                                if (start < file.size) {
                                    this.livewireUploadChunk(file, start);
                                }
                            }
                        }
                    );
                });
            } catch (error) {
                this.isUploading = false;
                throw error;
            }
        },

        generateUUIDv7() {
            const timestamp = Date.now();
            const randomBytes = crypto.getRandomValues(new Uint8Array(10));

            let uuid = timestamp.toString(16).padStart(12, '0');
            uuid += '-';
            uuid += (Math.floor(Math.random() * 0x1000)).toString(16).padStart(4, '0');
            uuid += '-7';
            uuid += (Math.floor(Math.random() * 0x1000)).toString(16).padStart(3, '0');
            uuid += '-';
            uuid += (Math.floor(Math.random() * 0x4000) + 0x8000).toString(16).padStart(4, '0');
            uuid += '-';

            for (let i = 0; i < randomBytes.length; i++) {
                uuid += randomBytes[i].toString(16).padStart(2, '0');
            }

            return uuid;
        }
    }));
})
