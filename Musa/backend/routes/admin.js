const express = require('express');
const multer = require('multer');
const sharp = require('sharp');
const path = require('path');
const fs = require('fs');
const db = require('../config/database');
const jwt = require('jsonwebtoken');
const router = express.Router();

// Middleware to verify admin token
const verifyAdmin = (req, res, next) => {
    const token = req.headers.authorization?.split(' ')[1];
    
    if (!token) {
        return res.status(401).json({ error: 'Access token required' });
    }
    
    try {
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        if (decoded.role !== 'admin') {
            return res.status(403).json({ error: 'Admin access required' });
        }
        req.user = decoded;
        next();
    } catch (error) {
        return res.status(401).json({ error: 'Invalid token' });
    }
};

// Configure multer for file uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        const uploadPath = path.join(__dirname, '../uploads/products');
        if (!fs.existsSync(uploadPath)) {
            fs.mkdirSync(uploadPath, { recursive: true });
        }
        cb(null, uploadPath);
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
    }
});

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 5 * 1024 * 1024, // 5MB limit
    },
    fileFilter: (req, file, cb) => {
        if (file.mimetype.startsWith('image/')) {
            cb(null, true);
        } else {
            cb(new Error('Only image files are allowed'), false);
        }
    }
});

// Admin login route
router.post('/login', async (req, res) => {
    try {
        const { username, password, rememberMe } = req.body;
        
        // Validate input
        if (!username || !password) {
            return res.status(400).json({ 
                success: false, 
                message: 'Username and password are required' 
            });
        }
        
        // For development, use hardcoded credentials
        // In production, this should check against a database with hashed passwords
        const validCredentials = [
            { username: 'admin', password: 'admin123', name: 'Administrador Principal' },
            { username: 'musaarion', password: 'musa2024', name: 'Musa & Arion Admin' }
        ];
        
        const user = validCredentials.find(u => u.username === username && u.password === password);
        
        if (!user) {
            return res.status(401).json({ 
                success: false, 
                message: 'Invalid credentials' 
            });
        }
        
        // Generate JWT token
        const token = jwt.sign(
            { 
                id: user.username, 
                username: user.username, 
                role: 'admin' 
            },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: rememberMe ? '7d' : '1d' }
        );
        
        res.json({
            success: true,
            message: 'Login successful',
            token: token,
            user: {
                username: user.username,
                name: user.name,
                role: 'admin'
            }
        });
        
    } catch (error) {
        console.error('Admin login error:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Internal server error' 
        });
    }
});

// Create product
router.post('/products', verifyAdmin, upload.array('images', 10), async (req, res) => {
    try {
        const {
            name,
            description,
            price,
            sale_price,
            sku,
            category_id,
            gender,
            stock_quantity,
            featured,
            colors,
            sizes,
            meta_title,
            meta_description
        } = req.body;

        // Validate required fields
        if (!name || !price || !category_id || !gender) {
            return res.status(400).json({ 
                error: 'Name, price, category, and gender are required' 
            });
        }

        // Generate slug from name
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');

        // Check if slug already exists
        const existingProduct = await db.query('SELECT id FROM products WHERE slug = $1', [slug]);
        if (existingProduct.rows.length > 0) {
            return res.status(400).json({ error: 'Product with this name already exists' });
        }

        // Insert product
        const productResult = await db.query(`
            INSERT INTO products (
                name, slug, description, price, sale_price, sku, category_id,
                gender, stock_quantity, featured, meta_title, meta_description
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)
            RETURNING id
        `, [
            name,
            slug,
            description,
            parseFloat(price),
            sale_price ? parseFloat(sale_price) : null,
            sku,
            parseInt(category_id),
            gender,
            parseInt(stock_quantity) || 0,
            featured === 'true',
            meta_title || name,
            meta_description || description
        ]);

        const productId = productResult.rows[0].id;

        // Process and save images
        if (req.files && req.files.length > 0) {
            for (let i = 0; i < req.files.length; i++) {
                const file = req.files[i];
                
                // Optimize image with Sharp
                const optimizedFileName = `optimized-${file.filename}`;
                const optimizedPath = path.join(path.dirname(file.path), optimizedFileName);
                
                await sharp(file.path)
                    .resize(800, 800, { 
                        fit: 'inside',
                        withoutEnlargement: true
                    })
                    .jpeg({ quality: 80 })
                    .toFile(optimizedPath);

                // Delete original file
                fs.unlinkSync(file.path);

                // Save image info to database
                const imageUrl = `/uploads/products/${optimizedFileName}`;
                await db.query(`
                    INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_primary)
                    VALUES ($1, $2, $3, $4, $5)
                `, [
                    productId,
                    imageUrl,
                    `${name} - Imagen ${i + 1}`,
                    i,
                    i === 0 // First image is primary
                ]);
            }
        }

        // Add colors if provided
        if (colors) {
            const colorArray = typeof colors === 'string' ? JSON.parse(colors) : colors;
            for (const color of colorArray) {
                await db.query(`
                    INSERT INTO product_colors (product_id, color_name, color_code, stock_quantity)
                    VALUES ($1, $2, $3, $4)
                `, [
                    productId,
                    color.name,
                    color.code || null,
                    parseInt(color.stock) || 0
                ]);
            }
        }

        // Add sizes if provided
        if (sizes) {
            const sizeArray = typeof sizes === 'string' ? JSON.parse(sizes) : sizes;
            for (let i = 0; i < sizeArray.length; i++) {
                const size = sizeArray[i];
                await db.query(`
                    INSERT INTO product_sizes (product_id, size_name, size_order, stock_quantity)
                    VALUES ($1, $2, $3, $4)
                `, [
                    productId,
                    size.name,
                    i,
                    parseInt(size.stock) || 0
                ]);
            }
        }

        res.status(201).json({
            success: true,
            message: 'Product created successfully',
            product_id: productId,
            slug: slug
        });

    } catch (error) {
        console.error('Create product error:', error);
        res.status(500).json({ 
            error: 'Error creating product',
            details: error.message
        });
    }
});

