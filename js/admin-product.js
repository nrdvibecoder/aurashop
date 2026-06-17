let colorIndex = window.colorIndex || 0;
const colorBlocks = window.colorBlocks || {};  // { colorIndex: { files: [] } }

function addColorBlock() {
    colorIndex++;
    const idx = colorIndex;
    colorBlocks[idx] = { files: [] };

    const container = document.getElementById('color-blocks');
    const block = document.createElement('div');
    block.className = 'color-block border border-outline-variant/20 rounded-xl p-6 space-y-4 bg-surface-container relative';
    block.dataset.idx = idx;

    block.innerHTML = `
        <button type="button" onclick="removeColorBlock(this)" class="absolute top-4 right-4 text-error hover:text-on-error-container transition-colors material-symbols-outlined text-xl">close</button>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant block mb-2">Nom de la couleur</label>
                <input type="text" name="color_name_${idx}" placeholder="ex: Sage Green" 
                    class="w-full bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 text-on-surface font-body-md focus:border-primary focus:ring-0 focus:outline-none" required>
            </div>
            <div>
                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant block mb-2">Code Hex de la couleur</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color_hex_${idx}" value="#b2cdbc" 
                        class="w-12 h-12 rounded-lg border border-outline-variant/20 bg-transparent cursor-pointer">
                    <input type="text" name="color_hex_text_${idx}" placeholder="#b2cdbc" value="#b2cdbc"
                        class="flex-1 bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 text-on-surface font-body-md focus:border-primary focus:ring-0 focus:outline-none">
                </div>
            </div>
        </div>
        <div>
            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant block mb-2">Images pour cette couleur</label>
            <div class="border-2 border-dashed border-outline-variant/30 rounded-xl p-6 text-center hover:border-primary transition-colors cursor-pointer" 
                onclick="document.getElementById('color-image-input-${idx}').click()">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-2 block">add_photo_alternate</span>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">Cliquez pour ajouter des images</p>
                <p class="font-body-md text-outline text-sm mt-1">Multiples images acceptées</p>
            </div>
            <input type="file" id="color-image-input-${idx}" multiple accept="image/*" 
                class="hidden" onchange="handleColorImages(event, ${idx})">
            <div id="color-thumbnails-${idx}" class="flex flex-wrap gap-3 mt-4"></div>
        </div>
    `;

    container.appendChild(block);

    // Sync hex color picker <-> text input
    const picker = block.querySelector(`[name="color_hex_${idx}"]`);
    const hexText = block.querySelector(`[name="color_hex_text_${idx}"]`);
    picker.addEventListener('input', () => { hexText.value = picker.value; });
    hexText.addEventListener('input', () => {
        if (/^#[0-9A-Fa-f]{6}$/.test(hexText.value)) {
            picker.value = hexText.value;
        }
    });
}

function removeColorBlock(btn) {
    const block = btn.closest('.color-block');
    const idx = block.dataset.idx;
    delete colorBlocks[idx];
    block.remove();
}

