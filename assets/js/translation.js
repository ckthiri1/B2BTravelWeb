// assets/js/translator.js

class PageTranslator {
    constructor() {
        this.currentLanguage = document.documentElement.lang || 'en';
        this.translationCache = {};
        this.initLanguageSelector();
    }

    initLanguageSelector() {
        const langSelector = document.createElement('div');
        langSelector.className = 'language-selector';
        
        const languages = [
            {code: 'en', name: 'English'},
            {code: 'fr', name: 'Français'},
            {code: 'es', name: 'Español'},
            {code: 'de', name: 'Deutsch'},
            {code: 'it', name: 'Italiano'}
        ];
        
        let options = languages.map(lang => 
            `<option value="${lang.code}" ${this.currentLanguage === lang.code ? 'selected' : ''}>${lang.name}</option>`
        ).join('');
        
        langSelector.innerHTML = `
            <select id="language-select">
                ${options}
            </select>
        `;
        
        document.body.prepend(langSelector);

        document.getElementById('language-select').addEventListener('change', async (e) => {
            this.currentLanguage = e.target.value;
            document.documentElement.lang = this.currentLanguage;
            localStorage.setItem('preferredLanguage', this.currentLanguage);
            await this.translatePage(this.currentLanguage);
        });
    }

    async translatePage(targetLanguage) {
        try {
            // Get all translatable elements
            const translatableElements = document.querySelectorAll('[data-translate], p, h1, h2, h3, h4, h5, h6, span, a, li, td, th, button, label');
            
            // Batch translations to reduce API calls
            const translationPromises = [];
            const elementsToTranslate = [];
            
            for (const element of translatableElements) {
                if (this.shouldSkipTranslation(element)) continue;
                
                const text = element.textContent.trim();
                if (!text) continue;
                
                const cacheKey = `trans_${targetLanguage}_${text}`;
                
                if (this.translationCache[cacheKey]) {
                    element.textContent = this.translationCache[cacheKey];
                    continue;
                }
                
                elementsToTranslate.push({element, text, cacheKey});
            }
            
            // Batch translate in chunks of 10
            const chunkSize = 10;
            for (let i = 0; i < elementsToTranslate.length; i += chunkSize) {
                const chunk = elementsToTranslate.slice(i, i + chunkSize);
                const texts = chunk.map(item => item.text);
                
                const response = await fetch('/api/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        texts,
                        target: targetLanguage,
                        source: 'en'
                    })
                });
                
                const data = await response.json();
                
                if (data.translatedTexts) {
                    data.translatedTexts.forEach((translation, index) => {
                        const {element, cacheKey} = chunk[index];
                        this.translationCache[cacheKey] = translation;
                        element.textContent = translation;
                    });
                }
            }
            
        } catch (error) {
            console.error('Translation error:', error);
        }
    }

    shouldSkipTranslation(element) {
        // Skip elements that should not be translated
        return element.tagName === 'SCRIPT' || 
               element.tagName === 'STYLE' || 
               element.tagName === 'CODE' || 
               element.hasAttribute('data-no-translate');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.pageTranslator = new PageTranslator();
    
    // Restore preferred language if set
    const savedLang = localStorage.getItem('preferredLanguage');
    if (savedLang) {
        document.documentElement.lang = savedLang;
        document.getElementById('language-select').value = savedLang;
    }
});