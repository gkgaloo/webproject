/**
 * Photo Upload Utilities
 * Handles candidate photo uploads with preview
 */

const PhotoUpload = {
    /**
     * Initialize photo upload for a form
     * @param {HTMLInputElement} fileInput - The file input element
     * @param {HTMLImageElement} previewElement - The image preview element
     * @param {Object} options - Configuration options
     */
    init(fileInput, previewElement, options = {}) {
        const defaults = {
            maxSize: 2 * 1024 * 1024, // 2MB
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png'],
            onSelect: null,
            onError: null
        };

        const config = { ...defaults, ...options };

        if (!fileInput) return;

        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];

            if (!file) {
                if (previewElement) {
                    previewElement.src = '';
                    previewElement.style.display = 'none';
                }
                return;
            }

            // Validate file type
            if (!config.allowedTypes.includes(file.type)) {
                const error = 'Only JPG, JPEG, and PNG images are allowed';
                if (config.onError) config.onError(error);
                fileInput.value = '';
                return;
            }

            // Validate file size
            if (file.size > config.maxSize) {
                const error = `File size must not exceed ${(config.maxSize / 1024 / 1024).toFixed(1)}MB`;
                if (config.onError) config.onError(error);
                fileInput.value = '';
                return;
            }

            // Show preview
            if (previewElement) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewElement.src = e.target.result;
                    previewElement.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            if (config.onSelect) config.onSelect(file);
        });
    },

    /**
     * Upload candidate photo using FormData
     * @param {string} endpoint - API endpoint
     * @param {Object} candidateData - Candidate data
     * @param {File} photoFile - Photo file to upload
     * @returns {Promise}
     */
    async uploadCandidateWithPhoto(endpoint, candidateData, photoFile) {
        const formData = new FormData();

        // Add candidate data
        for (const key in candidateData) {
            formData.append(key, candidateData[key]);
        }

        // Add photo file
        if (photoFile) {
            formData.append('photo', photoFile);
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });

            return await response.json();
        } catch (error) {
            console.error('Upload error:', error);
            return {
                success: false,
                message: 'Network error. Please try again.'
            };
        }
    },

    /**
     * Create image preview element
     * @param {string} containerId - Container element ID
     * @returns {HTMLImageElement}
     */
    createPreviewElement(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return null;

        const preview = document.createElement('img');
        preview.style.maxWidth = '200px';
        preview.style.maxHeight = '200px';
        preview.style.marginTop = '10px';
        preview.style.borderRadius = '8px';
        preview.style.display = 'none';
        preview.alt = 'Photo preview';

        container.appendChild(preview);
        return preview;
    },

    /**
     * Get candidate photo URL
     * @param {string} photoPath - Relative photo path
     * @returns {string}
     */
    getPhotoUrl(photoPath) {
        if (!photoPath || photoPath === 'null' || photoPath === '') {
            return '/onlinevoting/uploads/candidates/default_candidate.png';
        }
        return `/onlinevoting/${photoPath}`;
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PhotoUpload;
}