// Get all products for admin
router.get('/products', verifyAdmin, async (req, res) => {
    try {
        const { page = 1, limit = 20, search = '' } = req.query;
        const offset = (page - 1) * limit;

        let query = `
            SELECT 
                p.*,
                c.name as category_name,
                COUNT(*) OVER() as total_count,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', pi.id,
                            'image_url', pi.image_url,
                            'is_primary', pi.is_primary
                        )
                    ) FILTER (WHERE pi.id IS NOT NULL), '[]'
                ) as images
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON p.id = pi.product_id
        `;

        const params = [];
        let paramCount = 1;

        if (search) {
            query += ` WHERE p.name ILIKE $${paramCount} OR p.sku ILIKE $${paramCount}`;
            params.push(`%${search}%`);
            paramCount++;
        }

        query += `
            GROUP BY p.id, c.name
            ORDER BY p.created_at DESC
            LIMIT $${paramCount} OFFSET $${paramCount + 1}
        `;

        params.push(parseInt(limit), parseInt(offset));

        const result = await db.query(query, params);

        res.json({
            success: true,
            products: result.rows,
            pagination: {
                page: parseInt(page),
                limit: parseInt(limit),
                total: result.rows[0]?.total_count || 0,
                pages: Math.ceil((result.rows[0]?.total_count || 0) / limit)
            }
        });

    } catch (error) {
        console.error('Get admin products error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Update product
router.put('/products/:id', verifyAdmin, upload.array('images', 10), async (req, res) => {
    try {
        const productId = req.params.id;
        const {
            name,
            description,
            price,
            sale_price,
            sku,
            category_id,
            gender,
            stock_quantity,
            featured,
            is_active,
            colors,
            sizes,
            meta_title,
            meta_description
        } = req.body;

        // Generate new slug if name changed
        let slug = null;
        if (name) {
            slug = name.toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
        }

        // Update product
        const updateFields = [];
        const updateValues = [];
        let paramCount = 1;

        if (name) {
            updateFields.push(`name = $${paramCount}`);
            updateValues.push(name);
            paramCount++;
        }
        if (slug) {
            updateFields.push(`slug = $${paramCount}`);
            updateValues.push(slug);
            paramCount++;
        }
        if (description !== undefined) {
            updateFields.push(`description = $${paramCount}`);
            updateValues.push(description);
            paramCount++;
        }
        if (price) {
            updateFields.push(`price = $${paramCount}`);
            updateValues.push(parseFloat(price));
            paramCount++;
        }
        if (sale_price !== undefined) {
            updateFields.push(`sale_price = $${paramCount}`);
            updateValues.push(sale_price ? parseFloat(sale_price) : null);
            paramCount++;
        }
        if (sku !== undefined) {
            updateFields.push(`sku = $${paramCount}`);
            updateValues.push(sku);
            paramCount++;
        }
        if (category_id) {
            updateFields.push(`category_id = $${paramCount}`);
            updateValues.push(parseInt(category_id));
            paramCount++;
        }
        if (gender) {
            updateFields.push(`gender = $${paramCount}`);
            updateValues.push(gender);
            paramCount++;
        }
        if (stock_quantity !== undefined) {
            updateFields.push(`stock_quantity = $${paramCount}`);
            updateValues.push(parseInt(stock_quantity));
            paramCount++;
        }
        if (featured !== undefined) {
            updateFields.push(`featured = $${paramCount}`);
            updateValues.push(featured === 'true');
            paramCount++;
        }
        if (is_active !== undefined) {
            updateFields.push(`is_active = $${paramCount}`);
            updateValues.push(is_active === 'true');
            paramCount++;
        }

        updateFields.push(`updated_at = CURRENT_TIMESTAMP`);
        updateValues.push(productId);

        if (updateFields.length > 1) { // More than just updated_at
            const updateQuery = `
                UPDATE products 
                SET ${updateFields.join(', ')}
                WHERE id = $${paramCount}
            `;

            await db.query(updateQuery, updateValues);
        }

        // Handle new images
        if (req.files && req.files.length > 0) {
            // Get current max sort_order
            const maxOrderResult = await db.query(
                'SELECT COALESCE(MAX(sort_order), -1) as max_order FROM product_images WHERE product_id = $1',
                [productId]
            );
            let nextSortOrder = maxOrderResult.rows[0].max_order + 1;

            for (const file of req.files) {
                // Optimize image
                const optimizedFileName = `optimized-${file.filename}`;
                const optimizedPath = path.join(path.dirname(file.path), optimizedFileName);
                
                await sharp(file.path)
                    .resize(800, 800, { 
                        fit: 'inside',
                        withoutEnlargement: true
                    })
                    .jpeg({ quality: 80 })
                    .toFile(optimizedPath);

                fs.unlinkSync(file.path);

                const imageUrl = `/uploads/products/${optimizedFileName}`;
                await db.query(`
                    INSERT INTO product_images (product_id, image_url, alt_text, sort_order)
                    VALUES ($1, $2, $3, $4)
                `, [
                    productId,
                    imageUrl,
                    `${name || 'Product'} - Imagen ${nextSortOrder + 1}`,
                    nextSortOrder
                ]);

                nextSortOrder++;
            }
        }

        res.json({
            success: true,
            message: 'Product updated successfully'
        });

    } catch (error) {
        console.error('Update product error:', error);
        res.status(500).json({ 
            error: 'Error updating product',
            details: error.message
        });
    }
});

// Delete product
router.delete('/products/:id', verifyAdmin, async (req, res) => {
    try {
        const productId = req.params.id;

        // Get product images to delete files
        const imagesResult = await db.query(
            'SELECT image_url FROM product_images WHERE product_id = $1',
            [productId]
        );

        // Delete image files
        for (const image of imagesResult.rows) {
            const imagePath = path.join(__dirname, '..', image.image_url);
            if (fs.existsSync(imagePath)) {
                fs.unlinkSync(imagePath);
            }
        }

        // Delete product (cascade will handle related records)
        await db.query('DELETE FROM products WHERE id = $1', [productId]);

        res.json({
            success: true,
            message: 'Product deleted successfully'
        });

    } catch (error) {
        console.error('Delete product error:', error);
        res.status(500).json({ 
            error: 'Error deleting product',
            details: error.message
        });
    }
});

// Get dashboard stats
router.get('/dashboard', verifyAdmin, async (req, res) => {
    try {
        // Get total products
        const productsResult = await db.query('SELECT COUNT(*) as total FROM products WHERE is_active = true');
        
        // Get total orders
        const ordersResult = await db.query('SELECT COUNT(*) as total FROM orders');
        
        // Get total revenue
        const revenueResult = await db.query(`
            SELECT COALESCE(SUM(total_amount), 0) as total 
            FROM orders 
            WHERE payment_status = 'approved'
        `);
        
        // Get recent orders
        const recentOrdersResult = await db.query(`
            SELECT order_number, customer_name, total_amount, payment_status, created_at
            FROM orders 
            ORDER BY created_at DESC 
            LIMIT 10
        `);

        res.json({
            success: true,
            stats: {
                total_products: parseInt(productsResult.rows[0].total),
                total_orders: parseInt(ordersResult.rows[0].total),
                total_revenue: parseFloat(revenueResult.rows[0].total),
                recent_orders: recentOrdersResult.rows
            }
        });

    } catch (error) {
        console.error('Get dashboard stats error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Get admin dashboard stats
router.get('/dashboard/stats', verifyAdmin, async (req, res) => {
    try {
        // Get total products
        const productsResult = await db.query('SELECT COUNT(*) FROM products WHERE is_active = true');
        const totalProducts = parseInt(productsResult.rows[0].count);
        
        // Get total orders (this would need an orders table)
        const ordersToday = 28; // Placeholder
        
        // Get total sales (this would need proper sales calculation)
        const totalSales = 1250000; // Placeholder
        
        // Get satisfaction rate (this would need a reviews system)
        const satisfactionRate = 95; // Placeholder
        
        res.json({
            success: true,
            stats: {
                totalProducts,
                ordersToday,
                totalSales,
                satisfactionRate
            }
        });
    } catch (error) {
        console.error('Dashboard stats error:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Error fetching dashboard stats' 
        });
    }
});

module.exports = router;
