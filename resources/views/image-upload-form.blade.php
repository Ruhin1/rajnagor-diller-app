<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ছবি প্রিন্ট করুন - Rongta RP336</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 16px;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab.active {
            color: #667eea;
            font-weight: bold;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #667eea;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #f9f9f9;
        }
        
        .upload-area:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .upload-area.dragover {
            border-color: #667eea;
            background: #e6efff;
        }
        
        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .upload-text {
            color: #666;
            margin-bottom: 10px;
        }
        
        .file-name {
            margin-top: 15px;
            padding: 10px;
            background: #e6efff;
            border-radius: 8px;
            color: #667eea;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .preview-container {
            margin-top: 20px;
            text-align: center;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .info-box {
            background: #e6efff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖨️ ছবি প্রিন্ট করুন</h1>
        <p class="subtitle">Rongta RP336 Thermal Printer</p>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('upload')">📤 Upload</button>
            <button class="tab" onclick="switchTab('url')">🔗 URL</button>
            <button class="tab" onclick="switchTab('qr')">📱 QR Code</button>
        </div>
        
        <!-- Tab 1: Upload -->
        <div id="tab-upload" class="tab-content active">
            <div class="info-box">
                <p>✅ Supported: JPG, PNG, GIF, BMP</p>
                <p>📏 Max Width: 576px (72mm)</p>
                <p>💾 Max Size: 5MB</p>
            </div>
            
            <form id="uploadForm">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📁</div>
                    <p class="upload-text">ছবি আপলোড করতে ক্লিক করুন অথবা এখানে drag & drop করুন</p>
                    <input type="file" id="imageInput" accept="image/*" style="display: none;">
                    <div class="file-name" id="fileName" style="display: none;"></div>
                </div>
                
                <div class="preview-container" id="previewContainer" style="display: none;">
                    <img id="previewImage" class="preview-image" src="" alt="Preview">
                </div>
                
                <div class="form-group">
                    <label for="width">Width (pixels):</label>
                    <input type="number" id="width" name="width" value="576" min="100" max="576">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="centered" name="centered" checked>
                        <label for="centered">মাঝখানে প্রিন্ট করুন</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                    🖨️ প্রিন্ট করুন
                </button>
            </form>
        </div>
        
        <!-- Tab 2: URL -->
        <div id="tab-url" class="tab-content">
            <div class="info-box">
                <p>🌐 যেকোনো ছবির URL দিন</p>
                <p>⚡ সরাসরি ইন্টারনেট থেকে প্রিন্ট করুন</p>
            </div>
            
            <form id="urlForm">
                <div class="form-group">
                    <label for="imageUrl">Image URL:</label>
                    <input type="text" id="imageUrl" name="url" placeholder="https://example.com/image.jpg">
                </div>
                
                <div class="form-group">
                    <label for="urlWidth">Width (pixels):</label>
                    <input type="number" id="urlWidth" name="width" value="576" min="100" max="576">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="urlCentered" name="centered" checked>
                        <label for="urlCentered">মাঝখানে প্রিন্ট করুন</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    🖨️ URL থেকে প্রিন্ট করুন
                </button>
            </form>
        </div>
        
        <!-- Tab 3: QR Code -->
        <div id="tab-qr" class="tab-content">
            <div class="info-box">
                <p>📱 QR Code তৈরি করে প্রিন্ট করুন</p>
                <p>🔗 URL, টেক্সট, যেকোনো কিছু encode করুন</p>
            </div>
            
            <form id="qrForm">
                <div class="form-group">
                    <label for="qrData">QR Code Data:</label>
                    <input type="text" id="qrData" name="data" placeholder="https://example.com বা যেকোনো টেক্সট">
                </div>
                
                <div class="form-group">
                    <label for="qrSize">Size (pixels):</label>
                    <input type="number" id="qrSize" name="size" value="300" min="100" max="400">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    🖨️ QR Code প্রিন্ট করুন
                </button>
            </form>
        </div>
        
        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Printing...</p>
        </div>
        
        <!-- Message -->
        <div class="message" id="message"></div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const imageInput = document.getElementById('imageInput');
        const fileName = document.getElementById('fileName');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');
        const uploadBtn = document.getElementById('uploadBtn');
        const loading = document.getElementById('loading');
        const message = document.getElementById('message');
        
        // Tab switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all tab buttons
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Upload area click
        uploadArea.addEventListener('click', () => {
            imageInput.click();
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                handleFileSelect();
            }
        });
        
        // File input change
        imageInput.addEventListener('change', handleFileSelect);
        
        function handleFileSelect() {
            const file = imageInput.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileName.style.display = 'block';
                uploadBtn.disabled = false;
                
                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Upload form submit
        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('image', imageInput.files[0]);
            formData.append('width', document.getElementById('width').value);
            formData.append('centered', document.getElementById('centered').checked ? 1 : 0);
            
            await submitPrint('/print-image-upload', formData);
        });
        
        // URL form submit
        document.getElementById('urlForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('url', document.getElementById('imageUrl').value);
            formData.append('width', document.getElementById('urlWidth').value);
            formData.append('centered', document.getElementById('urlCentered').checked ? 1 : 0);
            
            await submitPrint('/print-image-url', formData);
        });
        
        // QR form submit
        document.getElementById('qrForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('data', document.getElementById('qrData').value);
            formData.append('size', document.getElementById('qrSize').value);
            
            await submitPrint('/print-qr-code', formData);
        });
        
        // Submit print request
        async function submitPrint(url, formData) {
            loading.style.display = 'block';
            message.style.display = 'none';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                loading.style.display = 'none';
                message.style.display = 'block';
                
                if (result.success) {
                    message.className = 'message success';
                    message.textContent = '✅ ' + result.message;
                } else {
                    message.className = 'message error';
                    message.textContent = '❌ ' + result.message;
                }
                
                // Hide message after 5 seconds
                setTimeout(() => {
                    message.style.display = 'none';
                }, 5000);
                
            } catch (error) {
                loading.style.display = 'none';
                message.style.display = 'block';
                message.className = 'message error';
                message.textContent = '❌ Error: ' + error.message;
            }
        }
    </script>
</body>
</html>