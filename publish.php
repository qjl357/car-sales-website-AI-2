<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$seller_username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Publishing Console · Used Car Platform</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="pages.css">
<style>
.error-msg {
    color: #ff6b6b;
    font-size: 12px;
    margin-top: 4px;
    display: none;
}
.field-error {
    border-color: #ff6b6b !important;
}
.preview-image {
    max-width: 100%;
    max-height: 140px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.2);
    background: #f5eddc;
    padding: 8px;
}
.preview-image img {
    max-width: 100%;
    max-height: 120px;
    border-radius: 6px;
    display: block;
}
.file-name {
    font-size: 12px;
    color: #c5b18a;
    margin-top: 6px;
}
.image-note {
    font-size: 11px;
    color: #ba9f7a;
    margin-top: 5px;
}
.auth-wrap {
    max-width: 760px;
    margin: 18px 0 0 68px;
}
.form-grid {
    grid-template-columns: 1fr;
    gap: 12px 16px;
}
.input, .textarea {
    text-align: left;
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
.form-actions-inline {
    margin-top: 18px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.form-actions-inline .btn {
    width: auto !important;
    min-width: 170px;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.btn-success {
    background: #2c5a3b;
    color: white;
}
.btn-ghost {
    background: #e0e0e0;
    color: #333;
}
.alert-success {
    background: #2c5a3b;
    color: #e2e8c3;
    padding: 10px 12px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.alert-error {
    background: #6b2c2c;
    color: #ffc3c3;
    padding: 10px 12px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.user-bar {
    background: #f5eddc;
    padding: 10px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: right;
    color: #333;
}
.user-bar a {
    color: #c58e38;
    margin-left: 15px;
}
@media (max-width: 900px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .form-actions-inline .btn {
        width: 100% !important;
    }
}
</style>
</head>
<body>
<main class="page-shell lt-shell">
<header class="lt-topbar">
    <a class="lt-back" href="home.html" aria-label="Back to control">‹</a>
    <div class="lt-brand-block">
        <p class="lt-brand">Inventory Operations</p>
        <h1>Publishing Console</h1>
    </div>
    <nav class="lt-nav">
        <a class="lt-nav-link" href="home.html">Home</a>
        <a class="lt-nav-link active" href="publish.php">Publish</a>
        <a class="lt-nav-link" href="login.php">Login</a>
    </nav>
</header>

<div class="auth-wrap">
    <div class="card">
        <div class="user-bar">
            Welcome, <strong><?php echo htmlspecialchars($seller_username); ?></strong>
            <a href="logout.php">Logout</a>
        </div>

        <h2 style="text-align:left; margin:0 0 20px 0;">Vehicle Publish</h2>

        <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert-success">✓ Vehicle added successfully!</div>
        <?php endif; ?>

        <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="alert-error">✗ Failed to add vehicle. Please try again.</div>
        <?php endif; ?>

        <form id="vehiclePublishForm" action="save_vehicle.php" method="post" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field">
                    <label for="vehicle-color">Color *</label>
                    <input class="input" id="vehicle-color" name="color" type="text" placeholder="e.g. Red, Blue, Black">
                    <div class="error-msg" id="colorError"></div>
                </div>

                <div class="field">
                    <label for="vehicle-model">Model *</label>
                    <input class="input" id="vehicle-model" name="model" type="text" placeholder="e.g. BMW X5">
                    <div class="error-msg" id="modelError"></div>
                </div>

                <div class="field">
                    <label for="vehicle-year">Year (4 digits) *</label>
                    <input class="input" id="vehicle-year" name="year" type="text" maxlength="4" inputmode="numeric" placeholder="e.g. 2023">
                    <div class="error-msg" id="yearError"></div>
                </div>

                <div class="field">
                    <label for="vehicle-location">Location *</label>
                    <input class="input" id="vehicle-location" name="location" type="text" placeholder="e.g. Beijing">
                    <div class="error-msg" id="locationError"></div>
                </div>

                <div class="field">
                    <label for="vehicle-price">Price (¥) *</label>
                    <input class="input" id="vehicle-price" name="price" type="text" inputmode="numeric" placeholder="e.g. 199900">
                    <div class="error-msg" id="priceError"></div>
                </div>

                <div class="field">
                    <label for="vehicle-image">Car Image</label>
                    <input class="input" type="file" id="vehicle-image" name="vehicle_image" accept="image/jpeg, image/png, image/jpg, image/webp">
                    <div id="imagePreviewContainer" class="preview-image" style="display: none;"></div>
                    <div class="file-name" id="imageFileName">No file chosen</div>
                    <div class="image-note">Supports JPG, PNG, WEBP.</div>
                    <div class="error-msg" id="imageError"></div>
                </div>
            </div>

            <div class="form-actions-inline">
                <button class="btn btn-success" type="submit">Submit for Review</button>
                <button class="btn btn-ghost" type="reset" id="resetFormBtn">Reset</button>
            </div>
        </form>
    </div>
</div>
</main>

<script>
(function() {
    const colorInput = document.getElementById('vehicle-color');
    const modelInput = document.getElementById('vehicle-model');
    const yearInput = document.getElementById('vehicle-year');
    const locationInput = document.getElementById('vehicle-location');
    const priceInput = document.getElementById('vehicle-price');
    const imageInput = document.getElementById('vehicle-image');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imageFileNameSpan = document.getElementById('imageFileName');
    const form = document.getElementById('vehiclePublishForm');
    const resetBtn = document.getElementById('resetFormBtn');

    const colorError = document.getElementById('colorError');
    const modelError = document.getElementById('modelError');
    const yearError = document.getElementById('yearError');
    const locationError = document.getElementById('locationError');
    const priceError = document.getElementById('priceError');
    const imageError = document.getElementById('imageError');

    function clearAllErrors() {
        const errorDivs = [colorError, modelError, yearError, locationError, priceError, imageError];
        errorDivs.forEach(div => { if(div) { div.style.display = 'none'; div.innerText = ''; } });
        const fields = [colorInput, modelInput, yearInput, locationInput, priceInput];
        fields.forEach(field => { if(field) field.classList.remove('field-error'); });
        if(imageInput) imageInput.classList.remove('field-error');
    }

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        imageError.style.display = 'none';
        imageFileNameSpan.innerText = file ? file.name : 'No file chosen';
        
        imagePreviewContainer.innerHTML = '';
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const imgElement = document.createElement('img');
                imgElement.src = ev.target.result;
                imgElement.alt = 'Car preview';
                imgElement.style.maxWidth = '100%';
                imgElement.style.borderRadius = '6px';
                imagePreviewContainer.appendChild(imgElement);
                imagePreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else if (file) {
            imageError.innerText = 'Please select a valid image file.';
            imageError.style.display = 'block';
            imagePreviewContainer.style.display = 'none';
            imageFileNameSpan.innerText = 'Invalid file';
        } else {
            imagePreviewContainer.style.display = 'none';
        }
    });

    function resetFormFields() {
        form.reset();
        imagePreviewContainer.innerHTML = '';
        imagePreviewContainer.style.display = 'none';
        imageFileNameSpan.innerText = 'No file chosen';
        clearAllErrors();
        const allInputs = [colorInput, modelInput, yearInput, locationInput, priceInput];
        allInputs.forEach(inp => { if(inp) inp.classList.remove('field-error'); });
    }
    
    resetBtn.addEventListener('click', function(e) {
        e.preventDefault();
        resetFormFields();
    });

    function validateYear(yearStr) {
        if (!yearStr) return false;
        return /^\d{4}$/.test(yearStr.trim());
    }

    function validatePrice(priceStr) {
        if (!priceStr) return false;
        return /^\d+(\.\d+)?$/.test(priceStr.trim());
    }

    form.addEventListener('submit', function(e) {
        clearAllErrors();
        let isValid = true;

        const colorVal = colorInput.value.trim();
        if (!colorVal) {
            colorError.innerText = 'Please enter a color.';
            colorError.style.display = 'block';
            colorInput.classList.add('field-error');
            isValid = false;
        }

        const modelVal = modelInput.value.trim();
        if (!modelVal) {
            modelError.innerText = 'Model is required.';
            modelError.style.display = 'block';
            modelInput.classList.add('field-error');
            isValid = false;
        }

        const yearVal = yearInput.value.trim();
        if (!yearVal) {
            yearError.innerText = 'Year is required.';
            yearError.style.display = 'block';
            yearInput.classList.add('field-error');
            isValid = false;
        } else if (!validateYear(yearVal)) {
            yearError.innerText = 'Year must be 4 digits (e.g. 2023).';
            yearError.style.display = 'block';
            yearInput.classList.add('field-error');
            isValid = false;
        }

        const locationVal = locationInput.value.trim();
        if (!locationVal) {
            locationError.innerText = 'Location is required.';
            locationError.style.display = 'block';
            locationInput.classList.add('field-error');
            isValid = false;
        }

        const priceVal = priceInput.value.trim();
        if (!priceVal) {
            priceError.innerText = 'Price is required.';
            priceError.style.display = 'block';
            priceInput.classList.add('field-error');
            isValid = false;
        } else if (!validatePrice(priceVal)) {
            priceError.innerText = 'Price must be a valid number.';
            priceError.style.display = 'block';
            priceInput.classList.add('field-error');
            isValid = false;
        }

        const imageFile = imageInput.files[0];
        if (imageFile && !imageFile.type.startsWith('image/')) {
            imageError.innerText = 'Please select a valid image.';
            imageError.style.display = 'block';
            imageInput.classList.add('field-error');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            const firstError = document.querySelector('.field-error');
            if(firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();
</script>
</body>
</html>