function food_analysis_shortcode() {
    ob_start();
    ?>
    <div id="food-analysis-container" style="max-width: 375px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <style>
            /* Phone Container */
            .phone-container {
                width: 375px;
                height: 812px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 40px;
                padding: 8px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                position: relative;
                margin: 20px auto;
            }
            
            .phone-screen {
                width: 100%;
                height: 100%;
                background: #000;
                border-radius: 32px;
                overflow: hidden;
                position: relative;
            }
            
            .phone-notch {
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 150px;
                height: 30px;
                background: #000;
                border-radius: 0 0 15px 15px;
                z-index: 10;
            }
            
            .phone-content {
                height: 100%;
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                overflow-y: auto;
                padding: 35px 20px 20px;
                position: relative;
            }
            
            /* Status Bar */
            .status-bar {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 35px;
                background: rgba(0, 0, 0, 0.05);
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 20px;
                font-size: 14px;
                font-weight: 600;
                color: #333;
                z-index: 5;
            }
            
            .status-left {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .status-right {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            /* App Content */
            .app-header {
                text-align: center;
                margin-bottom: 30px;
                padding-top: 20px;
            }
            
            .app-title {
                color: #2d3748;
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 8px;
            }
            
            .app-subtitle {
                color: #718096;
                font-size: 16px;
                line-height: 1.4;
            }
            
            .food-analysis-form {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 25px;
                margin-bottom: 20px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                display: block;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 12px;
                font-size: 16px;
            }
            
            .image-preview {
                display: none;
                margin-bottom: 20px;
                text-align: center;
                background: rgba(255, 255, 255, 0.8);
                border-radius: 15px;
                padding: 15px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            }
            
            .preview-image {
                max-width: 100%;
                max-height: 200px;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                border: 2px solid rgba(255, 255, 255, 0.8);
            }
            
            .preview-filename {
                margin-top: 10px;
                font-size: 14px;
                color: #4a5568;
                font-weight: 500;
            }
            
            .file-input-wrapper {
                position: relative;
                display: block;
                width: 100%;
            }
            
            .file-input {
                width: 100%;
                padding: 20px;
                border: 2px dashed #cbd5e0;
                border-radius: 15px;
                background: rgba(247, 250, 252, 0.8);
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
                color: #4a5568;
                font-size: 16px;
                font-weight: 500;
                position: relative;
                min-height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .file-input:hover {
                border-color: #667eea;
                background: rgba(102, 126, 234, 0.1);
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
            }
            
            .file-input.has-file {
                border-color: #48bb78;
                background: rgba(72, 187, 120, 0.1);
                color: #2f855a;
            }
            
            .hidden-file-input {
                position: absolute;
                left: 0;
                top: 0;
                opacity: 0;
                width: 100%;
                height: 100%;
                cursor: pointer;
                z-index: 2;
            }
            
            .submit-btn {
                width: 100%;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                padding: 18px 20px;
                border: none;
                border-radius: 15px;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 15px;
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
            }
            
            .submit-btn:hover:not(:disabled) {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            }
            
            .submit-btn:disabled {
                background: #a0aec0;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }
            
            .loading-spinner {
                display: none;
                text-align: center;
                padding: 30px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                margin: 20px 0;
            }
            
            .spinner {
                border: 4px solid rgba(102, 126, 234, 0.1);
                border-top: 4px solid #667eea;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin: 0 auto 15px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .loading-text {
                color: #4a5568;
                font-size: 16px;
                font-weight: 500;
            }
            
            .results-container {
                display: none;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 25px;
                margin: 20px 0;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .results-title {
                color: #2d3748;
                font-size: 20px;
                font-weight: 700;
                margin-bottom: 20px;
                text-align: center;
            }
            
            .meal-name {
                background: linear-gradient(135deg, #48bb78, #38a169);
                color: white;
                padding: 18px 20px;
                border-radius: 15px;
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 25px;
                text-align: center;
                box-shadow: 0 4px 16px rgba(72, 187, 120, 0.3);
            }
            
            .nutrition-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                margin-bottom: 25px;
            }
            
            .nutrition-item {
                background: rgba(247, 250, 252, 0.8);
                padding: 15px;
                border-radius: 12px;
                border-left: 4px solid #667eea;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                min-height: 70px;
                justify-content: center;
            }
            
            .nutrition-label {
                font-weight: 600;
                color: #4a5568;
                font-size: 12px;
                margin-bottom: 4px;
                text-transform: uppercase;
            }
            
            .nutrition-value {
                font-weight: 700;
                color: #2d3748;
                font-size: 16px;
            }
            
            .scores-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-top: 20px;
            }
            
            .score-item {
                text-align: center;
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            }
            
            .confidence-score {
                background: linear-gradient(135deg, #9f7aea, #805ad5);
                color: white;
            }
            
            .health-score {
                background: linear-gradient(135deg, #ed8936, #dd6b20);
                color: white;
            }
            
            .score-value {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 5px;
            }
            
            .score-label {
                font-size: 12px;
                opacity: 0.9;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .error-message {
                display: none;
                background: rgba(254, 242, 242, 0.9);
                border: 1px solid #fed7d7;
                color: #c53030;
                padding: 20px;
                border-radius: 15px;
                margin: 20px 0;
                text-align: center;
                backdrop-filter: blur(10px);
            }
            
            .try-again-btn {
                background: linear-gradient(135deg, #718096, #4a5568);
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                margin-top: 15px;
                font-size: 14px;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            .try-again-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(113, 128, 150, 0.3);
            }
            
            /* Responsive adjustments */
            @media (max-width: 400px) {
                .phone-container {
                    width: 100%;
                    max-width: 375px;
                    margin: 10px auto;
                }
                
                .nutrition-grid {
                    grid-template-columns: 1fr;
                }
                
                .scores-container {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        
        <div class="phone-container">
            <div class="phone-screen">
                <div class="phone-notch"></div>
                <div class="phone-content">
                    <div class="status-bar">
                        <div class="status-left">
                            <span>9:41</span>
                        </div>
                        <div class="status-right">
                            <span>📶</span>
                            <span>📶</span>
                            <span>🔋</span>
                        </div>
                    </div>
                    
                    <div class="app-header">
                        <h1 class="app-title">🍽️ Food AI</h1>
                        <p class="app-subtitle">Snap a photo of your meal for instant nutritional analysis</p>
                    </div>
                    
                    <div class="food-analysis-form">
                        <form id="foodAnalysisForm">
                            <div class="form-group">
                                <label class="form-label" for="imageFile">📸 Upload Food Photo</label>
                                
                                <div class="image-preview" id="imagePreview">
                                    <img class="preview-image" id="previewImage" alt="Food preview">
                                    <div class="preview-filename" id="previewFilename"></div>
                                </div>
                                
                                <div class="file-input-wrapper">
                                    <div class="file-input" id="fileInputDisplay">
                                        📷 Tap to select photo
                                    </div>
                                    <input type="file" class="hidden-file-input" id="imageFile" accept="image/*" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="submit-btn" id="submitBtn">
                                🔍 Analyze Food
                            </button>
                        </form>
                    </div>
                    
                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="spinner"></div>
                        <p class="loading-text">Analyzing your delicious meal...</p>
                    </div>
                    
                    <div class="results-container" id="resultsContainer">
                        <h3 class="results-title">📊 Nutrition Analysis</h3>
                        <div id="resultsContent"></div>
                        <button class="try-again-btn" onclick="resetForm()">📷 Analyze Another</button>
                    </div>
                    
                    <div class="error-message" id="errorMessage">
                        <p id="errorText">😕 Something went wrong</p>
                        <button class="try-again-btn" onclick="resetForm()">🔄 Try Again</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('foodAnalysisForm');
            const fileInput = document.getElementById('imageFile');
            const fileInputDisplay = document.getElementById('fileInputDisplay');
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const resultsContainer = document.getElementById('resultsContainer');
            const errorMessage = document.getElementById('errorMessage');
            const imagePreview = document.getElementById('imagePreview');
            const previewImage = document.getElementById('previewImage');
            const previewFilename = document.getElementById('previewFilename');
            
            // File input handling
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    displayFilePreview(file);
                } else {
                    hideFilePreview();
                }
            });
            
            // Click handler for the display div
            fileInputDisplay.addEventListener('click', function(e) {
                fileInput.click();
            });
            
            // Drag and drop functionality
            fileInputDisplay.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = '#667eea';
                this.style.background = 'rgba(102, 126, 234, 0.1)';
            });
            
            fileInputDisplay.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.borderColor = '#cbd5e0';
                this.style.background = 'rgba(247, 250, 252, 0.8)';
            });
            
            fileInputDisplay.addEventListener('drop', function(e) {
                e.preventDefault();
                const files = e.dataTransfer.files;
                if (files.length > 0 && files[0].type.startsWith('image/')) {
                    // Manually set the files and trigger change event
                    const dt = new DataTransfer();
                    dt.items.add(files[0]);
                    fileInput.files = dt.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
                this.style.borderColor = '#cbd5e0';
                this.style.background = 'rgba(247, 250, 252, 0.8)';
            });
            
            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const file = fileInput.files[0];
                if (!file) {
                    showError('Please select an image file.');
                    return;
                }
                
                // Show loading state
                showLoading();
                
                try {
                    // Create FormData and append the image file
                    const formData = new FormData();
                    formData.append('image', file);
                    
                    // Make API call with binary file
                    const response = await fetch('WEBHOOK_URL', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('API Response:', data); // Debug log
                    
                    // Handle the response structure more robustly
                    let resultData = null;
                    
                    if (data && Array.isArray(data) && data.length > 0) {
                        // Handle array response like your example
                        if (data[0].response && data[0].response.body) {
                            resultData = data[0].response.body;
                        } else if (data[0].body) {
                            resultData = data[0].body;
                        } else if (data[0].mealName) {
                            resultData = data[0];
                        }
                    } else if (data && data.response && data.response.body) {
                        // Handle single object with nested response
                        resultData = data.response.body;
                    } else if (data && data.body) {
                        // Handle single object with body
                        resultData = data.body;
                    } else if (data && data.mealName) {
                        // Handle direct object
                        resultData = data;
                    }
                    
                    if (resultData && resultData.mealName) {
                        displayResults(resultData);
                    } else {
                        console.error('Could not parse response:', data);
                        throw new Error('Invalid response format from server. Please check console for details.');
                    }
                    
                } catch (error) {
                    console.error('Error:', error);
                    showError('Failed to analyze the image. Please try again.');
                } finally {
                    hideLoading();
                }
            });
        });
        
        function displayFilePreview(file) {
            // Update display text and style
            fileInputDisplay.textContent = `✅ ${file.name}`;
            fileInputDisplay.classList.add('has-file');
            
            // Show image preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewFilename.textContent = file.name;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        
        function hideFilePreview() {
            fileInputDisplay.textContent = '📷 Tap to select photo';
            fileInputDisplay.classList.remove('has-file');
            imagePreview.style.display = 'none';
        }
        
        function showLoading() {
            loadingSpinner.style.display = 'block';
            submitBtn.disabled = true;
            hideError();
            hideResults();
        }
        
        function hideLoading() {
            loadingSpinner.style.display = 'none';
            submitBtn.disabled = false;
        }
        
        function showError(message) {
            document.getElementById('errorText').textContent = message;
            errorMessage.style.display = 'block';
            hideResults();
        }
        
        function hideError() {
            errorMessage.style.display = 'none';
        }
        
        function hideResults() {
            resultsContainer.style.display = 'none';
        }
        
        function displayResults(data) {
            const resultsContent = document.getElementById('resultsContent');
            
            // Ensure we have all required data with fallbacks
            const mealName = data.mealName || 'Unknown Meal';
            const calories = data.calories || 0;
            const protein = data.protein || 0;
            const carbs = data.carbs || 0;
            const fat = data.fat || 0;
            const fiber = data.fiber || 0;
            const sugar = data.sugar || 0;
            const sodium = data.sodium || 0;
            const confidenceScore = data.confidenceScore || 0;
            const healthScore = data.healthScore || 0;
            
            // Format confidence score as percentage
            const confidencePercent = Math.round(confidenceScore * 100);
            
            console.log('Displaying results for:', mealName); // Debug log
            
            resultsContent.innerHTML = `
                <div class="meal-name">${mealName}</div>
                
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <span class="nutrition-label">Calories</span>
                        <span class="nutrition-value">${calories}</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label">Protein</span>
                        <span class="nutrition-value">${protein}g</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label">Carbs</span>
                        <span class="nutrition-value">${carbs}g</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label">Fat</span>
                        <span class="nutrition-value">${fat}g</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label">Fiber</span>
                        <span class="nutrition-value">${fiber}g</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label">Sugar</span>
                        <span class="nutrition-value">${sugar}g</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label">Sodium</span>
                        <span class="nutrition-value">${sodium}mg</span>
                    </div>
                </div>
                
                <div class="scores-container">
                    <div class="score-item confidence-score">
                        <div class="score-value">${confidencePercent}%</div>
                        <div class="score-label">Confidence</div>
                    </div>
                    <div class="score-item health-score">
                        <div class="score-value">${healthScore}/10</div>
                        <div class="score-label">Health Score</div>
                    </div>
                </div>
            `;
            
            resultsContainer.style.display = 'block';
            hideError();
        }
        
        function resetForm() {
            form.reset();
            hideFilePreview();
            hideResults();
            hideError();
        }
    </script>
    
    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('food_analysis', 'food_analysis_shortcode');
