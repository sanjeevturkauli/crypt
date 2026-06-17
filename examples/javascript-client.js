/**
 * JavaScript Client Example for Response Crypt Package
 * This demonstrates how to encrypt requests and decrypt responses
 * using CryptoJS library
 */

// Install: npm install crypto-js
import CryptoJS from 'crypto-js';

class SecureApiClient {
    constructor(baseUrl, encryptionKey) {
        this.baseUrl = baseUrl;
        this.encryptionKey = encryptionKey;
    }

    /**
     * Encrypt data for sending to API
     */
    encryptData(data) {
        const jsonString = JSON.stringify(data);
        const encrypted = CryptoJS.AES.encrypt(jsonString, this.encryptionKey).toString();
        return encrypted;
    }

    /**
     * Decrypt data received from API
     */
    decryptData(encryptedData) {
        const decrypted = CryptoJS.AES.decrypt(encryptedData, this.encryptionKey);
        const jsonString = decrypted.toString(CryptoJS.enc.Utf8);
        return JSON.parse(jsonString);
    }

    /**
     * Make a secure POST request
     */
    async securePost(endpoint, data, options = {}) {
        try {
            const encrypted = this.encryptData(data);
            
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...options.headers,
                },
                body: JSON.stringify({ payload: encrypted }),
                ...options,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            // Check if response is encrypted
            if (result.encrypted && result.payload) {
                return this.decryptData(result.payload);
            }

            return result;
        } catch (error) {
            console.error('Secure request failed:', error);
            throw error;
        }
    }

    /**
     * Make a secure GET request
     */
    async secureGet(endpoint, options = {}) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    ...options.headers,
                },
                ...options,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            // Check if response is encrypted
            if (result.encrypted && result.payload) {
                return this.decryptData(result.payload);
            }

            return result;
        } catch (error) {
            console.error('Secure request failed:', error);
            throw error;
        }
    }
}

// ============================================
// Usage Examples
// ============================================

const API_BASE_URL = 'https://api.example.com';
const ENCRYPTION_KEY = process.env.REACT_APP_ENCRYPTION_KEY || 'your-encryption-key';

const client = new SecureApiClient(API_BASE_URL, ENCRYPTION_KEY);

// Example 1: Create user with encrypted request/response
async function createUser() {
    const userData = {
        name: 'John Doe',
        email: 'john@example.com',
        password: 'secret123',
    };

    try {
        const response = await client.securePost('/api/users', userData);
        console.log('User created:', response);
        return response;
    } catch (error) {
        console.error('Failed to create user:', error);
    }
}

// Example 2: Get secure data
async function getSecureData() {
    try {
        const response = await client.secureGet('/api/secure-data');
        console.log('Secure data:', response);
        return response;
    } catch (error) {
        console.error('Failed to get secure data:', error);
    }
}

// Example 3: Send transaction
async function sendTransaction(amount, recipient) {
    const transactionData = {
        amount: amount,
        recipient: recipient,
        currency: 'USD',
        timestamp: new Date().toISOString(),
    };

    try {
        const response = await client.securePost('/api/transactions', transactionData, {
            headers: {
                'Authorization': `Bearer ${getAuthToken()}`,
            },
        });
        console.log('Transaction successful:', response);
        return response;
    } catch (error) {
        console.error('Transaction failed:', error);
    }
}

// Helper function to get auth token
function getAuthToken() {
    return localStorage.getItem('auth_token') || '';
}

// ============================================
// React Hook Example
// ============================================

function useSecureApi() {
    const client = new SecureApiClient(API_BASE_URL, ENCRYPTION_KEY);

    const securePost = async (endpoint, data, options = {}) => {
        return await client.securePost(endpoint, data, options);
    };

    const secureGet = async (endpoint, options = {}) => {
        return await client.secureGet(endpoint, options);
    };

    return { securePost, secureGet };
}

// Usage in React component
function MyComponent() {
    const { securePost } = useSecureApi();

    const handleSubmit = async (formData) => {
        try {
            const result = await securePost('/api/submit', formData);
            console.log('Success:', result);
        } catch (error) {
            console.error('Error:', error);
        }
    };

    return (
        <form onSubmit={(e) => {
            e.preventDefault();
            handleSubmit({ name: 'Test', value: 123 });
        }}>
            <button type="submit">Submit Encrypted</button>
        </form>
    );
}

export { SecureApiClient, useSecureApi };
