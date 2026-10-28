/**
 * FRONTEND FIX FOR MULTIPART FORM DATA
 * 
 * Use this function to clean your form data before sending
 */

function cleanFormDataForUpload(formData) {
    /**
     * Remove duplicate fields from FormData
     * Keep only the last occurrence of each field
     */
    const cleaned = new FormData();
    const fields = {};
    
    // First pass: identify all fields
    for (const [key, value] of formData.entries()) {
        if (key.endsWith('[]')) {
            // Handle array fields separately
            if (!fields[key]) fields[key] = [];
            fields[key].push(value);
        } else {
            // For regular fields, keep the last value
            fields[key] = value;
        }
    }
    
    // Second pass: rebuild FormData without duplicates
    for (const [key, value] of Object.entries(fields)) {
        if (Array.isArray(value)) {
            // Add all array elements
            value.forEach(v => cleaned.append(key, v));
        } else {
            // Add single value
            if (value !== null && value !== undefined && value !== '') {
                cleaned.append(key, value);
            }
        }
    }
    
    // Remove empty file uploads
    if (cleaned.has('file')) {
        // Check if file is empty
        const file = cleaned.get('file');
        if (!file || file.size === 0 || !file.name) {
            cleaned.delete('file');
        }
    }
    
    return cleaned;
}

/**
 * Example usage in your product form submission:
 * 
 * const form = document.querySelector('form#productForm');
 * const formData = new FormData(form);
 * 
 * // Clean the data
 * const cleanedData = cleanFormDataForUpload(formData);
 * 
 * // Send it
 * fetch(form.action, {
 *     method: 'POST',
 *     body: cleanedData,
 *     headers: {
 *         'X-Requested-With': 'XMLHttpRequest'
 *         // Don't set Content-Type, browser will set it with boundary
 *     }
 * })
 * .then(r => r.json())
 * .then(d => {
 *     if(d.status === 'success') {
 *         window.location.href = d.redirectTo;
 *     } else {
 *         console.error(d);
 *     }
 * });
 */

// Alternative: Send as JSON instead (much cleaner)
function submitProductFormAsJSON() {
    const form = document.querySelector('form#productForm');
    const formData = new FormData(form);
    
    const jsonData = {
        _token: formData.get('_token'),
        name: formData.get('name'),
        slug: formData.get('slug'),
        sku: formData.get('sku'),
        brand_id: formData.get('brand_id'),
        product_type: formData.get('product_type'),
        regular_price: formData.get('regular_price'),
        sale_price: formData.get('sale_price'),
        // ... add all fields you need
        gallery_images: formData.get('gallery_images'),
        categories: formData.getAll('categories[]'),
    };
    
    // Send as JSON instead of multipart
    return fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify(jsonData)
    })
    .then(r => r.json())
    .catch(e => {
        console.error('Form submission error:', e);
        throw e;
    });
}