// Helper function for compressing images
function compressAndResizeImage(file, callback) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const maxDimension = 1200;
            let width = img.width;
            let height = img.height;

            if (width > height) {
                if (width > maxDimension) {
                    height = Math.round((height * maxDimension) / width);
                    width = maxDimension;
                }
            } else {
                if (height > maxDimension) {
                    width = Math.round((width * maxDimension) / height);
                    height = maxDimension;
                }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            // Compress to JPEG with 0.8 quality
            const compressedBase64 = canvas.toDataURL('image/jpeg', 0.8);
            callback(compressedBase64);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function handleColorImages(event, idx) {
    const files = Array.from(event.target.files);
    const thumbContainer = document.getElementById(`color-thumbnails-${idx}`);
    if (!colorBlocks[idx]) colorBlocks[idx] = { files: [] };

    files.forEach(file => {
        compressAndResizeImage(file, function(compressedBase64) {
            colorBlocks[idx].files.push(compressedBase64);
            const arrayIndex = colorBlocks[idx].files.length - 1;

            const thumb = document.createElement('div');
            thumb.className = 'relative w-20 h-20 rounded-lg overflow-hidden border border-outline-variant/20';
            thumb.dataset.arrayIndex = arrayIndex;
            thumb.innerHTML = `
                <img src="${compressedBase64}" class="w-full h-full object-cover">
                <button type="button" onclick="removeColorThumbnail(this, ${idx})" 
                    class="absolute top-0.5 right-0.5 w-5 h-5 bg-error text-on-error rounded-full flex items-center justify-center text-xs font-bold hover:scale-110 transition-transform">✕</button>
            `;
            thumbContainer.appendChild(thumb);
        });
    });
}

function removeColorThumbnail(btn, idx) {
    const thumb = btn.closest('[data-array-index]');
    const arrayIndex = parseInt(thumb.dataset.arrayIndex);
    if (colorBlocks[idx]) {
        // Remove item from array and mark it as null or filter it out, or slice
        colorBlocks[idx].files.splice(arrayIndex, 1);
        // Re-index all remaining thumbnails in the DOM
        const container = document.getElementById(`color-thumbnails-${idx}`);
        const thumbs = container.querySelectorAll('[data-array-index]');
        thumbs.forEach((t, index) => {
            t.dataset.arrayIndex = index;
        });
    }
    thumb.remove();
}

// Handle main image preview
function handleMainImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    compressAndResizeImage(file, function(compressedBase64) {
        const preview = document.getElementById('main-image-preview');
        const previewContainer = document.getElementById('main-image-preview-container');
        if (preview) {
            preview.src = compressedBase64;
            previewContainer.classList.remove('hidden');
        }
        const base64Input = document.getElementById('base64-image-input');
        if (base64Input) base64Input.value = compressedBase64;
    });
}

// Serialize everything on form submit
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('product-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const colorsInput = document.getElementById('colors-input');
        const colorImagesInput = document.getElementById('color-images-input');
        const sizesInput = document.getElementById('sizes-input');

        // Collect colors
        const colorBlockEls = document.querySelectorAll('.color-block');
        const colors = [];
        const colorImages = {};

        colorBlockEls.forEach(block => {
            const idx = block.dataset.idx;
            const nameInput = block.querySelector(`[name="color_name_${idx}"]`);
            const hexInput = block.querySelector(`[name="color_hex_${idx}"]`);
            if (nameInput && nameInput.value.trim()) {
                const colorName = nameInput.value.trim();
                colors.push({ name: colorName, hex: hexInput ? hexInput.value : '#000000' });
                if (colorBlocks[idx] && colorBlocks[idx].files.length > 0) {
                    colorImages[colorName] = colorBlocks[idx].files;
                }
            }
        });

        if (colorsInput) colorsInput.value = JSON.stringify(colors);
        if (colorImagesInput) colorImagesInput.value = JSON.stringify(colorImages);

        // Collect checked sizes
        const sizeCheckboxes = document.querySelectorAll('[name="size[]"]:checked');
        const sizes = Array.from(sizeCheckboxes).map(cb => cb.value);
        if (sizesInput) sizesInput.value = JSON.stringify(sizes);
    });

    // Initialize with one color block if adding
    const colorContainer = document.getElementById('color-blocks');
    if (colorContainer && colorContainer.children.length === 0) {
        addColorBlock();
    }

    // Pre-fill existing color blocks for edit page
    if (window.existingColors && window.existingColors.length > 0) {
        window.existingColors.forEach(c => {
            addColorBlock();
            const lastBlock = document.querySelector('.color-block:last-child');
            if (lastBlock) {
                const nameInput = lastBlock.querySelector(`[name$="_${colorIndex}"]`);
                if (nameInput) nameInput.value = c.name;
            }
        });
    }
});
