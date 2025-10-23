#!/usr/bin/env node

/**
 * Musa & Arion Backend Startup Script
 * Complete initialization and testing
 */

const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

// Configuration
const config = {
    port: process.env.PORT || 3001,
    env: process.env.NODE_ENV || 'development',
    dbCheck: true,
    apiTest: true,
    verbose: true
};

// Colors for console output
const colors = {
    reset: '\x1b[0m',
    bright: '\x1b[1m',
    red: '\x1b[31m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    magenta: '\x1b[35m',
    cyan: '\x1b[36m'
};

// Logging utility
function log(message, color = 'reset') {
    const timestamp = new Date().toISOString();
    console.log(`${colors[color]}[${timestamp}] ${message}${colors.reset}`);
}

// Check if required directories exist
function checkDirectories() {
    log('🔍 Checking directories...', 'cyan');
    
    const requiredDirs = [
        'uploads',
        'uploads/products',
        'config',
        'routes',
        'database'
    ];

    requiredDirs.forEach(dir => {
        const dirPath = path.join(__dirname, dir);
        if (!fs.existsSync(dirPath)) {
            log(`📁 Creating directory: ${dir}`, 'yellow');
            fs.mkdirSync(dirPath, { recursive: true });
        }
    });

    log('✅ Directories checked', 'green');
}

// Check environment variables
function checkEnvironment() {
    log('🔍 Checking environment...', 'cyan');
    
    const requiredVars = [
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'JWT_SECRET'
    ];

    const missing = requiredVars.filter(varName => !process.env[varName]);
    
    if (missing.length > 0) {
        log(`❌ Missing environment variables: ${missing.join(', ')}`, 'red');
        log('💡 Please check your .env file', 'yellow');
        return false;
    }

    log('✅ Environment variables checked', 'green');
    return true;
}

// Test database connection
async function testDatabase() {
    log('🔍 Testing database connection...', 'cyan');
    
    try {
        const db = require('./config/database');
        await db.query('SELECT NOW()');
        log('✅ Database connection successful', 'green');
        return true;
    } catch (error) {
        log(`❌ Database connection failed: ${error.message}`, 'red');
        log('💡 Please check your database configuration', 'yellow');
        return false;
    }
}

// Start the server
function startServer() {
    log('🚀 Starting Musa & Arion Backend...', 'magenta');
    
    const serverProcess = spawn('node', ['server.js'], {
        stdio: 'inherit',
        env: process.env
    });

    serverProcess.on('error', (error) => {
        log(`❌ Server error: ${error.message}`, 'red');
        process.exit(1);
    });

    serverProcess.on('close', (code) => {
        log(`Server process exited with code ${code}`, code === 0 ? 'green' : 'red');
    });

    // Handle graceful shutdown
    process.on('SIGTERM', () => {
        log('🛑 Received SIGTERM, shutting down gracefully...', 'yellow');
        serverProcess.kill('SIGTERM');
    });

    process.on('SIGINT', () => {
        log('🛑 Received SIGINT, shutting down gracefully...', 'yellow');
        serverProcess.kill('SIGINT');
    });
}

// Test API endpoints
async function testAPI() {
    log('🔍 Testing API endpoints...', 'cyan');
    
    // Wait for server to start
    await new Promise(resolve => setTimeout(resolve, 3000));
    
    try {
        const fetch = require('node-fetch');
        const baseURL = `http://localhost:${config.port}/api`;
        
        // Test health endpoint
        const healthResponse = await fetch(`${baseURL}/health`);
        const healthData = await healthResponse.json();
        
        if (healthResponse.ok) {
            log('✅ Health endpoint working', 'green');
        } else {
            log('❌ Health endpoint failed', 'red');
        }
        
        // Test products endpoint
        const productsResponse = await fetch(`${baseURL}/products`);
        const productsData = await productsResponse.json();
        
        if (productsResponse.ok) {
            log('✅ Products endpoint working', 'green');
        } else {
            log('❌ Products endpoint failed', 'red');
        }
        
        // Test admin login
        const loginResponse = await fetch(`${baseURL}/admin/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: 'admin',
                password: 'admin123'
            })
        });
        
        const loginData = await loginResponse.json();
        
        if (loginResponse.ok && loginData.success) {
            log('✅ Admin login working', 'green');
        } else {
            log('❌ Admin login failed', 'red');
        }
        
    } catch (error) {
        log(`❌ API test failed: ${error.message}`, 'red');
    }
}

// Main startup function
async function main() {
    log('🌟 Musa & Arion Backend Startup', 'bright');
    log('=====================================', 'bright');
    
    // Check directories
    checkDirectories();
    
    // Check environment
    if (!checkEnvironment()) {
        process.exit(1);
    }
    
    // Test database if enabled
    if (config.dbCheck) {
        const dbOk = await testDatabase();
        if (!dbOk) {
            log('⚠️  Database test failed, but continuing...', 'yellow');
        }
    }
    
    // Start server
    startServer();
    
    // Test API if enabled
    if (config.apiTest) {
        setTimeout(testAPI, 5000);
    }
    
    log('🎉 Backend startup complete!', 'green');
    log(`🌐 Server running on http://localhost:${config.port}`, 'cyan');
    log(`📋 Health check: http://localhost:${config.port}/api/health`, 'cyan');
    log(`🔐 Admin panel: http://localhost:${config.port}/admin`, 'cyan');
}

// Run main function
if (require.main === module) {
    main().catch(error => {
        log(`❌ Startup failed: ${error.message}`, 'red');
        process.exit(1);
    });
}

module.exports = { main, config };
